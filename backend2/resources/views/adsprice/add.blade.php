@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Ads Price</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/adsprice') }}">Ads Price</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/adsprice/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Ads Position</label>
                  <input type="text" name="ads_position" class="form-control" id="validationCustom01" value="{{ old('ads_position') }}" required />
                  <div class="invalid-feedback">
                    Please enter the ads position.
                  </div>
                  @error('ads_position')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                 <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Ads Price</label>
                  <input type="text" name="price" class="form-control" id="validationCustom03" value="{{ old('price') }}" required />
                  <div class="invalid-feedback">
                    Please enter the ads price.
                  </div>
                  @error('price')
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