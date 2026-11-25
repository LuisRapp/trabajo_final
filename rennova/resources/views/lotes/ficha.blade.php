@extends('layouts.app')

@section('title', 'Lote')

@section('content')
    @livewire('ficha-lote', ['loteId' => $loteId])
@endsection
