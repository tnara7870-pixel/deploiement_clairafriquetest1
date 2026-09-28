<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\CommandeAnnulationController;
use App\Http\Controllers\Admin\MouvementController;
use App\Http\Controllers\Admin\RapportController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Client\CatalogueController;
use App\Http\Controllers\Client\CommandeController;
use App\Http\Controllers\Client\CompteController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\FavoriController;
use App\Http\Controllers\Client\PaiementController;
use App\Http\Controllers\Client\PanierController;
use App\Http\Controllers\Commande\LivraisonController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\Stock\AlerteController;
use Illuminate\Support\Facades\Route;

// Pages légales : publiques, accessibles avec ou sans connexion (le
// bandeau cookies et le lien du footer y renvoient depuis toutes les pages).
Route::get('/conditions-utilisation', [LegalController::class, 'cgu'])->name('legal.cgu');
Route::get('/confidentialite', [LegalController::class, 'confidentialite'])->name('legal.confidentialite');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/mot-de-passe/oublie', [PasswordResetController::class, 'demande'])->name('password.request');
    Route::post('/mot-de-passe/email', [PasswordResetController::class, 'envoyerLien'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/mot-de-passe/reinitialiser/{token}', [PasswordResetController::class, 'formulaire'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'reinitialiser'])
        ->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth', 'account.active'])->name('logout');

// Vérification de l'email par code (accessible dès la connexion, avant
// même que le compte soit vérifié — donc pas de middleware
// 'email.verified' ici, seulement 'auth').
Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/verification-email', [EmailVerificationController::class, 'formulaire'])->name('verification.notice');
    Route::post('/verification-email', [EmailVerificationController::class, 'verifier'])
        ->middleware('throttle:10,1')->name('verification.verifier');
    Route::post('/verification-email/renvoyer', [EmailVerificationController::class, 'renvoyer'])
        ->middleware('throttle:3,1')->name('verification.renvoyer');
});

// ── ESPACE CLIENT ─────────────────────────────────────────────────
Route::prefix('catalogue')->name('client.')->group(function () {

    // Pages publiques
    Route::get('/', [CatalogueController::class, 'index'])->name('catalogue');
    Route::get('/article/{id}', [CatalogueController::class, 'show'])->name('article');

    // Pages nécessitant connexion client
    Route::middleware(['auth', 'account.active', 'email.verified', 'role:client'])->group(function () {

        // Panier
        Route::get('/panier', [PanierController::class, 'index'])->name('panier');
        Route::post('/panier', [PanierController::class, 'ajouter'])->name('panier.ajouter');
        Route::post('/panier/ajax', [PanierController::class, 'ajouterAjax'])->name('panier.ajouter.ajax');
        Route::post('/panier/definir/ajax', [PanierController::class, 'definirQuantiteAjax'])->name('panier.definir.ajax');
        Route::patch('/panier/{id}', [PanierController::class, 'modifier'])->name('panier.modifier');
        Route::patch('/panier/{id}/ajax', [PanierController::class, 'modifierAjax'])->name('panier.modifier.ajax');
        Route::delete('/panier/{id}', [PanierController::class, 'supprimer'])->name('panier.supprimer');

        // Favoris
        Route::get('/favoris', [FavoriController::class, 'index'])->name('favoris');
        Route::post('/favoris/{id}', [FavoriController::class, 'toggle'])->name('favoris.toggle');
        Route::post('/favoris/{id}/ajax', [FavoriController::class, 'toggleAjax'])->name('favoris.toggle.ajax');

        // Commandes
        Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes');
        Route::get('/commande/especes', [CommandeController::class, 'especes'])->name('commande.especes');
        Route::post('/commande/especes/confirmer', [CommandeController::class, 'confirmerEspeces'])->name('commande.especes.confirmer');
        Route::get('/recapitulatif', [CommandeController::class, 'recapitulatif'])->name('recapitulatif');
        Route::post('/commande/preparer', [CommandeController::class, 'preparer'])->name('commande.preparer');
        Route::get('/commande/{id}', [CommandeController::class, 'show'])->name('commande.detail');
        Route::get('/commande/{id}/facture', [CommandeController::class, 'facture'])->name('commande.facture');
        Route::post('/commande/{id}/demander-annulation', [CommandeController::class, 'demanderAnnulation'])->name('commande.demanderAnnulation');

        // Paiement PayDunya
        Route::get('/paiement', [PaiementController::class, 'choix'])->name('paiement.choix');
        Route::get('/paiement/retour', [PaiementController::class, 'retour'])->name('paiement.retour');
        Route::get('/paiement/annulation', [PaiementController::class, 'annulation'])->name('paiement.annulation');

        // Compte
        Route::get('/compte', [CompteController::class, 'index'])->name('compte');
        Route::patch('/compte', [CompteController::class, 'update'])->name('compte.update');
        Route::get('/catalogue/ajax', [CatalogueController::class, 'ajax'])->name('client.catalogue.ajax');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Notifications in-app
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::patch('/notifications/{id}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
        Route::patch('/notifications/toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.toutesLues');
    });
});

// IPN PayDunya
Route::post('/catalogue/paiement/ipn', [PaiementController::class, 'ipn'])
    ->name('paiement.ipn');

// ── ESPACE ADMIN ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'account.active', 'role:administrateur'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/commandes/{id}/annulation', [CommandeAnnulationController::class, 'show'])
        ->name('commande.annulation');

    Route::patch('/utilisateur/{id}/mot-de-passe', [UtilisateurController::class, 'updatePassword'])
        ->name('utilisateur.password.update');

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles');

    Route::get('/categories', [CategorieController::class, 'index'])->name('categories');

    Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs');
    Route::patch('/utilisateur/{id}/bloquer', [UtilisateurController::class, 'bloquer'])->name('utilisateur.bloquer');
    Route::patch('/utilisateur/{id}/debloquer', [UtilisateurController::class, 'debloquer'])->name('utilisateur.debloquer');
    Route::patch('/utilisateur/{id}/role', [UtilisateurController::class, 'updateRole'])->name('utilisateur.role');
    Route::post('/utilisateur', [UtilisateurController::class, 'store'])->name('utilisateur.store');

    Route::get('/mouvements', [MouvementController::class, 'index'])->name('mouvements');
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports');
    Route::get('/rapports/export', [RapportController::class, 'export'])->name('rapports.export');

    Route::get('/compte', [App\Http\Controllers\Shared\CompteController::class, 'index'])->name('compte');
    Route::patch('/compte', [App\Http\Controllers\Shared\CompteController::class, 'update'])->name('compte.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('/notifications/{id}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
    Route::patch('/notifications/toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.toutesLues');
});

// ── ESPACE RES.STOCK ──────────────────────────────────────────────
Route::prefix('stock')->name('stock.')->middleware(['auth', 'account.active', 'role:res.stock,administrateur'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Stock\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/articles', [App\Http\Controllers\Stock\ArticleController::class, 'index'])->name('articles');
    Route::post('/article', [App\Http\Controllers\Stock\ArticleController::class, 'store'])->name('article.store');
    Route::patch('/article/{id}', [App\Http\Controllers\Stock\ArticleController::class, 'update'])->name('article.update');
    Route::patch('/article/{id}/statut', [App\Http\Controllers\Stock\ArticleController::class, 'toggleStatut'])->name('article.statut');
    Route::delete('/article/{id}', [App\Http\Controllers\Stock\ArticleController::class, 'destroy'])->name('article.destroy');

    Route::get('/categories', [App\Http\Controllers\Stock\CategorieController::class, 'index'])->name('categories');
    Route::post('/categorie', [App\Http\Controllers\Stock\CategorieController::class, 'store'])->name('categorie.store');
    Route::patch('/categorie/{id}', [App\Http\Controllers\Stock\CategorieController::class, 'update'])->name('categorie.update');
    Route::patch('/categorie/{id}/statut', [App\Http\Controllers\Stock\CategorieController::class, 'toggleStatut'])->name('categorie.statut');
    Route::delete('/categorie/{id}', [App\Http\Controllers\Stock\CategorieController::class, 'destroy'])->name('categorie.destroy');

    Route::get('/mouvements', [App\Http\Controllers\Stock\MouvementController::class, 'index'])->name('mouvements');
    Route::post('/mouvement', [App\Http\Controllers\Stock\MouvementController::class, 'store'])->name('mouvement.store');

    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes');
    Route::post('/alerte', [AlerteController::class, 'store'])->name('alerte.store');
    Route::patch('/alerte/{id}', [AlerteController::class, 'toggle'])->name('alerte.toggle');

    Route::get('/compte', [App\Http\Controllers\Shared\CompteController::class, 'index'])->name('compte');
    Route::patch('/compte', [App\Http\Controllers\Shared\CompteController::class, 'update'])->name('compte.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('/notifications/{id}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
    Route::patch('/notifications/toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.toutesLues');
});

// ── ESPACE RES.COMMANDE ───────────────────────────────────────────
Route::prefix('commandes-admin')->name('commande.')->middleware(['auth', 'account.active', 'role:res.commande,administrateur'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Commande\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/liste', [App\Http\Controllers\Commande\CommandeController::class, 'index'])->name('liste');
    Route::get('/detail/{id}', [App\Http\Controllers\Commande\CommandeController::class, 'show'])->name('detail');
    Route::patch('/statut/{id}', [App\Http\Controllers\Commande\CommandeController::class, 'updateStatut'])->name('statut');

    Route::middleware('role:administrateur')->group(function () {
        Route::patch('/annulation/{id}/valider', [App\Http\Controllers\Commande\CommandeController::class, 'validerAnnulation'])->name('annulation.valider');
        Route::patch('/annulation/{id}/refuser', [App\Http\Controllers\Commande\CommandeController::class, 'refuserAnnulation'])->name('annulation.refuser');
        Route::post('/remboursement/{id}', [App\Http\Controllers\Commande\CommandeController::class, 'enregistrerRemboursement'])->name('remboursement.enregistrer');
    });

    Route::patch('/probleme-stock/{id}/resoudre', [App\Http\Controllers\Commande\CommandeController::class, 'resoudreProblemeStock'])->name('problemeStock.resoudre');
    Route::get('/livraisons', [LivraisonController::class, 'index'])->name('livraisons');
    Route::patch('/livraison/{id}', [LivraisonController::class, 'update'])->name('livraison.update');
    Route::get('/paiements', [App\Http\Controllers\Commande\PaiementController::class, 'index'])->name('paiements');
    Route::patch('/paiement/{id}', [App\Http\Controllers\Commande\PaiementController::class, 'update'])->name('paiement.update');
    Route::get('/facture/{id}', [App\Http\Controllers\Commande\CommandeController::class, 'facture'])->name('facture');

    Route::get('/compte', [App\Http\Controllers\Shared\CompteController::class, 'index'])->name('compte');
    Route::patch('/compte', [App\Http\Controllers\Shared\CompteController::class, 'update'])->name('compte.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('/notifications/{id}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
    Route::patch('/notifications/toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.toutesLues');
});

Route::get('/commandes-admin/notifications/count',
    [App\Http\Controllers\Commande\DashboardController::class, 'notificationsCount'])
    ->middleware(['auth', 'role:res.commande,administrateur'])
    ->name('commande.notifications.count');

// ── ACCUEIL ───────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
