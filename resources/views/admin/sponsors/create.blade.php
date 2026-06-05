@extends('admin.layouts.app')

@section('panel')
    @include('admin.sponsors._form', [
        'action' => route('admin.sponsors.store'),
    ])
@endsection
