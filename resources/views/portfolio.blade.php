@extends('layouts.app')

@section('title', 'Portfolio | Digital Builder')

@section('content')
    @include('components.portfolio-hero')

    @include('components.portfolio-grid')

    @include('components.portfolio-testimonials')

    @include('components.cta')
@endsection
