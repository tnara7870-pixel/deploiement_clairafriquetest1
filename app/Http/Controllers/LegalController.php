<?php

namespace App\Http\Controllers;

/**
 * Pages légales publiques, accessibles sans connexion : conditions
 * générales d'utilisation et politique de confidentialité (données
 * personnelles + cookies), conformément à la loi n°2008-12 du 25 janvier
 * 2008 relative à la protection des données à caractère personnel et à
 * la loi n°2008-08 du 25 janvier 2008 sur les transactions électroniques.
 */
class LegalController extends Controller
{
    public function cgu()
    {
        return view('legal.cgu');
    }

    public function confidentialite()
    {
        return view('legal.confidentialite');
    }
}
