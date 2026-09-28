{{-- resources/views/errors/419.blade.php --}}
@extends('errors::layout')
@section('title', 'Votre ticket a expiré')
@section('code', '419')
@section('image')
    <img src="{{ asset('images/errors/419.svg') }}" alt="Session expirée" class="w-64 h-64 object-contain">
@endsection
@section('message')
    Le jeton qui vous autorisait à remplir ce formulaire n'est plus valide — sans doute une visite un peu trop longue dans les rayons. Rechargez la page et réessayez, votre carte de lecteur reste valable.
@endsection
@section('button')
    <button type="button" onclick="window.location.reload()" class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-light text-white font-medium text-sm rounded-lg transition-colors">
        Recharger la page
    </button>
@endsection
