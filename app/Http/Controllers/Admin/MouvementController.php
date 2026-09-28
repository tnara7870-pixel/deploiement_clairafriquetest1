<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\MouvementStockRepository;

class MouvementController extends Controller
{
    public function index()
    {
        $repository = new MouvementStockRepository;
        $mouvements = $repository->paginate(request()->only([
            'type',
            'search',
            'date_debut',
            'date_fin',
        ]));

        return view('admin.mouvements', compact('mouvements'));
    }
}
