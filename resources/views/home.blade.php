@extends('layouts.auth')

@section('title', 'MENRO Home')

@section('content')
<section class="glass-card">
    <div class="brand-stack">
        <p class="kicker">MENRO</p>
        <h1 class="brand-title">Welcome, {{ auth()->user()->name }}</h1>
        <p class="brand-copy">You are signed in.</p>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="home-actions">
        @csrf
        <button class="button button-secondary" type="submit">Log out</button>
    </form>
</section>
@endsection
