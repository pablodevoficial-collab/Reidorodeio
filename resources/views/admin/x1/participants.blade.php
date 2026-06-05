@extends('admin.layouts.app')

@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')
<div class="page-header mb-4">
    <h2 class="page-title">Participantes - Sala #{{ $room->id }}</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr><th>ID</th><th>Usuário</th><th>Slot</th><th>Entrou em</th></tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ optional($p->user)->name }}</td>
                        <td>{{ $p->slot }}</td>
                        <td>{{ $p->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
