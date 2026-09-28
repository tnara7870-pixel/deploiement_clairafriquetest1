{{-- resources/views/errors/401.blade.php --}}
@extends('errors::layout')
@section('title', 'Présentez votre carte de lecteur')
@section('code', '401')
@section('image')
    <img src="{{ asset('images/errors/401.svg') }}" alt="Authentification requise" class="w-64 h-64 object-contain">
@endsection
@section('message')
    L'accès à cette section est réservé aux membres inscrits. Merci de vous identifier au comptoir avant de poursuivre votre visite.
@endsection
@section('button')
    <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Se connecter
    </a>
@endsection
