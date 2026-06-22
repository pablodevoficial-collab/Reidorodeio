@extends('frontend.layouts.app')

@section('content')
<div class="rr-page">
    @include('frontend.partials.hero')
    @include('frontend.partials.spotlight')
    @include('frontend.partials.steps')
    @include('frontend.partials.footer')
</div>
@endsection
