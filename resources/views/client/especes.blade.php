@extends('layouts.client')
@section('title', 'Confirmation — Retrait boutique')

@section('content')
@include('client._etapes-commande', ['etape' => 3])
<div class="max-w-md mx-auto px-6 py-16 text-center">
    <div class="bg-white border border-primary-pale rounded-2xl p-8 shadow-sm">
        <div class="text-5xl mb-4"><x-heroicon-o-building-storefront class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
        
        <h1 class="text-lg font-bold text-primary-dark mb-2">
            Retrait en boutique
        </h1>
        
        <p class="text-gray-500 text-sm mb-6 leading-relaxed">
            Votre commande sera préparée par nos équipes. Rendez-vous
            @if(!empty($session['pointVente']))
                <strong class="text-primary-dark font-semibold">au point de vente {{ $session['pointVente'] === 'ucad' ? 'UCAD' : 'Centre-ville' }}</strong>
            @else
                en librairie
            @endif
            pour la récupérer et régler 
            <strong class="text-primary-dark font-bold">
                {{ number_format($session['total'], 0, ',', ' ') }} F CFA
            </strong> 
            en espèces.
        </p>

        <div class="bg-primary-bg/60 rounded-xl p-4 mb-6 text-left border border-primary-pale">
            <div class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">
                Récapitulatif
            </div>
            
            <div class="flex justify-between text-sm py-1">
                <span class="text-gray-600">Mode de livraison</span>
                <span class="font-medium text-gray-800"><x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retrait boutique</span>
            </div>
            
            <div class="flex justify-between text-sm py-1">
                <span class="text-gray-600">Paiement</span>
                <span class="font-medium text-gray-800"><x-heroicon-o-banknotes class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Espèces sur place</span>
            </div>
            
            <div class="flex justify-between text-sm mt-2 pt-2 border-t border-primary-pale/80">
                <span class="font-semibold text-gray-900">Total à régler</span>
                <span class="font-bold text-primary-dark text-base">
                    {{ number_format($session['total'], 0, ',', ' ') }} F CFA
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('client.commande.especes.confirmer') }}">
            @csrf
            <button type="submit"
                class="w-full bg-primary hover:bg-primary-dark text-white py-3.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg">
                <x-heroicon-o-check-circle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Confirmer la commande
            </button>
        </form>

        <a href="{{ route('client.recapitulatif') }}"
            class="block text-center text-xs text-gray-400 hover:text-gray-600 hover:underline mt-4 transition-colors">
            <x-heroicon-o-arrow-left class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Modifier le choix de paiement
        </a>
    </div>
</div>
@endsection