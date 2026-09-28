<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Commande extends Model
{
    protected $table = 'commandes';

    protected $primaryKey = 'idCommande';

    protected $fillable = [
        'numeroCommande',
        'dateCommande',
        'montantTotal',
        'statut',
        'motifAnnulation',
        'statutAvantAnnulation',
        'problemeStock',
        'problemeStockDetails',
        'idUtilisateur',
    ];

    protected function casts(): array
    {
        return [
            'dateCommande' => 'datetime',
            'montantTotal' => 'decimal:2',
            'problemeStock' => 'boolean',
        ];
    }

    // RELATIONS
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommande::class, 'idCommande', 'idCommande');
    }

    public function articles()
    {
        return $this->belongsToMany(
            Article::class,
            'ligne_commandes',
            'idCommande',
            'idArticle',
            'idCommande',
            'idArticle'
        )->withPivot('quantite', 'prixUnitaire')->withTimestamps();
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class, 'idCommande', 'idCommande');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'idCommande', 'idCommande');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class, 'idCommande', 'idCommande');
    }

    public function historique()
    {
        return $this->hasMany(HistoriqueCommande::class, 'idCommande', 'idCommande')
            ->orderBy('dateAction');
    }

    public function remboursements()
    {
        return $this->hasMany(Remboursement::class, 'idCommande', 'idCommande');
    }

    // ── RÈGLES MÉTIER CENTRALISÉES (éligibilité annulation / remboursement) ──

    /**
     * Statuts à partir desquels un client peut encore demander
     * l'annulation de sa commande (avant toute expédition).
     */
    public const STATUTS_ANNULABLES_PAR_CLIENT = ['en_attente', 'validee'];

    public function estAnnulableParClient(): bool
    {
        return in_array($this->statut, self::STATUTS_ANNULABLES_PAR_CLIENT, true);
    }

    public function aUneDemandeAnnulationEnCours(): bool
    {
        return $this->statut === 'demande_annulation';
    }

    /**
     * Une commande a besoin d'un remboursement manuel si elle est
     * annulée, que le paiement a bien été encaissé (Wave/OM validé, ou
     * espèces déjà perçues), et qu'aucun remboursement n'a encore été
     * enregistré pour elle.
     */
    public function necessiteRemboursement(): bool
    {
        if ($this->statut !== 'annulee') {
            return false;
        }

        if (! $this->paiement || $this->paiement->statutPaiement !== 'valide') {
            return false;
        }

        return $this->remboursements()->doesntExist();
    }

    /**
     * Crée une commande avec un numéro lisible dérivé de l'idCommande
     * réellement obtenu après insertion — jamais pré-calculé.
     *
     * L'ancienne version (MAX(idCommande)+1 en PHP) avait exactement la
     * même race condition qu'un COUNT() : rien ne garantit l'atomicité
     * d'un calcul fait hors de MySQL, même sur une colonne elle-même
     * auto-incrémentée. Deux commandes créées à la même milliseconde
     * (ex. webhook IPN + retour navigateur pour deux clients différents)
     * pouvaient calculer le même numéro et déclencher une violation de
     * contrainte unique non rattrapable proprement. Voir audit du
     * 02/08/2026, bug majeur 3.
     *
     * En laissant MySQL attribuer idCommande de façon atomique puis en
     * dérivant le numéro affiché à partir de cette valeur déjà garantie
     * unique, la collision devient structurellement impossible.
     */
    public static function creerAvecNumero(array $attributs): self
    {
        $commande = self::create(array_merge($attributs, [
            // Valeur temporaire courte (< 20 caractères) : garantit l'unicité
            // sans dépasser la taille max de la colonne MySQL.
            'numeroCommande' => 'TMP-'.Str::random(10),
        ]));

        $commande->update(['numeroCommande' => self::formaterNumero($commande->idCommande)]);

        return $commande;
    }

    public static function formaterNumero(int $idCommande): string
    {
        return 'CMD-'.date('Y').'-'.str_pad($idCommande, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Libellé français du statut GLOBAL de la commande — à ne pas confondre
     * avec le statut du paiement ou celui de la livraison, qui ont chacun
     * leurs propres libellés (voir vues commande.detail / commande.liste).
     * Centralisé ici pour que la vue et le message de confirmation flash
     * de CommandeController::updateStatut() emploient toujours exactement
     * le même mot — avant, les deux vues dupliquaient ce tableau et le
     * message flash ne le reprenait pas du tout ("Statut mis à jour.",
     * sans dire lequel ni vers quoi : retour utilisateur du 03/09/2026).
     */
    public static function libellesStatuts(): array
    {
        return [
            'en_attente' => 'En attente',
            'validee' => 'Validée',
            'en_livraison' => 'En livraison',
            'livree' => 'Livrée',
            'annulee' => 'Annulée',
            'demande_annulation' => 'Demande d\'annulation',
        ];
    }

    public static function libelleStatut(string $statut): string
    {
        return self::libellesStatuts()[$statut] ?? $statut;
    }
}
