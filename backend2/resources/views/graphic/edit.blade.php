@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Ads Graphic</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/graphic') }}">Ads Graphic</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/graphic/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="gid" value="{{ $data['graphic']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['graphic']->title }}" >
                  <div class="invalid-feedback">
                    Please enter the title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Icon Upload</label>
                    <input class="form-control" type="file" name="icon" accept="image/*" id="formFile">
                    @if($data['graphic']->images)
                    <img src="{{asset('public/uploads/graphic').'/'.$data['graphic']->images }}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['graphic']->images }}" />
                    @endif
                    @error('icon')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="col-form-label">Site Link</label>
                  <input type="text" name="link" class="form-control" value="{{ $data['graphic']->sitelink }}" id="validationCustom03">
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