@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Start</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/electionresults/electionstar') }}">Election Start</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/electionresults/updateelectionstar') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $data['electionstar']->id }}" />
                <div class="col-md-12">
                      <label for="validationCustom07" class="form-label">Select State</label>
                      <select class="form-select" name="stateId" id="validationCustom01" required>
                        <option value="">Choose...</option>
                        @foreach ($data['state'] as $state)
                        <option value="{{ $state->id }}" @if($state->id==$data['electionstar']->stateId) {{ 'selected' }} @endif>{{ $state->name }}</option>
                        @endforeach
                      </select>
                      <div class="invalid-feedback">
                        Please Select status.
                      </div>
                      @error('party_status')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom07" class="form-label">Select status</label>
                  <select class="form-select" name="party_status" id="validationCustom07" required>
                        <option selected  value="">Choose...</option>
                        <option @if($data['electionstar']->party_status == "Won") {{ 'selected' }} @endif value="Won" >Won</option>
                        <option @if($data['electionstar']->party_status == "Lost") {{ 'selected' }} @endif value="Lost" >Lost</option>
                  </select>
                  <div class="invalid-feedback">
                    Please Select status.
                  </div>
                  @error('party_status')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                 
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['electionstar']->title }}" required />
                  <div class="invalid-feedback">
                    Please enter the title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">City</label>
                  <input type="text" name="city" class="form-control" id="validationCustom01" value="{{ $data['electionstar']->city }}" required />
                  <div class="invalid-feedback">
                    Please enter the city.
                  </div>
                  @error('city')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Party Type</label>
                  <input type="text" name="partytype" class="form-control" id="validationCustom02" value="{{ $data['electionstar']->partytype }}" required />
                  <div class="invalid-feedback">
                    Please enter the Party Type.
                  </div>
                  @error('partytype')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Select Image</label>
                  <input class="form-control" type="file" name="files" accept="image/*"  id="formFile">
                   @error('files')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="file-container" id="fileContainer">
                  
                    <div class="col-md-2 file-item" id="imagestar">
                      @php
                      $imagePath = $data['electionstar']->image;
                      @endphp
                      <img src="{{ $imagePath}}">
                      <input type="hidden" name="exitsfiles" id="exitsfiles" value="{{ $imagePath }}">
                    </div>                    
                    </div>
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