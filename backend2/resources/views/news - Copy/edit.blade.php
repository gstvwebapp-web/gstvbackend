@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>News</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/news') }}">News</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
            @if(session('success'))
                <div class="col-lg-12">
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                </div>
            @endif
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body pt-4">


                        <form class="row g-3 needs-validation" method="POST" action="{{ url('/news/update') }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-9">
                                    <input type="hidden" name="nid" id="nid" value="{{ $data['news']->id }}">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="validationCustom01" class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" id="validationCustom01"
                                                value="{{ $data['news']->title }}" required>
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
                                                id="validationCustom11" value="{{ $data['news']->englishTitle }}"
                                                required>
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
                                        <textarea class="tinymce-editor form-control" id="descriptioneditor"
                                            name="description" required>{{ $data['news']->description }}</textarea>
                                        <div class="invalid-feedback">
                                            Please enter the news description.
                                        </div>
                                        @error('description')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!--<div class="row">
                  <div class="col-md-6">
                    <label for="inputNumber" class="col-form-label">Upload Video <span class="msgcls">( Only MP4, AVI, MOV, WEBM and WMV videos are allowed, The video size must not exceed 10 MB. )</span></label>
                    <input class="form-control" type="file" name="uploadVideo" accept="video/mp4, video/webm, video/avi, video/mov, video/wmv" id="formFile">
                    @error('uploadVideo')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                  </div>
                  
                  <div class="col-md-6">
                    <label for="inputNumber" class="col-form-label">Video URL</label>
                    <input class="form-control" type="text" placeholder="https://youtu.be/c8Jp3bYPcrc?si=kfppHObQAvaztaYR" name="videoURL" value="{{ $data['news']->videourl }}" id="formFile">
                  </div>
                </div>-->           <br>
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" {{ $data['news']->is_live_news == 1 ? 'checked' : '' }}  name="is_live_news" value="1" id="is_live_news">
                                        <label class="form-check-label" for="is_live_news">
                                            Live News
                                        </label>
                                        </div>
                                    </div>
                                     
                                    <div class="col-md-12" id="livenewsdiv" style="display: {{ $data['news']->is_live_news == 1 ? 'block' : 'none' }};" >
                                        <button type="button" id="add-more" class="btn btn-primary" style="float: right; margin-top: -10px;">Add More</button>
                                        <div id="dynamic-fields" style="padding: 10px 0px;">
                                        @if(!empty($data['livenews']))
                                            @foreach($data['livenews'] as $ln)
                                                <input type="hidden" name="live_lid[]" value="{{$ln->lid}}"/>
                                                <div class="field-group mb-3">
                                                    <label for="title">Title</label>
                                                    <input type="text" name="live_titles[]" class="form-control mb-2" value="{{$ln->livetitle}}" placeholder="Enter Title">

                                                    <label for="content">Content</label>
                                                    <textarea name="live_contents[]" class="tinymce-editor-live form-control" id="liveditor"  placeholder="Enter Content"> {{ $ln->livedesc }}</textarea>
                                                    <button type="button" class="btn btn-danger delete-field mt-2">Delete</button>
                                                </div>
                                            @endforeach
                                        @endif
                                        </div> 
                                     </div>    
                                </div>
                                <style>
                                .multi-select-menuitem--titled:before,
                                .multi-select-container {
                                    display: none !important;
                                }

                                .multi-select-menuitem {
                                    padding: 0.6em 1em 0.1em 30px !important;
                                }

                                .multi-select-menuitem--titledsr {
                                    padding: 0 0 0 20px !important;
                                    margin: 0 0 0 30px !important;
                                }

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
                                    @php
                                    $extension = pathinfo($data['news']->featureImage, PATHINFO_EXTENSION);
                                    $videoextension = pathinfo($data['news']->videoURL, PATHINFO_EXTENSION);
                                    @endphp
                                    <div class="col-md-6" style="float:left;padding:0 5px 0 0;">
                                        <label for="featureImage" class="col-form-label">Image
                                            <!--<span class="msgcls">( Only Webp, JPEG, PNG, JPG, WEBP and GIF images are allowed, The image size must not exceed 1 MB. )</span>-->
                                        </label>
                                        <div>
                                            @if($data['news']->featureImage!='')
                                            <span style="display:block" onclick="imgremove();" id="imgclose"
                                                class="imgclosecls">X</span>
                                            @endif
                                            <div data-bs-toggle="modal" id="selectedImageContainer"
                                                data-bs-target="#imagevideoModalCenter" class="imgvideocls">
                                                @if($data['news']->featureImage!='' && $extension=='pdf')
                                                <img src="{{asset('public/media').'/pdficon.jpg'}}"
                                                    style="width:100%;" />
                                                @elseif($data['news']->featureImage!='' && ($extension == 'jpg' ||
                                                $extension == 'jpeg' || $extension == 'png' || $extension == 'gif' ||
                                                $extension =='webp'))
                                                <img src="{{ str_replace('.'.$extension, '_small.'.$extension, $data['news']->featureImage) }}"
                                                    class="oneselectimggallery">
                                                @elseif($data['news']->featureImage!='' && (($extension != 'jpg' ||
                                                $extension != 'jpeg' || $extension != 'png' || $extension != 'gif' ||
                                                $extension !='webp') || $extension!='pdf'))
                                                <img src="{{asset('public/media').'/videoicon.jpg'}}"
                                                    style="width:100%;" />
                                                @else
                                                Image
                                                @endif
                                            </div>
                                        </div>
                                        <input class="form-control" type="hidden" id="featureImage" name="featureImage"
                                            value="{{ $data['news']->featureImage }}">

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
                                        <div>
                                            @if($data['news']->videoURL!='' && ($videoextension=='mp4' ||
                                            $videoextension=='webm'))
                                            <span style="display:block;" onclick="videoremove();" id="videoclose"
                                                class="videoclosecls">X</span>
                                            @endif
                                            <div data-bs-toggle="modal" id="selectedVideoContainers"
                                                data-bs-target="#videoModalCenters" class="imgvideocls">

                                                @if($data['news']->videoURL!='' && ($videoextension=='mp4' ||
                                                $videoextension=='webm'))
                                                <img src="{{preg_replace('/\.(mp4|webm)$/', '_video.gif', $data['news']->videoURL)}}"
                                                    style="width:100%;" />
                                                @else
                                                Video
                                                @endif
                                            </div>
                                        </div>
                                        <input class="form-control" type="hidden" name="videoURL"
                                            value="{{ $data['news']->videoURL }}" id="featureVideo">
                                    </div> 
                                    <div class="clearfix"></div>
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_vertical_video" value="1" @if($data['news']->is_vertical_video==9) {{ 'checked' }} @endif id="is_vertical_video">
                                        <label class="form-check-label" for="is_vertical_video">
                                            Vertical Video
                                        </label>
                                        </div>
                                    </div>
 
                                    <div class="col-md-12" style="float:left;">
                                        <label for="validationCustom01" class="col-form-label">Category</label>
                                        <select id="catID" name="catID" class="form-select" required>
                                            <option value="">Select Category</option>
                                            @foreach ($data['category'] as $parentCategory)
                                            @if ($parentCategory->parentID == 0 || $parentCategory->parentID ==
                                            $parentCategory->id)
                                            <option value="{{ $parentCategory->id }}" @if(in_array($parentCategory->id,
                                                explode(",",$data['news']->catID))) {{ 'selected'}} @endif>
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
                                            @foreach ($data['subcategory'] as $subcategory)
                                            <option value="{{ $subcategory->id }}" @if(in_array($subcategory->id,
                                                explode(",",$data['news']->catID))) {{ 'selected'}}
                                                @endif>{{ $subcategory->title }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                    @if(!empty($data['eventcategory']))
                                    <div class="col-md-12 pt-2">
                                        <label for="validationCustom01" class="col-form-label">Event & Festival
                                            Category</label>
                                        <select id="eventCat" name="eventCat" class="form-select">
                                            <option value="">Select Event & Festival Category</option>
                                            @foreach ($data['eventcategory'] as $eventcat)
                                            <option value="{{ $eventcat->id }}" @if(in_array($eventcat->id,
                                                explode(",",$data['news']->catID))) {{ 'selected'}}
                                                @endif>{{ $eventcat->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <div class="col-md-12 pt-2">
                                        <label class="form-check-label" for="relatednewsid">
                                            Related News
                                        </label>
                                        <input type="hidden" id="relatednewsidexist" value="{{ $data['news']->relatednewsid }}"/>
                                        <select id="relatednewsid" name="relatednewsid[]" class="form-select select2" multiple>
                                            <option value="">Select Related News</option>
                                            @foreach ($data['relatedNews'] as $news)
                                                <option value="{{ $news->id }}" 
                                                        @if(in_array($news->id, explode(',', $data['news']->relatednewsid))) selected @endif>
                                                    {{ $news->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                   <!-- <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_home" value="1" @if($data['news']->is_home==1) {{ 'checked' }} @endif id="is_home" />
                                        <label class="form-check-label" for="is_home">
                                            Top Stories
                                        </label>
                                        </div>
                                    </div>-->
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_breaking" value="1" @if($data['news']->is_breaking==1) {{ 'checked' }} @endif id="is_breaking" />
                                        <label class="form-check-label" for="is_breaking">
                                            Breaking News
                                        </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="inputNumber" class="col-form-label">Tags</label>
                                        <br />
                                        @php
                                        $tags = explode(',',$data['news']->tags);
                                        @endphp
                                        <select class="tags-select form-control" name="tags[]" multiple="multiple">
                                            @foreach($tags as $tag)
                                            <option value="{{$tag}}" selected>{{$tag}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if(Auth::user()->roleID==1 || Auth::user()->roleID==2)
                                    <div class="col-md-12">
                                        <label for="validationCustom33" class="col-form-label">Users</label>
                                        <select class="form-select" name="userID" id="validationCustom33">
                                            @foreach ($data['user'] as $user)
                                            <option value="{{ $user->id }}" @if($user->id==$data['news']->userID)
                                                {{ 'selected' }} @endif>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @else
                                    <input type='hidden' name="userID" value="{{ $data['news']->userID }}" />
                                    @endif

                                    <div class="col-md-12">
                                        <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                                        <input type="text" name="metakeyword" maxlength="190"
                                            value="{{ $data['news']->metakeyword }}" class="form-control"
                                            id="validationCustom08" />
                                    </div>

                                    <div class="col-md-12">
                                        <label for="newstatus" class="col-form-label">Status</label>
                                        <select class="form-select" name="status" id="newstatus">
                                            <option @if($data['news']->status=='Active') {{ 'selected' }} @endif
                                                value="Active">Publish</option>
                                            <option @if($data['news']->status=='Inactive') {{ 'selected' }} @endif
                                                value="Inactive">Draft</option>
                                            <option @if($data['news']->status=='Schedule') {{ 'selected' }} @endif
                                                value="Schedule">Schedule</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        @if($data['news']->status=='Schedule')
                                        <div id="scheduledatetxt">
                                            @else
                                            <div id="scheduledatetxt" style="display:none;">
                                                @endif
                                                <label for="inputNumber" class="col-form-label">Schedule Date</label>
                                                <input class="form-control" type="datetime-local" name="scheduleDate"
                                                    value="{{ $data['news']->scheduleDate }}" id="scheduleDate">
                                            </div>
                                        </div>
                                     <div class="col-md-12">
                                        <label for="inshorts" class="col-form-label">Inshorts</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="inshorts" id="inshorts" value="1"
                                                  @if($data['news']->inshorts == 1) {{ 'checked' }} @endif>
                                            <label class="form-check-label" for="inshorts">
                                                Enable Inshorts
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="notification" class="col-form-label"></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="notification" id="notification" value="1"
                                                >
                                                <!-- @if($data['news']->notification == 1) {{ 'checked' }} @endif -->
                                            <label class="form-check-label" for="notification">
                                                Send Notification
                                            </label>
                                        </div>
                                    </div>


                                        <div class="col-12 pt-3">
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </form>



                    </div>



                </div>
            </div>
    </section>
</main><!-- End #main -->

@include('news.socialpost')

@include('news.mediaajax')

@include('news.mediaajaxvideo')

@endsection