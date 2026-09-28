<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Panier;
use App\Models\User;
use App\Rules\MotDePasseComplexe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    // Nombre de tentatives autorisées avant blocage du compte
    private const MAX_TENTATIVES = 3;

    // Durée du blocage en secondes (10 minutes)
    private const DUREE_BLOCAGE = 600;

    // Afficher le formulaire de connexion
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectParRole();
        }

        // Si le dernier email tenté est encore bloqué (ex. rechargement de
        // la page pendant le décompte), on renvoie le temps restant pour
        // que la vue puisse réafficher le message + le décompte.
        $secondesRestantes = null;
        $dernierEmail = session('dernier_email_connexion');

        if ($dernierEmail) {
            $user = $this->trouverUtilisateur($dernierEmail);
            if ($user && $this->estBloque($user)) {
                $secondesRestantes = now()->diffInSeconds($user->bloqueJusqua, false);
            }
        }

        return view('auth.login', [
            'secondesRestantes' => $secondesRestantes,
            'emailBloque' => $secondesRestantes ? $dernierEmail : null,
        ]);
    }

    // Traiter la connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required|min:6',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'motDePasse.required' => 'Le mot de passe est obligatoire.',
            'motDePasse.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // On mémorise l'email tenté pour pouvoir réafficher le blocage
        // même si l'utilisateur recharge la page de connexion.
        session(['dernier_email_connexion' => $request->email]);

        // Limiteur simple par IP : protège contre le credential stuffing
        // distribué / énumération d'emails (A4). Renvoie 429 quand le
        // seuil est atteint.
        $limiterKey = 'login-ip:'.$request->ip();
        $maxAttempts = 10; // autoriser jusqu'à 10 tentatives différentes par IP

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors([
                'email' => 'Trop de tentatives depuis votre adresse réseau. Réessayez dans '.$seconds.' seconde(s).',
            ])->setStatusCode(429);
        }

        $user = $this->trouverUtilisateur($request->email);

        // Si un blocage précédent existe mais est terminé, on réinitialise
        // le compteur : l'utilisateur doit retrouver 3 tentatives fraîches.
        if ($user && $user->bloqueJusqua !== null && $user->bloqueJusqua->isPast()) {
            // forceFill() : tentativesEchouees/bloqueJusqua sont
            // volontairement hors de $fillable (colonnes de sécurité, voir
            // audit du 02/08/2026 point 9) — cette mise à jour reste
            // interne et contrôlée (aucune donnée issue de $request), donc
            // le contournement du guard de mass-assignment est légitime ici.
            $user->forceFill([
                'tentativesEchouees' => 0,
                'bloqueJusqua' => null,
            ])->save();
        }

        // Le compte est déjà bloqué : on ne vérifie même pas le mot de
        // passe, il reste réellement inaccessible pendant le blocage.
        if ($user && $this->estBloque($user)) {
            return $this->reponseCompteBloque($user);
        }

        if (! $user) {
            // Email inconnu : on calcule quand même un hash bcrypt, à
            // vide, pour que le temps de réponse soit du même ordre que
            // le cas "email connu, mot de passe faux" ci-dessous (qui,
            // lui, appelle Hash::check() — coût CPU équivalent à
            // Hash::make()). Sans ça, la réponse revient quasi
            // instantanément pour un email inconnu et après ~100ms pour
            // un email connu : le message d'erreur est identique dans
            // les deux cas, mais le temps de réponse, mesurable et
            // automatisable, ne l'est pas — ce qui permet d'énumérer les
            // comptes enregistrés malgré l'intention du message
            // générique. Voir audit du 04/08/2026, point moyen 9.
            Hash::make($request->motDePasse);
        }

        if (! $user || ! Hash::check($request->motDePasse, $user->motDePasse)) {
            // Toujours incrémenter le compteur IP pour toute tentative invalide
            // (email inconnu ou mot de passe invalide) afin que l'attaquant
            // subisse le throttle même en changeant d'email (A4).
            RateLimiter::hit($limiterKey, 60);

            if ($user) {
                $user->increment('tentativesEchouees');

                // Ce dernier échec vient d'atteindre le seuil de blocage.
                if ($user->tentativesEchouees >= self::MAX_TENTATIVES) {
                    $user->forceFill([
                        'bloqueJusqua' => now()->addSeconds(self::DUREE_BLOCAGE),
                    ])->save();

                    return $this->reponseCompteBloque($user->fresh());
                }

                $restantes = self::MAX_TENTATIVES - $user->tentativesEchouees;

                return back()->withErrors([
                    'email' => 'Identifiants incorrects. Vérifiez votre email et mot de passe. '
                        .'Tentative(s) restante(s) avant blocage du compte : '.$restantes.'.',
                ])->withInput($request->only('email'));
            }

            // Email inconnu : on ne révèle pas si le compte existe ou non,
            // mais l'IP est tout de même limitée par RateLimiter.
            return back()->withErrors([
                'email' => 'Identifiants incorrects. Vérifiez votre email et mot de passe.',
            ])->withInput($request->only('email'));
        }

        // Connexion réussie : on réinitialise le compteur d'échecs du compte.
        $user->forceFill([
            'tentativesEchouees' => 0,
            'bloqueJusqua' => null,
        ])->save();

        if (! $user->statut) {
            return back()->withErrors([
                'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Succès : réinitialiser le compteur IP et le verrouillage côté RateLimiter
        $limiterKey = 'login-ip:'.$request->ip();
        RateLimiter::clear($limiterKey);

        // Fusionner le panier session avec le panier BDD si client
        if ($user->hasRole('client')) {
            $this->fusionnerPanier($user);
        }

        return $this->redirectParRole();
    }

    // Recherche l'utilisateur par email, insensible à la casse/aux espaces.
    private function trouverUtilisateur(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->first();
    }

    // Le compte est-il actuellement bloqué ?
    private function estBloque(User $user): bool
    {
        return $user->bloqueJusqua !== null && $user->bloqueJusqua->isFuture();
    }

    // Réponse renvoyée quand le compte est bloqué (3 échecs atteints)
    private function reponseCompteBloque(User $user)
    {
        $secondes = max(now()->diffInSeconds($user->bloqueJusqua, false), 0);

        return back()
            ->withErrors([
                'email' => 'Trop de tentatives. Votre compte est bloqué pendant 10 minutes.',
            ])
            ->with('secondesRestantes', $secondes)
            ->withInput(['email' => $user->email]);
    }

    // Afficher le formulaire d'inscription
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectParRole();
        }

        return view('auth.register');
    }

    // Traiter l'inscription
    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'nullable|string|max:20',
            'motDePasse' => ['required', 'confirmed', new MotDePasseComplexe],
            'conditionsAcceptees' => 'accepted',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'motDePasse.required' => 'Le mot de passe est obligatoire.',
            'motDePasse.confirmed' => 'Les mots de passe ne correspondent pas.',
            'conditionsAcceptees.accepted' => 'Vous devez accepter les conditions d\'utilisation et la politique de confidentialité pour créer un compte.',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'motDePasse' => Hash::make($request->motDePasse),
            'telephone' => $request->telephone,
            'statut' => true,
            // Preuve de consentement (loi n°2008-12, principe de
            // redevabilité) : horodatage capturé au moment même de la
            // création, et non recalculé plus tard.
            'conditionsAccepteesLe' => now(),
        ]);

        $user->assignRole('client');

        // Créer un panier vide pour le nouveau client
        Panier::create(['idUtilisateur' => $user->idUtilisateur]);

        Auth::login($user);
        $request->session()->regenerate();

        // Vérification de l'email : envoie un code à 6 chiffres et bloque
        // l'accès au catalogue tant qu'il n'est pas confirmé (voir
        // EnsureEmailIsVerified). Confirme que le client est bien
        // propriétaire de l'adresse saisie avant de l'utiliser pour les
        // confirmations de commande et la réinitialisation de mot de passe.
        EmailVerificationController::envoyerCode($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Bienvenue sur ClaireAfrique ! Un code de vérification vient de vous être envoyé par email.');
    }

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté.');
    }

    private function redirectParRole()
    {
        $user = Auth::user();

        if ($user->hasRole('administrateur')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        if ($user->hasRole('res.stock')) {
            return redirect()->intended(route('stock.dashboard'));
        }
        if ($user->hasRole('res.commande')) {
            return redirect()->intended(route('commande.dashboard'));
        }

        return redirect()->intended(route('client.catalogue'));
    }

    // Fusionner panier session → BDD après connexion
    private function fusionnerPanier(User $user)
    {
        $panierSession = session()->get('panier', []);
        if (empty($panierSession)) {
            return;
        }

        $panier = $user->panier ?? Panier::create([
            'idUtilisateur' => $user->idUtilisateur,
        ]);

        foreach ($panierSession as $idArticle => $item) {
            $ligne = $panier->lignePaniers()
                ->where('idArticle', $idArticle)
                ->first();
            if ($ligne) {
                $ligne->increment('quantite', $item['quantite']);
            } else {
                $panier->lignePaniers()->create([
                    'idArticle' => $idArticle,
                    'quantite' => $item['quantite'],
                ]);
            }
        }

        session()->forget('panier');
    }
}
