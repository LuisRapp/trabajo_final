@extends('layouts.app')

@section('content')
<div class="w-full">
    @livewire('allocation-proposals', ['loteId' => $loteId])
</div>
@endsection
