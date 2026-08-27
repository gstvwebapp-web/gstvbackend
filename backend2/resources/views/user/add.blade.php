@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>User</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/user') }}">Users</a></li>
          <li class="breadcrumb-item active">Add</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
      @if(session('error'))
        <div class="col-lg-12">
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
        </div>
      @endif
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/user/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Name</label>
                  <input type="text" name="name" class="form-control" id="validationCustom01" value="{{ old('name') }}" required>
                  <div class="invalid-feedback">
                    Please enter the full name.
                  </div>
                  @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" id="validationCustom02" value="{{ old('email') }}" required>
                  <div class="invalid-feedback">
                    Please enter the email address.
                  </div>
                  @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" id="validationCustom03" value="{{ old('password') }}" required>
                  <div class="invalid-feedback">
                    Please enter the password.
                  </div>
                  @error('password')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Profile Photo</label>
                    <input class="form-control" type="file" name="profileImg" accept="image/*" id="profileImg">
                    @error('profileImg')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom04" class="form-label">Select Role</label>
                  <select class="form-select" name="roleID" id="validationCustom04" required>
                    <option selected  value="">Choose...</option>
                    @foreach ($data['role'] as $role)
                        <option value="{{ $role->id }}" @if($role->id==old('roleID')) {{ 'selected' }} @endif>{{ $role->title }}</option>
                    @endforeach
                  </select>
                  <div class="invalid-feedback">
                    Please select the user role.
                  </div>
                  @error('roleID')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="row">
                <div class="col-md-6 pt-3">
                <label for="validationCustom04" class="form-label"><strong>Modules</strong></label>
                    
                    @foreach ($data['module'] as $module)
                    <div class="form-check">
                        <input class="form-check-input modulecls" name="moduleID[]" @if(old('moduleID') && in_array($module->id, old('moduleID'))) checked @endif type="checkbox" value="{{ $module->id }}" id="module_{{ $module->id }}"  />
                        <label class="form-check-label" for="module_{{ $module->id }}">
                        {{ $module->title }}
                        </label>
                        </div>
                    @endforeach
                    <div class="invalid-feedback">
                      At least one module must be selected.
                    </div>
                    @error('moduleID')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 pt-3">
                  <label for="validationCustom04" class="form-label"><strong>Categories</strong></label>
                    <div class="form-check">
                    @foreach ($data['category'] as $parentCategory)
                      @if ($parentCategory->parentID == 0 || $parentCategory->parentID == $parentCategory->id) 
                        <input class="form-check-input" name="catID[]" type="checkbox" value="{{ $parentCategory->id }}" @if(old('catID') && in_array($parentCategory->id, old('catID'))) checked @endif id="cat{{$parentCategory->id}}" />
                        <label class="form-check-label" for="cat{{$parentCategory->id}}">
                        {{ $parentCategory->title }}
                        </label>
                            <div>
                              @foreach ($data['category'] as $category)
                                  @if ($category->parentID == $parentCategory->id)
                                      <div class="subcat">
                                        <input class="form-check-input" name="catID[]" type="checkbox" value="{{ $category->id }}" id="cat{{$category->id}}" @if(old('catID') && in_array($category->id, old('catID'))) checked @endif />
                                        <label class="form-check-label" for="cat{{$category->id}}">
                                        {{ $category->title }}
                                        </label>
                                      </div>
                                  @endif
                              @endforeach
                            </div>
                      @endif
                    @endforeach
                    @error('catID')
                    <div class="alert alert-danger">{{ $message }}</div>
                     @enderror
                    </div>
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