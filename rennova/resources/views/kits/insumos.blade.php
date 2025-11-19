@extends('layouts.app')

@section('content')
    @livewire('configuracion-insumos-kit', ['kitId' => $kit->id_kit_preventivo])
@endsection
