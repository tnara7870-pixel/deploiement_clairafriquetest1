{{-- resources/views/errors/429.blade.php --}}
@extends('errors::layout')
@section('title', 'Doucement avec les allers-retours')
@section('code', '429')
@section('image')
    <img src="{{ asset('images/errors/429.svg') }}" alt="Trop de requêtes" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Vous avez sollicité le comptoir un peu trop souvent en peu de temps. Le bibliothécaire vous demande de patienter un instant avant de revenir.
@endsection
@section('button')
    <a href="{{ url('/') }}" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Retourner au hall d'entrée
    </a>
@endsection
