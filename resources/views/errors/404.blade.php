{{-- resources/views/errors/404.blade.php --}}
@extends('errors::layout')
@section('title', 'Ce rayon n\'existe pas')
@section('code', '404')
@section('image')
    <img src="{{ asset('images/errors/404.svg') }}" alt="Rayon introuvable" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Vous avez suivi une piste vers une étagère qui n'a jamais existé, ou qui a été retirée du catalogue. Vérifiez la référence, ou repartez du hall d'entrée.
@endsection
@section('button')
    <a href="{{ url('/') }}" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Retourner au hall d'entrée
    </a>
@endsection
