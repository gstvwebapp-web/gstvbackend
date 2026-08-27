@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Categories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/category') }}">Category</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/category/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="catid" value="{{ $data['category']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Category Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['category']->title }}" >
                  <div class="invalid-feedback">
                    Please enter the category title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Category Gujarati Title</label>
                  <input type="text" name="category_name_guj" class="form-control" id="validationCustom01" value="{{ $data['category']->category_name_guj }}" >
                  <div class="invalid-feedback">
                    Please enter the category gujarati title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Icon Upload</label>
                    <input class="form-control" type="file" name="icon" accept="image/*" id="formFile">
                    @if($data['category']->icon)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/category/icon/'.$data['category']->icon;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldImg" value="{{ $data['category']->icon }}" />
                    @endif
                    @error('icon')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="setHome" value="1" @if($data['category']->setHome==1) {{ 'checked' }} @endif id="setHome" />
                      <label class="form-check-label" for="setHome">Set in Home Page</label>
                    </div>
                </div>
                <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="settopvideos" value="1" id="settopvideos" @if($data['category']->settopvideos==1) {{ 'checked' }} @endif  />
                      <label class="form-check-label" for="settopvideos">Set in Top Videos</label>
                    </div>
                </div>
                <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="settopmenucat" value="1" id="settopmenucat" @if($data['category']->settopmenucat==1) {{ 'checked' }} @endif   />
                      <label class="form-check-label" for="settopmenucat">Set in Top Menu Category For Mobile</label>
                    </div>
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Feature Image Upload</label>
                    <input class="form-control" type="file" name="featureImage" accept="image/*" id="formFile">
                    @if($data['category']->featureImage)
                    @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/category/icon/'.$data['category']->featureImage;
                    @endphp
                    <img src="{{$imgurl}}" style="width:30px;"/>
                    <input type="hidden" name="oldfeatureImg" value="{{ $data['category']->featureImage }}" />
                    @endif
                    @error('featureImage')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="eventCat" value="1" @if($data['category']->eventCat==1) {{ 'checked' }} @endif id="eventCat" />
                      <label class="form-check-label" for="eventCat">Event & Festival Category</label>
                    </div>
                </div>
                <div class="col-md-12">
                  <label for="validationCustom04" class="col-form-label">Parent Category</label>
                  <select class="form-select" name="parentID" id="validationCustom04">
                    <option selected  value="0">Choose...</option>
                    @foreach ($data['subcategory'] as $subcategory)
                        <option value="{{ $subcategory->id }}" @if($subcategory->id==$data['category']->parentID) {{ 'selected' }} @endif>{{ $subcategory->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="col-form-label">Meta Title</label>
                  <input type="text" name="metatitle" class="form-control" value="{{ $data['category']->metatitle }}" id="validationCustom03">
                  <div class="invalid-feedback">
                    Please enter the meta title.
                  </div>
                  @error('metatitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                  <input type="text" name="metakeyword" value="{{ $data['category']->metakeyword }}" class="form-control" id="validationCustom08" />
                </div>
                <div class="col-md-12">
                  <label for="inputPassword" class="col-form-label">Meta Description</label>
                  <textarea class="form-control" name="metadesc" style="height: 100px">{{ $data['category']->metadesc }}</textarea>
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