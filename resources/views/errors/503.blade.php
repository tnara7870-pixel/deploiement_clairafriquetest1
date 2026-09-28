{{-- resources/views/errors/503.blade.php --}}
@extends('errors::layout')
@section('title', 'Fermeture pour inventaire')
@section('code', '503')
@section('image')
    <img src="{{ asset('images/errors/503.svg') }}" alt="Maintenance en cours" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Nos archivistes font actuellement l'inventaire des rayons. Nous rouvrons nos portes très bientôt — merci de votre patience.
@endsection
@section('button')
    <button type="button" onclick="window.location.reload()" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Actualiser la page
    </button>
@endsection
