@extends('layouts.app')

@section('content')
    @livewire('programar-mantenimiento', ['notificacionId' => $notificacionId])
@endsection
