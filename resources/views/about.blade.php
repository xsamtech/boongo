@extends('layouts.guest')

@section('guest-content')

    @if (Route::is('about.home'))
        @include('partials.about.about')
    @endif

    @if (Route::is('about.entity'))
        @include('partials.about.' . $entity)
    @endif

@endsection
