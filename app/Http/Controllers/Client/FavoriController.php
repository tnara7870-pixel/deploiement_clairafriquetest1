<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Favori;
use Illuminate\Support\Facades\Auth;

class FavoriController extends Controller
{
    public function index()
    {
        $favoris = Favori::with('article.categorie')
            ->where('idUtilisateur', Auth::user()->idUtilisateur)
            ->latest()
            ->get();

        return view('client.favoris', compact('favoris'));
    }

    public function toggle(int $id)
    {
        $article = Article::findOrFail($id);
        $userId = Auth::user()->idUtilisateur;

        $favori = Favori::where('idUtilisateur', $userId)
            ->where('idArticle', $id)
            ->first();

        if ($favori) {
            $favori->delete();
            $message = 'Retiré des favoris.';
        } else {
            Favori::create([
                'idUtilisateur' => $userId,
                'idArticle' => $id,
            ]);
            $message = 'Ajouté aux favoris.';
        }

        return back()->with('success', $message);
    }

    public function toggleAjax(int $id)
    {
        $userId = Auth::user()->idUtilisateur;

        $favori = Favori::where('idUtilisateur', $userId)
            ->where('idArticle', $id)
            ->first();

        if ($favori) {
            $favori->delete();

            return response()->json(['statut' => 'retire']);
        } else {
            Favori::create([
                'idUtilisateur' => $userId,
                'idArticle' => $id,
            ]);

            return response()->json(['statut' => 'ajoute']);
        }
    }
}
