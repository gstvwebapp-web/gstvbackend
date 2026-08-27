@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Modules</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/module') }}">Modules</a></li>
          <li class="breadcrumb-item active">Add</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/module/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Module Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ old('title') }}" required>
                  <div class="invalid-feedback">
                    Please enter the module title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Module Link</label>
                  <input type="text" name="moduleLink" value="{{ old('moduleLink') }}" class="form-control" id="validationCustom02" required>
                  <div class="invalid-feedback">
                    Please enter the module link
                  </div>
                  @error('moduleLink')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                
                
                <div class="col-12">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div>
              </form><!-- End Custom Styled Validation -->

            </div>
          </div>

        

        </div>
      </div>
    </section>

     

  </main><!-- End #main -->
@endsection