@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Rashi Data</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/rashidata') }}">Rashi Data</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/rashidata/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="rashifalid" value="{{ $data['rashidata']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Select Rashi</label>
                  <select class="form-select" name="rashifal_id" id="validationCustom03" required>
                    <option selected  value="">Choose...</option>
                    @foreach ($data['rashi'] as $rashi)
                        <option value="{{ $rashi->id }}" @if($rashi->id==$data['rashidata']->rashifal_id) {{ 'selected' }} @endif>{{ $rashi->title }}</option>
                    @endforeach
                  </select>
                  <div class="invalid-feedback">
                    Please select the Rashi.
                  </div>
                  @error('ecatID')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Author name</label>
                  <input type="text" name="authorname" class="form-control" id="validationCustom01" value="{{  $data['rashidata']->authorname }}" required />
                  <div class="invalid-feedback">
                    Please enter the rashi author name.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Description</label>
                  <!-- <input type="text" name="rashidescription" class="form-control" id="validationCustom01" value="{{ $data['rashidata']->rashidescription }}" required /> -->
                  <textarea class="form-control newfeatures" name="rashidescription" style="height: 100px">{{ $data['rashidata']->rashidescription }}</textarea>
                  <div class="invalid-feedback">
                    Please enter the Description.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
              
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Author Icon Upload</label>
                    <input class="form-control" type="file" name="authoricon" accept="image/*" id="formFile">
                    @if($data['rashidata']->authoricon)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/rashi/author/'.$data['rashidata']->authoricon;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['rashidata']->authoricon }}" />
                    @endif
                    @error('icon')
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