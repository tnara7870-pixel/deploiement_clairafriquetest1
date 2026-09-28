<?php

namespace App\Services;

use App\DTO\CommandeData;
use App\Exceptions\StockInsuffisantException;
use App\Http\Requests\PreparerCommandeRequest;
use App\Repositories\PanierRepository;
use Illuminate\Support\Facades\Log;

class CommandePreparationService
{
    public function __construct(private PanierRepository $panierRepository) {}

    /**
     * @throws StockInsuffisantException si le stock du point attribué ne
     *                                   suffit plus — le message précise si l'article est disponible à
     *                                   l'autre point, pour que le client sache quoi faire plutôt que de
     *                                   recevoir un refus générique (même logique que
     *                                   CommandeCreationService, voir StockInsuffisantException::pour()).
     */
    public function prepare(PreparerCommandeRequest $request, int $userId): ?CommandeData
    {
        $panier = $this->panierRepository->findByUser($userId);
        if (! $panier || $panier->lignePaniers->isEmpty()) {
            return null;
        }

        // Vérifiée sur le stock du point qui fournira réellement la
        // commande (choisi par le client pour un retrait boutique,
        // entrepôt principal pour une livraison à domicile), pas sur le
        // total global — même logique que CommandeCreationService.
        $pointVenteAttribue = $request->modeLivraison === 'boutique'
            ? $request->pointVente
            : config('pointvente.entrepot_principal');

        foreach ($panier->lignePaniers as $ligne) {
            if ($ligne->article->stockPour($pointVenteAttribue) < $ligne->quantite) {
                Log::warning('Stock insuffisant durant la préparation de commande', [
                    'idArticle' => $ligne->idArticle,
                    'quantite' => $ligne->quantite,
                    'pointVente' => $pointVenteAttribue,
                    'stock' => $ligne->article->stockPour($pointVenteAttribue),
                ]);
                throw StockInsuffisantException::pour(
                    $ligne->article,
                    $ligne->quantite,
                    $pointVenteAttribue,
                    $request->modeLivraison
                );
            }
        }

        $fraisLivraison = $request->modeLivraison === 'domicile'
            ? config('claireafrique.frais_livraison_domicile', 2000)
            : 0;

        return CommandeData::fromRequest(
            $request,
            $userId,
            $panier->calculerMontant() + $fraisLivraison,
            $fraisLivraison
        );
    }
}
