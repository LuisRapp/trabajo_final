@extends('layouts.app')

@section('content')
    @livewire('lote-planificacion-tareas', ['loteId' => $loteId])
@endsection
