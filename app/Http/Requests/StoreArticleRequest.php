<?php

namespace App\Http\Requests;

use App\Rules\ValeurArticleNonDupliquee;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Extrait de Stock\ArticleController::store(). Démonstration de
 * l'utilisation des FormRequest (au lieu de $request->validate() inline
 * partout dans le projet) sur le module le plus représentatif : c'est
 * ici que se joue la traçabilité du stock, donc là où une validation
 * fiable compte le plus.
 */
class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation d'accès à cette action est déjà garantie par le
        // middleware de route (role:res.stock,administrateur) ; ce module
        // n'a pas de notion de "propriétaire" de l'article à vérifier.
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'unique:articles,reference', new ValeurArticleNonDupliquee('reference')],
            'designation' => ['required', 'max:150', new ValeurArticleNonDupliquee('designation')],
            'prix' => 'required|numeric|min:0',
            'quantiteStock' => 'required|integer|min:0',
            'idCategorie' => 'required|exists:categories,idCategorie',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'seuilMinimal' => 'nullable|integer|min:0',
            // Point de vente qui reçoit le stock de départ. Absent pour un
            // res.stock scopé à un point (son point s'applique
            // automatiquement, voir ArticleService::createArticle).
            'pointVenteInitial' => 'nullable|in:ucad,centre_ville',
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Cette référence est déjà utilisée par un autre article.',
        ];
    }
}
