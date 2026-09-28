<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->bothify('REF-####'),
            'designation' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'prix' => fake()->numberBetween(500, 20000),
            'quantiteStock' => 10,
            'statut' => true,
            'idCategorie' => Categorie::factory(),
        ];
    }
}
