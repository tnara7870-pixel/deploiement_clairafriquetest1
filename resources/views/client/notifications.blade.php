@extends('layouts.client')
@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
    @include('shared._notifications-liste')
</div>
@endsection
