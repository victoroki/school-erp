@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="mb-4">
                <i class="fas fa-lock fa-4x text-muted"></i>
            </div>
            <h1 class="display-4 fw-bold text-danger mb-3">403</h1>
            <h2 class="h4 mb-3">Access Denied</h2>
            <p class="text-muted mb-4">{{ $message ?? 'You do not have permission to perform this action.' }}</p>
            <a href="{{ url()->previous() ?? route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
