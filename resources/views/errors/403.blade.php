{{-- resources/views/errors/403.blade.php --}}
@extends('errors::layout')
@section('title', 'Section réservée aux archivistes')
@section('code', '403')
@section('image')
    <img src="{{ asset('images/errors/403.svg') }}" alt="Accès refusé" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Cette rangée est fermée à clé ou nécessite des autorisations spéciales. Vous n'avez pas le passe-partout requis pour consulter ce rayon.
@endsection
@section('button')
    <a href="{{ url('/') }}" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Retourner au rayon principal
    </a>
@endsection
