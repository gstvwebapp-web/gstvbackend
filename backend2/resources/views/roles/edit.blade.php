@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Roles</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/roles') }}">Roles</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/roles/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="rid" value="{{ $data['role']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Role Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ $data['role']->title }}" required>
                  <div class="invalid-feedback">
                    Please enter the role title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                      <input class="form-check-input mt-2" @if($data['role']->viewRole==1) {{ 'checked'}} @endif type="checkbox" name="viewRole" value='1' id="viewRole">
                      <label for="inputNumber" class="col-form-label">View</label>
                </div>
                <div class="col-md-12">      
                      <input class="form-check-input mt-2" @if($data['role']->addRole==1) {{ 'checked'}} @endif type="checkbox" name="addRole" value='1' id="addRole">
                      <label for="inputNumber" class="col-form-label">Add</label>
                </div>
                <div class="col-md-12">
                      <input class="form-check-input mt-2" type="checkbox" @if($data['role']->editRole==1) {{ 'checked'}} @endif name="editRole" value='1' id="editRole">
                      <label for="inputNumber" class="col-form-label">Edit</label>
                </div>
                <div class="col-md-12">   
                      <input class="form-check-input mt-2" type="checkbox" @if($data['role']->deleteRole==1) {{ 'checked'}} @endif name="deleteRole" value='1' id="deleteRole">
                      <label for="inputNumber" class="col-form-label">Delete</label>
                </div>
                <div class="col-md-12">   
                      <input class="form-check-input mt-2" type="checkbox" @if($data['role']->publishRole==1) {{ 'checked'}} @endif name="publishRole" value='1' id="publishRole">
                      <label for="inputNumber" class="col-form-label">Publish / Unpublish</label>
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