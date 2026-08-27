@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Categories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/gift') }}">Gift</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/gift/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="giftid" value="{{ $data['gift']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Gift Title</label>
                  <input type="text" name="gifttitle" class="form-control" id="validationCustom01" value="{{ $data['gift']->gifttitle }}" >
                  <div class="invalid-feedback">
                    Please enter the gift title.
                  </div>
                  @error('gifttitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Icon Upload</label>
                    <input class="form-control" type="file" name="giftimage" accept="image/*" id="formFile">
                    @if($data['gift']->giftimage)
                    <img src="{{$data['gift']->giftimage}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['gift']->giftimage }}" />
                    @endif
                    @error('giftimage')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                  <label for="validationCustom03" class="col-form-label">Coins</label>
                  <input type="text" name="coins" class="form-control" value="{{ $data['gift']->coins }}" id="validationCustom03">
                  <div class="invalid-feedback">
                    Please enter the coins.
                  </div>
                  @error('coins')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom08" class="col-form-label">Quantity</label>
                  <input type="text" name="qty" value="{{ $data['gift']->qty }}" class="form-control" id="validationCustom08" />
                </div> 
                <div class="col-md-12">
                  <label for="validationCustom09" class="col-form-label">Expire Date</label>
                  <input type="date" name="expiredate" value="{{ $data['gift']->expiredate }}" class="form-control" id="validationCustom09" />
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