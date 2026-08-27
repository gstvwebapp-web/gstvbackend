@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Side Menus</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/sidemenu') }}">Side Menu</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/sidemenu/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="sid" value="{{ $data['sidemenu']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Menu Title</label>
                  <input type="text" name="menu" class="form-control" id="validationCustom01" value="{{ $data['sidemenu']->menu }}" >
                  <div class="invalid-feedback">
                    Please enter the menu title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Menu Action</label>
                  <input type="text" name="menuaction" class="form-control" id="validationCustom02" value="{{ $data['sidemenu']->menuaction }}" >
                  <div class="invalid-feedback">
                    Please enter the menu action.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Icon Upload</label>
                    <input class="form-control" type="file" name="icon" accept="image/*" id="formFile">
                    @if($data['sidemenu']->menuicon)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/sidemenu/'.$data['sidemenu']->menuicon;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['sidemenu']->menuicon }}" />
                    @endif
                    @error('icon')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="loginstatus" value="1" @if($data['sidemenu']->loginstatus==1) {{ 'checked' }} @endif id="loginstatus" />
                      <label class="form-check-label" for="loginstatus">Login Menu Show</label>
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