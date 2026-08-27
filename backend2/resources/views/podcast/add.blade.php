@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Podcast</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/podcast') }}">Podcast</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/podcast/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Select Podcast Author</label>
                  <select class="form-select" name="authorid" id="validationCustom03" required>
                    <option selected  value="">Choose...</option>
                    @foreach ($data['podcastauthor'] as $podcastauthor)
                        <option value="{{ $podcastauthor->id }}" @if($podcastauthor->id==old('authorid')) {{ 'selected' }} @endif>{{ $podcastauthor->authorname }}</option>
                    @endforeach
                  </select>
                  <div class="invalid-feedback">
                    Please select the Podcast author.
                  </div>
                  @error('authorid')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Podcast Title</label>
                  <input type="text" name="podcastTitle" class="form-control" id="validationCustom01" value="{{ old('podcastTitle') }}" required />
                  <div class="invalid-feedback">
                    Please enter the podcast Title.
                  </div>
                  @error('podcastTitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Podcast Description</label>
                  <!-- <input type="text" name="podcastTitle" class="form-control" id="validationCustom01" value="{{ old('podcastTitle') }}"  /> -->
                  <textarea name="podcastDescription" class="form-control newfeatures" id="validationCustom01">{{ old('podcastDescription') }}</textarea>
                   <div class="invalid-feedback">
                    Please enter the podcast Description.
                  </div>
                  @error('podcastDescription')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Audio clip</label>
                    <input class="form-control" type="file" name="audioclip" accept="image/*" id="formFile">
                    @error('audioclip')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div> 
              
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Audio clip Image</label>
                    <input class="form-control" type="file" name="audioclipimg" accept="image/*" id="formFile">
                    @error('audioclipimg')
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