@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Delete Media</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Delete Media</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
      @if(session('success'))
            <div class="col-lg-12">
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            </div>
            @endif
      @if(session('error'))
            <div class="col-lg-12">
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            </div>
            @endif
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-3">
 

                <form class="row g-3 needs-validation" method="POST" action="{{ route('deletemediaImages') }}" novalidate>
                    @csrf
                    <div class="col-md-6">
                      <label for="startDate" class="form-label">Start Date</label>
                      <input type="date" name="start_date" class="form-control" id="startDate" required>
                      <div class="invalid-feedback">
                        Please enter a valid start date.
                      </div>
                      @error('start_date')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-6">
                      <label for="endDate" class="form-label">End Date</label>
                      <input type="date" name="end_date" class="form-control" id="endDate">
                      <div class="invalid-feedback">
                        Please enter a valid end date.
                      </div>
                      @error('end_date')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </form>
 
            </div>    
          </div>

        </div>
      </div>
    </section>

</main><!-- End #main -->

@endsection
