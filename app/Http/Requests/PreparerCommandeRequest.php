<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreparerCommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modeLivraison' => 'required|in:domicile,boutique',
            'adresse' => 'required_if:modeLivraison,domicile|nullable|string|max:255',
            'pointVente' => 'required_if:modeLivraison,boutique|nullable|in:ucad,centre_ville',
            // Les coordonnées sont un complément optionnel à l'adresse
            // texte (jamais un remplacement) — voir migration. Bornes
            // larges couvrant le Sénégal, pas seulement Dakar.
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'modePaiement' => 'required|in:wave,orange_money,especes',
        ];
    }

    public function messages(): array
    {
        return [
            'modeLivraison.required' => 'Le mode de livraison est obligatoire.',
            'adresse.required_if' => 'L’adresse est obligatoire pour la livraison à domicile.',
            'pointVente.required_if' => 'Le point de vente est obligatoire pour un retrait en boutique.',
            'pointVente.in' => 'Le point de vente sélectionné n’est pas valide.',
            'modePaiement.required' => 'Le mode de paiement est obligatoire.',
            'modePaiement.in' => 'Le mode de paiement sélectionné n’est pas valide.',
        ];
    }
}
