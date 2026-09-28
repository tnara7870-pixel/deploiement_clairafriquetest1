{{-- resources/views/errors/500.blade.php --}}
@extends('errors::layout')
@section('title', 'Désordre dans les archives')
@section('code', '500')
@section('image')
    <img src="{{ asset('images/errors/500.svg') }}" alt="Erreur interne" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Un dossier a été mal rangé quelque part dans nos réserves. Notre équipe d'archivistes en a été prévenue et s'en occupe déjà. Réessayez dans quelques instants.
@endsection
@section('button')
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
            Réessayer
        </button>
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-primary text-primary hover:bg-primary-pale font-medium text-sm rounded-lg transition-colors">
            Retourner au hall d'entrée
        </a>
    </div>
@endsection
