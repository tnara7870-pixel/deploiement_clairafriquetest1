<?php

namespace App\Http\Requests;

use App\Rules\ValeurArticleNonDupliquee;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Extrait de Stock\ArticleController::update(). Volontairement sans
 * champ "quantiteStock" : la quantité en stock ne se modifie que via un
 * Mouvement de stock (traçabilité obligatoire) — voir CommandeAnnulationService
 * et MouvementController pour le mécanisme équivalent côté commandes.
 */
class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'designation' => ['required', 'max:150', new ValeurArticleNonDupliquee('designation', (int) $this->route('id'))],
            'prix' => 'required|numeric|min:0',
            'idCategorie' => 'required|exists:categories,idCategorie',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'seuilMinimal' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'designation.required' => 'La désignation est obligatoire.',
            'designation.max' => 'La désignation ne doit pas dépasser 150 caractères.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'idCategorie.required' => 'La catégorie est obligatoire.',
            'idCategorie.exists' => 'La catégorie sélectionnée n’existe pas.',
            'image.mimes' => 'L’image doit être au format jpg, jpeg, png ou webp.',
            'image.max' => 'L’image ne doit pas dépasser 2 Mo.',
            'seuilMinimal.integer' => 'Le seuil minimal doit être un nombre entier.',
            'seuilMinimal.min' => 'Le seuil minimal ne peut pas être négatif.',
        ];
    }
}
