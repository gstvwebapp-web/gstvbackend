@extends('layouts.app')

@section('content')
<style>
  .admin-profile-photo {
    display: flex;
    position: relative;
    max-width:100px;
    max-height:100px;
    margin-top: 10px;
    border-radius: 100px;
  }
  .admin-profile-photo img {
    max-width:70px;
    max-height:70px;
    min-width:70px;
    min-height:70px;
    border-radius:70px;
  }
  .admin-profile-photo .btn_photo_remove {
    width: 25px;
    height: 25px;
    background: red;
    border-radius: 30px;
    align-items: center;
    display: flex;
    justify-content: center;
    line-height: normal;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    position: absolute;
    right: 24px;
    top: 4px;
    border: 1px solid #fff;
}
</style>
<main id="main" class="main">

    <div class="pagetitle">
      <h1>User</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/user') }}">Users</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/user/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="uid" value="{{ $data['user']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Name</label>
                  <input type="text" name="name" class="form-control" id="validationCustom01" value="{{ $data['user']->name }}" required>
                  <div class="invalid-feedback">
                    Please enter the full name.
                  </div>
                  @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" id="validationCustom02" value="{{ $data['user']->email }}" required>
                  <div class="invalid-feedback">
                    Please enter the email address.
                  </div>
                  @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="" class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" id="" value="">
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Profile Photo</label>
                    <input class="form-control" type="file" name="profileImg" accept="image/*" id="profileImg">
                    @if($data['user']->profileImg)
                    <div class="admin-profile-photo">
                      <img src="{{config('app.backendnewsurl').'/public/uploads/profile/'.$data['user']->profileImg }}" id="getprofileimg"/>
                      <div id="removeuserProfile" class="btn_photo_remove" onclick="removeuserProfile({{ $data['user']->id }})" >X</div>
                    </div>
                    <input type="hidden" id="oldImg" name="oldImg" value="{{ $data['user']->profileImg }}" />
                    @endif
                    @error('profileImg')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom07" class="form-label">Select Role</label>
                  <select class="form-select" name="roleID" id="validationCustom07" required>
                    <option selected  value="">Choose...</option>
                    @foreach ($data['role'] as $role)
                        <option value="{{ $role->id }}" @if($role->id==$data['user']->roleID) {{ 'selected' }} @endif>{{ $role->title }}</option>
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
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="allmodule"/> <strong>Check All</strong>
                    </div>
                    @foreach ($data['module'] as $module)
                    <div class="form-check">
                        <input class="form-check-input" name="moduleID[]" @if(in_array($module->id, explode(",",$data['user']->moduleID))) {{ 'checked'}} @endif type="checkbox" value="{{ $module->id }}" id="module_{{ $module->id }}" />
                        <label class="form-check-label" for="module_{{ $module->id }}">
                        {{ $module->title }}
                        </label>
                    </div>
                    @endforeach
                    @error('moduleID')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-6 pt-3">
                    <label for="validationCustom04" class="form-label"><strong>Categories </strong></label>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="allcatID"/> <strong>Check All</strong>
                    </div>
                    <div class="form-check">
                    @foreach ($data['category'] as $parentCategory)
                      @if ($parentCategory->parentID == 0 || $parentCategory->parentID == $parentCategory->id) 
                        <input class="form-check-input" name="catID[]" type="checkbox" @if(in_array($parentCategory->id, explode(",",$data['user']->catID))) {{ 'checked'}} @endif value="{{ $parentCategory->id }}" id="cat{{$parentCategory->id}}" />
                        <label class="form-check-label" for="cat{{$parentCategory->id}}">
                        {{ $parentCategory->title }}
                        </label>
                            <div>
                              @foreach ($data['category'] as $category)
                                  @if ($category->parentID == $parentCategory->id)
                                      <div class="subcat">
                                        <input class="form-check-input" name="catID[]" @if(in_array($category->id, explode(",",$data['user']->catID))) {{ 'checked'}} @endif type="checkbox" value="{{ $category->id }}" id="cat{{$category->id}}" />
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

  <!-- jQuery script -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Custom script to check/uncheck all checkboxes -->
  <script>
      $(document).ready(function(){
          // Handle #allmodule checkbox
          $('#allmodule').on('change', function(){
              var isChecked = $(this).is(':checked');
              $('input[name="moduleID[]"]').prop('checked', isChecked);
          });

          // Handle #allcatID checkbox
          $('#allcatID').on('change', function(){
              var isChecked = $(this).is(':checked');
              $('input[name="catID[]"]').prop('checked', isChecked);
          });
      });
      function removeuserProfile(id){
        $("#oldImg").val('');
        $("#getprofileimg").hide();
        $("#removeuserProfile").hide();
      }
  </script>
@endsection
