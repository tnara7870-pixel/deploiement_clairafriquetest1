@php
    // Choix d'icône par mots-clés plutôt qu'un emoji cyclique sans rapport
    // avec le contenu réel de la catégorie. Liste volontairement large
    // (synonymes courants en français) ; repli sur une icône neutre
    // (étagère de livres) si rien ne correspond.
    $nom = mb_strtolower($nom ?? '');

    $icone = match (true) {
        str_contains($nom, 'roman') || str_contains($nom, 'littérat') || str_contains($nom, 'litterat')
            || str_contains($nom, 'poés') || str_contains($nom, 'poes') || str_contains($nom, 'récit')
            => 'book-open',

        str_contains($nom, 'scolaire') || str_contains($nom, 'école') || str_contains($nom, 'ecole')
            || str_contains($nom, 'cahier') || str_contains($nom, 'classe')
            => 'academic-cap',

        str_contains($nom, 'bureau') || str_contains($nom, 'papeterie') || str_contains($nom, 'fourniture')
            => 'briefcase',

        str_contains($nom, 'art') || str_contains($nom, 'dessin') || str_contains($nom, 'peintur')
            || str_contains($nom, 'coloriage') || str_contains($nom, 'créatif') || str_contains($nom, 'creatif')
            => 'paint-brush',

        str_contains($nom, 'informati') || str_contains($nom, 'électro') || str_contains($nom, 'electro')
            || str_contains($nom, 'tech') || str_contains($nom, 'numérique') || str_contains($nom, 'numerique')
            => 'computer-desktop',

        str_contains($nom, 'carte') || str_contains($nom, 'correspond') || str_contains($nom, 'enveloppe')
            => 'envelope',

        str_contains($nom, 'jeunesse') || str_contains($nom, 'enfant') || str_contains($nom, 'bébé') || str_contains($nom, 'bebe')
            => 'face-smile',

        str_contains($nom, 'religio') || str_contains($nom, 'spiritu') || str_contains($nom, 'coran')
            || str_contains($nom, 'bible')
            => 'sparkles',

        str_contains($nom, 'cuisine') || str_contains($nom, 'recette')
            => 'cake',

        str_contains($nom, 'histoire') || str_contains($nom, 'science') || str_contains($nom, 'essai')
            || str_contains($nom, 'document')
            => 'academic-cap',

        default => 'book-open',
    };

    $classes = $attributes->get('class', 'w-6 h-6');
@endphp

<x-dynamic-component :component="'heroicon-o-' . $icone" :class="$classes" />
