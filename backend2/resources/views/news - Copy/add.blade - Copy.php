@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>News</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/news') }}">News</a></li>
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

                        <form id="newsform" class="row g-3 needs-validation" method="POST"
                            action="{{ url('/news/store') }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            <input type="hidden" name="autosaveid" id="autosaveid" value="" />
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="validationCustom01" class="form-label">Title</label>
                                             <input type="text" name="title" class="form-control" id="validationCustom01"
    value="{{ isset($data['news']) ? $data['news']->title : old('title') }}" required>
                                          	<input type="hidden" value="{{ isset($data['news']) ? $data['news']->id : '' }}" name="usernews_userID" />
                                            <div class="invalid-feedback">
                                                Please enter the news title.
                                            </div>
                                            @error('title')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationCustom11" class="col-form-label">English Title</label>
                                            <input type="text" name="englishTitle" class="form-control"
                                                id="validationCustom11" value="{{ old('englishTitle') }}" required>
                                            <div class="invalid-feedback">
                                                Please enter the english title.
                                            </div>
                                            @error('englishTitle')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <label for="inputPassword" class="col-form-label">Description</label>
                                        <textarea class="tinymce-editor form-control" id="descriptioneditor" name="description" required>{{ isset($data['news']) ? $data['news']->description : old('description') }}</textarea>   
                                        <div class="invalid-feedback">
                                            Please enter the news description.
                                        </div>
                                        @error('description')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- <div class="row">
                  
                  <div class="col-md-6">
                      <label for="inputNumber" class="col-form-label">Upload Video <span class="msgcls">( Only MP4, AVI, MOV, WEBM and WMV videos are allowed, The video size must not exceed 10 MB. )</span></label>
                      <input class="form-control" type="file" name="uploadVideo" accept="video/mp4, video/webm, video/avi, video/mov, video/wmv" id="formFile">
                      @error('uploadVideo')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                  
                    
                    <div class="col-md-6">
                      <label for="inputNumber" class="col-form-label">Video URL</label>
                      <input class="form-control" type="text" placeholder="https://youtu.be/c8Jp3bYPcrc?si=kfppHObQAvaztaYR" name="videoURL" value="{{ old('videoURL') }}" id="formFile">
                    </div>
                    </div>-->

                                    <!--<div class="col-12 pt-3">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div> -->
                                </div>
                                <style>
                                .imgvideocls {
                                    width: 100%;
                                    border: 1px solid #ccc;
                                    padding: 5px 0 0 5px;
                                    float: left;
                                    border-radius: 5px;
                                    text-align: center;
                                    cursor: pointer;
                                }
                                </style>
                                <div class="col-md-3">
                                    <div class="col-md-6" style="float:left;padding:0 5px 0 0;">
                                        <label for="featureImage" class="col-form-label">Image
                                            <!--<span class="msgcls">( Only Webp, JPEG, PNG, JPG, WEBP and GIF images are allowed, The image size must not exceed 1 MB. )</span>-->
                                        </label>
                                        <div><span onclick="imgremove();" id="imgclose" class="imgclosecls">X</span>
                                            <div data-bs-toggle="modal" id="selectedImageContainer"
                                                data-bs-target="#imagevideoModalCenter" class="imgvideocls">Image</div>
                                        </div>
                                        <input class="form-control" type="hidden" name="featureImage" value=""
                                            id="featureImage">
                                        <div class="invalid-feedback">
                                            Please select the image.
                                        </div>
                                        @error('featureImage')
                                        <div class="alert alert-danger" style="float:left;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" style="float:left;">
                                        <label for="featureVideo" class="col-form-label">Video
                                            <!--<span class="msgcls">( Only Webp, JPEG, PNG, JPG, WEBP and GIF images are allowed, The image size must not exceed 1 MB. )</span>-->
                                        </label>
                                        <div><span onclick="videoremove();" id="videoclose"
                                                class="videoclosecls">X</span>
                                            <div data-bs-toggle="modal" id="selectedVideoContainers"
                                                data-bs-target="#videoModalCenters" class="imgvideocls">Video</div>
                                        </div>
                                        <input class="form-control" type="hidden" name="videoURL" value=""
                                            id="featureVideo">
                                    </div>


                                    <!--<div class="col-md-12">
                    <label for="imageURL" class="col-form-label">Image URL</label>
                    <input class="form-control" type="text" placeholder="" name="imageURL" value="" id="imageURL">
                  </div>
                <div class="col-md-12">
                <label for="catID" class="col-form-label">Category:</label><br/>
                <select id="catID" name="catID[]" class="form-select" multiple>
                  @foreach ($data['category'] as $parentCategory)
                    @if ($parentCategory->parentID == 0 || $parentCategory->parentID == $parentCategory->id) 
                    <optgroup label="{{ $parentCategory->title }}">
                      <option value="{{ $parentCategory->id }}" @if(old('catID') && in_array($parentCategory->id, old('catID'))) 'selected' @endif>{{ $parentCategory->title }}</option>
                    
                      @foreach ($data['category'] as $category)
                          @if ($category->parentID == $parentCategory->id)
                          <option value="{{ $category->id }}" @if(old('catID') && in_array($category->id, old('catID'))) 'selected' @endif>{{ $category->title }}</option>
                          @endif
                      @endforeach
                      </optgroup>
                    @endif
                  @endforeach
                </select>
                </div>-->

                                    <div class="col-md-12" style="float:left;">
                                        <label for="validationCustom01" class="col-form-label">Category</label>
                                        <select id="catID" name="catID" class="form-select" required>
                                            <option value="">Select Category</option>
                                            @foreach ($data['category'] as $parentCategory)
                                            @if ($parentCategory->parentID == 0 || $parentCategory->parentID ==
                                            $parentCategory->id)
                                            <option value="{{ $parentCategory->id }}">
                                                {{ $parentCategory->title }}
                                            </option>
                                            @endif
                                            @endforeach
                                        </select>
                                        @error('catID')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12" id="subcategories" style="float:left;">
                                        <label for="validationCustom01" class="col-form-label">Sub Category</label>
                                        <select id="subcatID" name="subcatID" class="form-select">
                                            <option value="">Select Sub Category</option>
                                        </select>

                                    </div>
                                    @if(!empty($data['eventcategory']))
                                    <div class="col-md-12 pt-2">
                                        <label for="validationCustom01" class="col-form-label">Event & Festival
                                            Category</label>
                                        <select id="eventCat" name="eventCat" class="form-select">
                                            <option value="">Select Event & Festival Category</option>
                                            @foreach ($data['eventcategory'] as $eventcat)
                                            <option value="{{ $eventcat->id }}">{{ $eventcat->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-12 pt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="is_home" value="1" id="is_home">
                      <label class="form-check-label" for="is_home">
                        Top Stories
                      </label>
                    </div>
                  </div>


                                    <div class="col-md-12">
                                        <label for="inputNumber" class="col-form-label">Tags</label>
                                        <br />

                                        <select class="tags-select form-control" name="tags[]"
                                            multiple="multiple"></select>
                                    </div>

                                    @if(Auth::user()->roleID==1 || Auth::user()->roleID==2)
                                    <div class="col-md-12">
                                        <label for="validationCustom33" class="col-form-label">Users</label>
                                        <select class="form-select" name="userID" id="validationCustom33">
                                            @foreach ($data['user'] as $user)
                                            <option value="{{ $user->id }}" @if($user->id==old('userID'))
                                                {{ 'selected' }} @endif>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <input type='hidden' name="userID" value="{{Auth::user()->id}}" />
                                    @endif
                                    <div class="col-md-12">
                                        <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                                        <input type="text" name="metakeyword" maxlength="190"
                                            value="{{ old('metakeyword') }}" class="form-control"
                                            id="validationCustom08" />
                                    </div>
                                    <div class="col-md-12">
                                        <label for="newstatus" class="col-form-label">Status</label>
                                        <select class="form-select" name="status" id="newstatus">
                                            <option selected value="Active">Publish</option>
                                            <option value="Inactive">Draft</option>
                                            <option value="Schedule">Schedule</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <div id="scheduledatetxt" style="display:none;">
                                            <label for="inputNumber" class="col-form-label">Schedule Date</label>
                                            <input class="form-control" type="datetime-local" name="scheduleDate"
                                                value="{{ old('scheduleDate') }}" id="scheduleDate">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="inshorts" class="col-form-label">Inshorts</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="inshorts" id="inshorts" value="1">
                                            <label class="form-check-label" for="inshorts">
                                                Enable Inshorts
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="notification" class="col-form-label"></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="notification" id="notification" value="1">
                                            <label class="form-check-label" for="notification">
                                            Send Notification
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 pt-3">
                                        <button class="btn btn-primary" type="submit" id="newsSubmit" value="1">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>





                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submitBtn = document.getElementById('newsSubmit');
    const form = document.getElementById('newsform');

    // Add event listener to the form submit event
    form.addEventListener('submit', function (event) {
        // Check if the form has the 'was-validated' class
        if (form.classList.contains('was-validated')) {
            // Disable the submit button if the form is valid
            if (form.checkValidity()) {
                submitBtn.disabled = true;
            } else {
                // If the form is invalid, do not disable the submit button
                event.preventDefault(); // Prevent form submission
            }
        } else {
            // If the form does not have 'was-validated' class, do not disable the submit button
            // Optionally, you can add a class to indicate the form was attempted to be validated
            form.classList.add('was-validated');
        }
    });
    });
</script>
@include('news.socialpost')

@include('news.mediaajax')

@include('news.mediaajaxvideo')

@endsection