@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-cog mr-2"></i>Library Settings
                    </h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-outline-secondary float-right" href="{{ route('library.dashboard') }}">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        @include('adminlte-templates::common.errors')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">Loan &amp; fine policy</h3>
                    </div>

                    {!! Form::open(['route' => ['library.settings.update'], 'method' => 'PATCH']) !!}
                        <div class="card-body">
                            <p class="text-muted">
                                These two values were previously fixed in code at a 14 day loan
                                and KES 50 per day. Saving here changes both without a deploy.
                            </p>

                            <div class="form-group">
                                {!! Form::label('loan_period_days', 'Loan period (days)') !!}
                                {!! Form::number('loan_period_days', $settings['loan_period_days'], [
                                    'class' => 'form-control',
                                    'min' => 1,
                                    'max' => 365,
                                    'required',
                                ]) !!}
                                <small class="form-text text-muted">
                                    How long a book may be kept before it is due. This is the
                                    default due date on a new loan; a librarian can still
                                    override it per issue.
                                </small>
                            </div>

                            <div class="form-group">
                                {!! Form::label('fine_per_day', 'Fine per day overdue') !!}
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">KES</span>
                                    </div>
                                    {!! Form::number('fine_per_day', $settings['fine_per_day'], [
                                        'class' => 'form-control',
                                        'step' => '0.50',
                                        'min' => 0,
                                        'max' => 100000,
                                        'required',
                                    ]) !!}
                                </div>
                                <small class="form-text text-muted">
                                    Charged per day a book is past its due date. Enter
                                    <strong>0</strong> if the school does not charge fines.
                                </small>
                            </div>
                        </div>

                        <div class="card-footer">
                            {!! Form::submit('Save Settings', ['class' => 'btn btn-primary']) !!}
                            <a href="{{ route('library.dashboard') }}" class="btn btn-default">Cancel</a>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">What these affect</h3>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Loan period</dt>
                            <dd class="text-muted">
                                The default due date when issuing a book, and the suggested due
                                date on the issue form.
                            </dd>

                            <dt>Fine per day</dt>
                            <dd class="text-muted">
                                The fine stored on an issue when a book comes back late, and the
                                amount shown against overdue books on the dashboard.
                            </dd>
                        </dl>

                        <hr>

                        <p class="mb-0 text-muted small">
                            Changing the fine rate does not re-price fines already stored on
                            returned issues. Books that are still out are re-costed on the
                            dashboard against the new rate.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
