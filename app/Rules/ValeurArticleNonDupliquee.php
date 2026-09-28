<?php

namespace App\Rules;

use App\Models\Article;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Empêche la création de deux articles dont un champ texte est
 * fondamentalement le même (ex. désignation "Ligne longue lettre" /
 * "Ligne longue lettres", ou référence "LIVRE-01" / "LIVRES-01"), même
 * quand le texte exact diffère et qu'une contrainte unique() classique ne
 * s'applique donc pas. La comparaison se fait sur une version normalisée
 * (casse, espaces, pluriel simple) plutôt que sur le texte brut.
 */
class ValeurArticleNonDupliquee implements ValidationRule
{
    public function __construct(
        private string $colonne = 'designation',
        private ?int $idArticleExclu = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $normalisee = $this->normaliser($value);

        $existants = Article::query()
            ->when($this->idArticleExclu, fn ($q) => $q->where('idArticle', '!=', $this->idArticleExclu))
            ->pluck($this->colonne);

        foreach ($existants as $valeurExistante) {
            if ($valeurExistante !== null && $this->normaliser($valeurExistante) === $normalisee) {
                $fail(
                    'Une valeur très similaire existe déjà : "'.$valeurExistante.'". '.
                    'Vérifiez qu\'il ne s\'agit pas du même article (singulier/pluriel, espaces, majuscules).'
                );

                return;
            }
        }
    }

    private function normaliser(string $texte): string
    {
        $texte = mb_strtolower(trim($texte));
        $texte = preg_replace('/\s+/', ' ', $texte);
        // Les tirets/underscores/points ne doivent pas suffire à distinguer
        // deux références qui seraient sinon identiques (ex. "LIVRE 01"
        // vs "LIVRE-01" vs "LIVRE_01").
        $texte = preg_replace('/[\s\-_.]+/', ' ', $texte);

        $mots = explode(' ', $texte);
        $mots = array_map(function (string $mot): string {
            // Ne retire un "s" final que sur les mots assez longs, pour
            // éviter de fausser la comparaison sur des mots/codes courts
            // (ex. "gaz", "bus") où le "s" fait partie du mot lui-même.
            return (mb_strlen($mot) > 3 && str_ends_with($mot, 's'))
                ? mb_substr($mot, 0, -1)
                : $mot;
        }, $mots);

        return implode(' ', $mots);
    }
}
