@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Rashi</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/rashi') }}">Rashi</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/rashi/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="rashiid" value="{{ $data['rashi']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['rashi']->title }}" >
                  <div class="invalid-feedback">
                    Please enter the rashi title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">English Title</label>
                  <input type="text" name="engtitle" class="form-control" id="validationCustom01" value="{{ $data['rashi']->engtitle }}" >
                  <div class="invalid-feedback">
                    Please enter the English title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Icon Upload</label>
                    <input class="form-control" type="file" name="rashiicon" accept="image/*" id="formFile">
                    @if($data['rashi']->rashiicon)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/rashi/icon/'.$data['rashi']->rashiicon;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['rashi']->rashiicon }}" />
                    @endif
                    @error('icon')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="col-form-label">Rashi word</label>
                  <input type="text" name="rashiword" class="form-control" value="{{ $data['rashi']->rashiword }}" id="validationCustom03">
                  <div class="invalid-feedback">
                    Please enter the rashi word.
                  </div>
                  @error('metatitle')
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