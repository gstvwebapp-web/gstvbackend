@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Change Password</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Change Password</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
      @if(session('error'))
        <div class="col-lg-12">
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
        </div>
      @endif
      @if(session('success'))
        <div class="col-lg-12">
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
        </div>
      @endif
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
           
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/changepassword/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Current Password</label>
                  <input type="password" name="currentpassword" class="form-control" id="validationCustom01" value="{{ old('currentpassword') }}" required>
                  <div class="invalid-feedback">
                    Please enter the current password.
                  </div>
                  @error('currentpassword')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">New Password</label>
                  <input type="password" name="newpassword" class="form-control" id="validationCustom02" value="{{ old('newpassword') }}" required>
                  <div class="invalid-feedback">
                    Please enter the new password.
                  </div>
                  @error('newpassword')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Confirm New Password</label>
                  <input type="password" name="newpassword_confirmation" class="form-control" id="validationCustom03" value="{{ old('newpassword_confirmation') }}" required>
                  <div class="invalid-feedback">
                    Please enter the confirm new password.
                  </div>
                  @error('newpassword_confirmation')
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