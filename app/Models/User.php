<?php

namespace App\Models;

use App\Mail\ReinitialisationMotDePasseMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $table = 'utilisateurs';

    protected $primaryKey = 'idUtilisateur';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guard_name = 'web';

    protected $fillable = [
        'nom', 'prenom', 'email',
        'motDePasse', 'telephone', 'statut',
        // Point de vente auquel un res.stock/res.commande est rattaché.
        // NULL = accès global, non scopé (comportement historique).
        'pointVenteAssigne',
        // Horodatage du consentement aux CGU/politique de confidentialité,
        // capturé une seule fois à l'inscription (AuthController::register).
        // Comme pour les champs ci-dessus, aucun update() généraliste ne
        // doit jamais réinjecter ce champ depuis une requête utilisateur.
        'conditionsAccepteesLe',
        // tentativesEchouees et bloqueJusqua retirés : mis à jour
        // uniquement depuis AuthController/UtilisateurController via des
        // update() ciblés et contrôlés, jamais depuis une requête
        // utilisateur non filtrée. Les laisser dans $fillable n'était
        // exploité par aucun code actuel, mais fermait la porte à un
        // futur update($request->all()) mal filtré qui aurait permis à un
        // client de s'auto-débloquer. Voir audit du 02/08/2026, point 9.
    ];

    protected $hidden = ['motDePasse', 'remember_token'];

    protected function casts(): array
    {
        return [
            'statut' => 'boolean',
            'bloqueJusqua' => 'datetime',
            'emailVerifieLe' => 'datetime',
            'codeVerificationExpire' => 'datetime',
            'conditionsAccepteesLe' => 'datetime',
        ];
    }

    /**
     * Remplace l'email de réinitialisation par défaut de Laravel
     * (Illuminate\Auth\Notifications\ResetPassword, en anglais et sans
     * charte graphique) par un Mailable personnalisé aux couleurs de
     * ClaireAfrique. Appelé automatiquement par Password::sendResetLink().
     */
    public function sendPasswordResetNotification($token): void
    {
        // PasswordResetController::formulaire() attend le token en segment
        // d'URL et l'email en query string (voir routes/web.php) : on
        // reconstruit exactement cette URL, sans signature (le token du
        // PasswordBroker fait déjà foi côté serveur).
        $url = route('password.reset', $token).'?email='.urlencode($this->email);

        Mail::to($this->email)->send(new ReinitialisationMotDePasseMail($this, $url));
    }

    public function getAuthPassword(): string
    {
        return $this->motDePasse;
    }

    public function panier()
    {
        return $this->hasOne(Panier::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class, 'idUtilisateur', 'idUtilisateur');
    }

    // Alias en français conservés pour compatibilité avec les vues/contrôleurs
    // existants, mais délégant désormais à Spatie/Permission (HasRoles)
    // plutôt que de dupliquer la logique : hasRole()/hasAnyRole() utilisent
    // déjà $this->getKey(), qui respecte notre clé primaire personnalisée
    // (idUtilisateur), donc aucune requête SQL maison n'est nécessaire ici.
    public function aLeRole(string $role): bool
    {
        return $this->hasRole($role);
    }

    /**
     * true si ce compte est cantonné à un seul point de vente (res.stock
     * ou res.commande assigné à UCAD ou Centre-ville). Un compte sans
     * point assigné (y compris tout administrateur) voit tout, sans
     * filtrage — c'est le comportement historique, préservé par défaut.
     */
    public function estScopePointVente(): bool
    {
        return ! is_null($this->pointVenteAssigne);
    }

    public function aUnDesRoles(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }
}
