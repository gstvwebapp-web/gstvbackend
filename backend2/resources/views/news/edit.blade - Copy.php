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
                                            <label for="validationCustom01" class="form-label">Title</label><span style="float:right"><input type="checkbox" {{ $data['news']->hide_title == 1 ? 'checked' : '' }} value="1" class="form-check-input" name="hide_title" /> For video Hide title</span>
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
                                                    <textarea name="live_contents[]" class="tinymce-editor-live form-control" id="liveditor-{{ md5($ln->lid) }}"  placeholder="Enter Content"> {{ $ln->livedesc }}</textarea>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input create-story-checkbox" type="checkbox" name="live_new_story[]" value="1" @if($ln->live_new_story==1) {{ 'checked' }} @endif id="createStoryCheckbox-{{ md5($ln->lid) }}">
                                                        <label class="form-check-label" for="createStoryCheckbox-{{ md5($ln->lid) }}">Create a new story</label>
                                                    </div>
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
                                                <img src="{{ str_replace('.'.$extension, '_small.'.$extension, $data['news']->featureImage) }}?v={{ time() }}"
                                                    class="oneselectimggallery">
                                                @elseif($data['news']->featureImage!='' && (($extension != 'jpg' ||
                                                $extension != 'jpeg' || $extension != 'png' || $extension != 'gif' ||
                                                $extension !='webp') || $extension!='pdf'))
                                                <img src="{{asset('public/media').'/videoicon.jpg'}}?v={{ time() }}"
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
                                                <img src="{{preg_replace('/\.(mp4|webm)$/', '_video_small.jpg', $data['news']->videoURL)}}?v={{ time() }}"
                                                    style="width:100%;" />
                                                @else
                                                Video
                                                @endif
                                            </div>
                                        </div>
                                        <input class="form-control" type="hidden" name="videoURL"
                                            value="{{ $data['news']->videoURL }}" id="featureVideo">
                                            <input class="form-control" type="hidden" name="oldvideoURL"
                                            value="{{ $data['news']->videoURL }}">
                                       <input class="form-control" type="hidden" name="video_start" value="{{ $data['news']->video_start }}"
                                            id="featureVideoid">
                                    </div>
                                  	<div class="col-md-12" id="subcategories" style="float:left;">
                                        <label for="validationCustom041" class="col-form-label">Image Credit Text</label>
                                        <input type="text" name="img_credit_txt" class="form-control" placeholder="Image Credit Text" id="validationCustom041"  value="{{ $data['news']->img_credit_txt }}" required>
                                    </div>
                                   <!--<div class="col-md-12" id="subcategories" style="float:left;">
                                        <label for="validationCustom041" class="col-form-label">Video Start From</label>
                                        <input type="text" name="video_" class="form-control" placeholder="Video start" id="validationCustom041"  value="{{ $data['news']->video_start }}">
                                    </div>-->
                                    <div class="clearfix"></div>
                                    <!-- <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_vertical_video" value="1" @if($data['news']->is_vertical_video==9) {{ 'checked' }} @endif id="is_vertical_video">
                                        <label class="form-check-label" for="is_vertical_video">
                                            Vertical Video
                                        </label>
                                        </div>
                                    </div> -->
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="is_jacket"
                                                value="1"
                                                id="is_jacket"
                                                {{ $data['news']->is_jacket == 1 ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="is_jacket">
                                                Add Jacket
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="col-md-12"
                                        id="jacketTitleDiv"
                                        style="display: {{ $data['news']->is_jacket == 1 ? 'block' : 'none' }};"
                                    >
                                        <label for="jacket_title" class="col-form-label">Jacket Title</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="jacket_title"
                                            id="jacket_title"
                                            maxlength="55"
                                            placeholder="Enter Jacket Title"
                                            value="{{ $data['news']->jacket_title }}"
                                        >

                                        <div class="invalid-feedback">
                                            Please enter Jacket Title.
                                        </div>
                                    </div>
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_video_horizontal" value="1" @if($data['news']->is_video_horizontal==1) {{ 'checked' }} @endif id="is_video_horizontal">
                                        <label class="form-check-label" for="is_video_horizontal">
                                            Horizontal Video
                                        </label>
                                        </div>
                                    </div>
                                  <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_valid" value="1" @if($data['news']->is_valid==1) {{ 'checked' }} @endif id="is_valid">
                                        <label class="form-check-label" for="is_valid">
                                            No Description
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
                                           <option value="{{ $parentCategory->id }}"
                                                @if(in_array($parentCategory->id, $data['categoryIds'])) selected @endif>
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
                                            <option value="{{ $subcategory->id }}"
                                                @if(in_array($subcategory->id, $data['categoryIds'])) selected @endif>
                                                {{ $subcategory->title }}
                                            </option>
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
                                            <option value="{{ $eventcat->id }}"
                                                @if(in_array($eventcat->id, $data['categoryIds'])) selected @endif>
                                                {{ $eventcat->title }}
                                            </option>
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
                                  <input class="form-check-input" type="hidden" name="is_home" value="{{ $data['news']->is_home == 1 ? 1 : 0 }}" id="is_home" />
                                   <!-- <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        
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

                                    @php
                                        $tags = explode(',',$data['news']->tags);
                                        @endphp

                                    <div class="col-md-12">
                                        <label for="inputNumber" class="col-form-label">Tags</label>
                                        <br /> 
                                            <div class="form-group position-relative">
                                                <input type="text" id="googleSearch" class="form-control" placeholder="Search in Google..." autocomplete="off">
                                                <div id="suggestions" class="list-group position-absolute w-100"></div>
                                            </div>

                                            <div id="selectedTags" class="mt-2"></div>
                                            <div id="selectedTagsInputs"></div>
                                    </div>
                                    <div class="col-md-12" style="float:left;">
                                        <label for="validationCustom01" class="col-form-label">News Source</label>
                                        <select id="newssource" name="newssource" class="form-select" required>
                                            <option value="">Select Source</option>
                                              @foreach ($data['newssourceList'] as $newsr)
                                            <option value="{{ $newsr->id }}" @if($newsr->id==$data['news']->newssource)
                                                {{ 'selected' }} @endif>{{ $newsr->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('newssource')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
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

                                    <!-- <div class="col-md-12">
                                        <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                                        <input type="text" name="metakeyword" maxlength="190"
                                            value="{{ $data['news']->metakeyword }}" class="form-control"
                                            id="validationCustom08" />
                                    </div> -->

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
<script>
let selectedTags = new Set(@json($tags));  // Store selected tags
let selectedIndex = -1; // Track highlighted suggestion


function fetchGoogleSuggestions(query) {
    let script = document.createElement('script');
    script.src = `https://suggestqueries.google.com/complete/search?client=firefox&q=${query}&callback=handleSuggestions`;
    document.body.appendChild(script);
    document.body.removeChild(script); // Cleanup
}

function handleSuggestions(response) {
    let suggestionsBox = document.getElementById("suggestions");
    suggestionsBox.innerHTML = "";
    selectedIndex = -1;
    let suggestions = response[1];

    suggestions.forEach((suggestion, index) => {
        let item = document.createElement("div");
        item.classList.add("list-group-item", "list-group-item-action");
        item.textContent = suggestion;
        item.dataset.index = index;

        item.onclick = function () {
            addTag(suggestion);
            suggestionsBox.innerHTML = "";
            document.getElementById("googleSearch").value = "";
        };

        suggestionsBox.appendChild(item);
    });
}

function addTag(tagText) {
    if (!selectedTags.has(tagText)) {
        selectedTags.add(tagText);
        renderTags();
    }
}

function removeTag(tagText) {
    selectedTags.delete(tagText);
    renderTags();
}

function renderTags() {
    const tagsContainer = document.getElementById("selectedTags");
    const inputsContainer = document.getElementById("selectedTagsInputs");
    tagsContainer.innerHTML = "";
    inputsContainer.innerHTML = "";

    selectedTags.forEach(tag => {
        // Create badge
        let tagElement = document.createElement("span");
        tagElement.classList.add("badge", "bg-primary", "me-1", "p-2", "mb-1");
        tagElement.innerHTML = `${tag} <span onclick="removeTag('${tag}')" style="cursor:pointer;">&times;</span>`;
        tagsContainer.appendChild(tagElement);

        // Create hidden input
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = "selectedTags[]";
        input.value = tag;
        inputsContainer.appendChild(input);
    });
}

const searchInput = document.getElementById("googleSearch");

searchInput.addEventListener("input", function () {
    const query = this.value.trim();
    if (query.length > 1) {
        fetchGoogleSuggestions(query);
    } else {
        document.getElementById("suggestions").innerHTML = "";
        selectedIndex = -1;
    }
});

searchInput.addEventListener("keydown", function (e) {
    const suggestionsBox = document.getElementById("suggestions");
    const items = suggestionsBox.querySelectorAll(".list-group-item");

    if (e.key === "ArrowDown") {
        e.preventDefault();
        if (items.length > 0) {
            selectedIndex = (selectedIndex + 1) % items.length;
            highlightItem(items);
        }
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (items.length > 0) {
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            highlightItem(items);
        }
    } else if (e.key === "Enter") {
        e.preventDefault();
        if (selectedIndex >= 0 && items[selectedIndex]) {
            const selectedText = items[selectedIndex].textContent;
            addTag(selectedText);
            searchInput.value = "";
            suggestionsBox.innerHTML = "";
            selectedIndex = -1;
        } else {
            const query = searchInput.value.trim();
            if (query !== "") {
                addTag(query);
                searchInput.value = "";
                suggestionsBox.innerHTML = "";
                selectedIndex = -1;
            }
        }
    }
});

function highlightItem(items) {
    items.forEach((item, index) => {
        item.classList.toggle("active", index === selectedIndex);
    });
}

document.addEventListener("click", function (e) {
    if (!document.getElementById("googleSearch").contains(e.target) &&
        !document.getElementById("suggestions").contains(e.target)) {
        document.getElementById("suggestions").innerHTML = "";
        selectedIndex = -1;
    }
});
    document.addEventListener("DOMContentLoaded", function () {
        renderTags();
    });


document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("is_valid");
    const description = document.getElementById("descriptioneditor");

    function toggleRequired() {
        if (checkbox.checked) {
            description.removeAttribute("required");
        } else {
            description.setAttribute("required", "required");
        }
    }

    // Run on page load
    toggleRequired();

    // Run on checkbox change
    checkbox.addEventListener("change", toggleRequired);

    //jacket
    const jacketCheckbox = document.getElementById("is_jacket");
    const jacketTitleDiv = document.getElementById("jacketTitleDiv");
    const jacketTitle = document.getElementById("jacket_title");

    function toggleJacket() {
        if (jacketCheckbox.checked) {
            jacketTitleDiv.style.display = "block";
            jacketTitle.required = true;
        } else {
            jacketTitleDiv.style.display = "none";
            jacketTitle.required = false;
            jacketTitle.value = "";
        }
    }

    toggleJacket();

    jacketCheckbox.addEventListener("change", toggleJacket);
});
</script>
@include('news.socialpost')

@include('news.mediaajax')

@include('news.mediaajaxvideo')

@endsection