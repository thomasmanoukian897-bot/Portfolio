@extends('layouts.app')

@section('title', 'Digital Builder | We Build the Future of the Web')

@section('content')
    <!-- Hero Component -->
    @include('components.hero')

    <!-- Stats Component -->
    @include('components.stats')

    <!-- Bento Grid Performance Component -->
    @include('components.performance')

    <!-- CTA Section Component -->
    @include('components.cta')
@endsection