@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Podcast Author</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/podcastauthor') }}">Podcast Author</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/podcastauthor/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="podcastauthorid" value="{{ $data['podcastauthor']->id }}" />  
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Author name</label>
                  <input type="text" name="authorname" class="form-control" id="validationCustom01" value="{{  $data['podcastauthor']->authorname }}" required />
                  <div class="invalid-feedback">
                    Please enter the author name.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                 <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Author Eng name</label>
                  <input type="text" name="authorengname" class="form-control" id="validationCustom01" value="{{  $data['podcastauthor']->authorengname }}" required />
                  <div class="invalid-feedback">
                    Please enter the author eng name.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                 <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Author Sub title</label>
                  <input type="text" name="authorsubtitle" class="form-control" id="validationCustom01" value="{{  $data['podcastauthor']->authorsubtitle }}" />
                  <div class="invalid-feedback">
                    Please enter the author sub title.
                  </div>
                  @error('authorsubtitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Description</label>
                      <textarea class="form-control" name="authordesc" style="height: 100px">{{  $data['podcastauthor']->authordesc }}</textarea>
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Author Icon Upload</label>
                    <input class="form-control" type="file" name="authorimg" accept="image/*" id="formFile">
                    @if($data['podcastauthor']->authorimg)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/podcast/'.$data['podcastauthor']->authorimg;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['podcastauthor']->authorimg }}" />
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