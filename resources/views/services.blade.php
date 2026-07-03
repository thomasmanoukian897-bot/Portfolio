@extends('layouts.app')

@section('title', 'Services | Digital Builder')

@section('content')
    @include('components.services-hero')

    @include('components.services-grid')

    @include('components.services-process')

    @include('components.cta')
@endsection
