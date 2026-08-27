@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Election Module</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('electionresultmodule') }}">Election Module</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ $election->title }}</h5>
                            <div>
                                <a href="{{ url('electionresultmodule/edit/' . $election->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <a href="{{ url('electionresultmodule') }}" class="btn btn-secondary btn-sm ms-1">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>State:</strong> {{ $election->state_name ?? '—' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Election Title:</strong> {{ $election->title }}
                            </div>
                            <div class="col-md-4">
                                <strong>Total Seats:</strong> {{ $election->total_seats }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Author:</strong> {{ $election->author_name ?? '—' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Created:</strong> {{ $election->created_at ? \Carbon\Carbon::parse($election->created_at)->format('d M Y, h:i A') : '—' }}
                            </div>
                        </div>

                        <hr>

                        @if($columns->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Party Name</th>
                                            @foreach($columns as $col)
                                                <th>{{ $col->column_name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $row)
                                            <tr>
                                                <td><strong>{{ $row->party_name }}</strong></td>
                                                @foreach($columns as $col)
                                                    <td>
                                                        {{ optional(isset($values[$row->id]) ? $values[$row->id]->where('column_id', $col->id)->first() : null)->value ?? '—' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $columns->count() + 1 }}" class="text-center text-muted">No party data found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No columns defined for this election module.</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
