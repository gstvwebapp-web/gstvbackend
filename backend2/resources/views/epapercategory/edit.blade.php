@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Epaper Categories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/epapercategory') }}">Epaper Category</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/epapercategory/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="catid" value="{{ $data['epapercategory']->id }}" />
                <div class="col-md-12">
                    <label for="validationCustom03" class="form-label">Select Type</label>
                    <select class="form-select" name="cattype" id="validationCustom03" required>
                          <option selected  value="">Choose...</option>
                          <option value="Newspaper" @if($data['epapercategory']->cattype=='Newspaper') {{ 'selected' }} @endif >Newspaper</option>
                          <option value="Magazine" @if($data['epapercategory']->cattype=='Magazine') {{ 'selected' }} @endif >Magazine</option>
                          <!-- <option value="Buddhiprakash" @if($data['epapercategory']->cattype=='Buddhiprakash') {{ 'selected' }} @endif >Buddhiprakash</option> -->
                    </select>
                    <div class="invalid-feedback">
                      Please Select Type.
                    </div>
                    @error('title')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Category Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['epapercategory']->title }}" >
                  <div class="invalid-feedback">
                    Please enter the category title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">English Category Title</label>
                  <input type="text" name="engtitle" class="form-control" id="validationCustom01" value="{{ $data['epapercategory']->engtitle }}" >
                  <div class="invalid-feedback">
                    Please enter the englisht category title.
                  </div>
                  @error('engtitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                  <label for="validationCustom03" class="col-form-label">Meta Title</label>
                  <input type="text" name="metatitle" class="form-control" value="{{ $data['epapercategory']->metatitle }}" id="validationCustom03">
                  <div class="invalid-feedback">
                    Please enter the meta title.
                  </div>
                  @error('metatitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                  <input type="text" name="metakeyword" value="{{ $data['epapercategory']->metakeyword }}" class="form-control" id="validationCustom08" />
                </div>
                <div class="col-md-12">
                  <label for="inputPassword" class="col-form-label">Meta Description</label>
                  <textarea class="form-control" name="metadesc" style="height: 100px">{{ $data['epapercategory']->metadesc }}</textarea>
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