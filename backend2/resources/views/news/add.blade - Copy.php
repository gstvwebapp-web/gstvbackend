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
                                            <label for="validationCustom01" class="form-label">Title</label> <span style="float:right"><input type="checkbox" value="1" class="form-check-input" name="hide_title" /> For video Hide title</span>
                                             <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ isset($data['news']) ? $data['news']->title : old('title') }}" required>
                                          	<input type="hidden" value="{{ isset($data['news']) ? $data['news']->id : '' }}" name="usernews_userID" />
                                            <div class="invalid-feedback">
                                                Please enter the news title.
                                            </div>
                                            @error('title')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label for="validationCustom11" class="col-form-label">
                                                English Title
                                                <span id="translateSpinner" class="ms-2 text-muted small" style="display:none;">
                                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                                    Translating...
                                                </span>
                                            </label>
                                            <input type="text" name="englishTitle" class="form-control"
                                                id="validationCustom11" value="{{ old('englishTitle') }}" required>
                                            <div class="invalid-feedback">
                                                Please enter the english title.
                                            </div>
                                            @if ($errors->has('englishTitle'))
                                                <span class="text-danger">{{ $errors->first('englishTitle') }}</span>
                                            @endif
                                            <!-- @error('englishTitle')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror -->
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
                                    <br>
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_live_news" value="1" id="is_live_news">
                                        <label class="form-check-label" for="is_live_news">
                                            Live News
                                        </label>
                                        </div>
                                    </div>
                                     
                                    <div class="col-md-12" id="livenewsdiv" style="display:none">
                                        <button type="button" id="add-more" class="btn btn-primary" style="float: right; margin-top: -10px;">Add More</button>
                                        <div id="dynamic-fields" style="padding: 10px 0px;">
                                            <div class="field-group mb-3">
                                                <label for="title">Title</label>
                                                <input type="text" name="live_titles[]" class="form-control mb-2" placeholder="Enter Title">

                                                <label for="content">Content</label>
                                                <textarea name="live_contents[]" class="tinymce-editor-live form-control" id="liveditor"  placeholder="Enter Content"></textarea>

                                                <div class="form-check mt-2">
                                                    <input class="form-check-input create-story-checkbox" type="checkbox" name="live_new_story[]" value="1" id="createStoryCheckbox">
                                                    <label class="form-check-label" for="createStoryCheckbox">Create a new story</label>
                                                </div>

                                                <button type="button" class="btn btn-danger delete-field mt-2">Delete</button>
                                            </div>
                                        </div>
                                    </div>    
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
                                      <input class="form-control" type="hidden" name="video_start" value=""
                                            id="featureVideoid">
                                    </div>
									 <div class="col-md-12" id="subcategories" style="float:left;">
                                        <label for="validationCustom041" class="col-form-label">Image Credit Text</label>
                                        <input type="text" name="img_credit_txt" class="form-control" placeholder="Image Credit Text" id="validationCustom041" value="gstv" required>
                                    </div>
                                    <!--<div class="col-md-12" id="subcategories" style="float:left;">
                                        <label for="validationCustom041" class="col-form-label">Video Start From</label>
                                        <input type="text" name="video_" class="form-control" placeholder="Video start" id="validationCustom041" value="10">
                                    </div>-->
                                    <div class="clearfix"></div>
                                    <!-- <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked name="is_vertical_video" value="1" id="is_vertical_video">
                                        <label class="form-check-label" for="is_vertical_video">
                                            Vertical Video
                                        </label>
                                        </div>
                                    </div> -->
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_jacket" value="1" id="is_jacket">
                                            <label class="form-check-label" for="is_jacket">
                                                Add Jacket
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12" id="jacketTitleDiv" style="display:none;">
                                        <label for="jacket_title" class="col-form-label">Jacket Title</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="jacket_title"
                                            id="jacket_title"
                                            maxlength="55"
                                            placeholder="Enter Jacket Title">
                                    </div>
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked  name="is_video_horizontal" value="1" id="is_video_horizontal">
                                        <label class="form-check-label" for="is_video_horizontal">
                                           Horizontal Video
                                        </label>
                                        </div>
                                    </div>
									<div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_valid" value="1" id="is_valid">
                                        <label class="form-check-label" for="is_valid">
                                            No Description
                                        </label>
                                        </div>
                                    </div>
                                    @php
                                        $userCatIds = explode(',', Auth::user()->catID);
                                        $defaultCategory = $userCatIds[0] ?? '';
                                        $defaultSubCategory = isset($userCatIds[1]) ? trim($userCatIds[1]) : '';
                                    @endphp
                                    <div class="col-md-12" style="float:left;">
                                        <label for="validationCustom01" class="col-form-label">Category</label>
                                        <select id="catID" name="catID" class="form-select" required data-default-subcat="{{ Auth::user()->roleID == 7 ? $defaultSubCategory : '' }}">
                                            <option value="">Select Category</option>
                                            @foreach ($data['category'] as $parentCategory)
                                            @if ($parentCategory->parentID == 0 || $parentCategory->parentID ==
                                            $parentCategory->id)
                                            <option value="{{ $parentCategory->id }}"
                                             {{ Auth::user()->roleID == 7 && $parentCategory->id == $defaultCategory ? 'selected' : '' }}>
                                                  
                    
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
                                         <label class="form-check-label" for="relatednewsid">
                                            Releated News
                                        </label>
                                        <input type="hidden" id="relatednewsidexist" value=""/>
                                        <select id="relatednewsid" name="relatednewsid[]" class="form-select select2" multiple>
                                            <option value="">Select Related News</option>
                                        </select>
                                    </div>

                                    <!--<div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_home" value="1" id="is_home">
                                        <label class="form-check-label" for="is_home">
                                            Top Stories
                                        </label>
                                        </div>
                                    </div>-->
                                    <div class="col-md-12 pt-2">
                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_breaking" value="1" id="is_breaking">
                                        <label class="form-check-label" for="is_breaking">
                                            Breaking News
                                        </label>
                                        </div>
                                    </div>

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
                                            <option value="{{ $newsr->id }}"
                                                {{
                                                    (Auth::user()->roleID == 3 && $newsr->title == 'Direct') ||
                                                    (Auth::user()->roleID == 4 && $newsr->title == 'TV')
                                                    ? 'selected' : ''
                                                }}>
                                                {{ $newsr->title }}
                                            </option>
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
                                            <option value="{{ $user->id }}" @if($user->id==old('userID'))
                                                {{ 'selected' }} @endif>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <input type='hidden' name="userID" value="{{Auth::user()->id}}" />
                                    @endif
                                    <!-- <div class="col-md-12">
                                        <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                                        <input type="text" name="metakeyword" maxlength="190"
                                            value="{{ old('metakeyword') }}" class="form-control"
                                            id="validationCustom08" />
                                    </div> -->
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
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                } else {
                    event.preventDefault(); // Prevent form submission
                }
            } else {
                form.classList.add('was-validated');
            }
        });
    // Jackert
        const jacketCheckbox = document.getElementById("is_jacket");
        const jacketTitleDiv = document.getElementById("jacketTitleDiv");
        const jacketTitle = document.getElementById("jacket_title");

        function toggleJacket() {
            if (jacketCheckbox.checked) {
                jacketTitleDiv.style.display = "block";
                jacketTitle.setAttribute("required", "required");
            } else {
                jacketTitleDiv.style.display = "none";
                jacketTitle.removeAttribute("required");
                jacketTitle.value = "";
            }
        }

        toggleJacket(); // Initial state

        jacketCheckbox.addEventListener("change", toggleJacket);
    });

// tag
    
let selectedTags = new Set(); // Store selected tags
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
    const title = localStorage.getItem('storyTitle');
    const content = localStorage.getItem('storyContent');
    const storyContentcatID = localStorage.getItem('storyContentcatID');
    const storyContentsubcatID = localStorage.getItem('storyContentsubcatID');
    const storyfeatureImage = localStorage.getItem('storyfeatureImage');
    const storyfeatureVideo = localStorage.getItem('storyfeatureVideo');
    // alert(storyContentcatID+storyContentsubcatID);
    if (title) {
        $('input[name="title"]').val(title);
    }
    if (content) {
        $('textarea[name="description"]').val(content);
    }
    setTimeout(function () {
    // const storyContentcatID = localStorage.getItem('storyContentcatID');
    // const storyContentsubcatID = localStorage.getItem('storyContentsubcatID');

    if (storyContentcatID) {
        $('#catID').val(storyContentcatID).trigger('change');
    }

    if (storyContentsubcatID) {
        $('#subcatID').val(storyContentsubcatID).trigger('change');
    }
    if(storyfeatureImage){
         $('#selectedImageContainer').html('<img src="'+storyfeatureImage+'" class="oneselectimggallery">');
         $('#featureImage').val(storyfeatureImage);
    }
    if(storyfeatureVideo){
         const gifUrl = storyfeatureVideo.replace(/\.mp4$/, '_video.gif');
         $('#selectedVideoContainers').html('<img src="'+gifUrl+'" class="oneselectimggallery">');
         $('#featureVideo').val(storyfeatureVideo);
    }
}, 1000);
 setTimeout(function () {
    // Optional: Clear it after use
    localStorage.removeItem('storyTitle');
    localStorage.removeItem('storyContent');
    localStorage.removeItem('storyContentcatID');
    localStorage.removeItem('storyContentsubcatID');
    localStorage.removeItem('storyfeatureImage');
    localStorage.removeItem('storyfeatureVideo');
    }, 5000);
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
});
</script>
@include('news.socialpost')

@include('news.mediaajax')

@include('news.mediaajaxvideo')

<script>
// ── Auto-translate Gujarati title → English Title + Auto Tags ─────────────
(function () {
    var titleInput   = document.getElementById('validationCustom01');
    var engInput     = document.getElementById('validationCustom11');
    var spinner      = document.getElementById('translateSpinner');
    var translateUrl = '{{ url("/news/translateTitle") }}';
    var debounceTimer;

    if (!titleInput || !engInput) return;

    titleInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var val = this.value.trim();
        if (!val) return;

        debounceTimer = setTimeout(function () {
            if (spinner) spinner.style.display = 'inline';

            fetch(translateUrl + '?text=' + encodeURIComponent(val))
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.translated) {
                        engInput.value = res.translated;

                        // ── Auto-add tags from English title ──────────────
                        autoAddTagsFromTitle(res.translated);

                        // ── Auto-fill related news from English title ─────
                        autoFillRelatedNews(res.translated);
                    }
                })
                .catch(function (err) {
                    console.warn('Auto-translate failed:', err);
                })
                .finally(function () {
                    if (spinner) spinner.style.display = 'none';
                });
        }, 700);
    });

    /**
     * Extract nouns and proper nouns from the English title as tags.
     * Priority:
     * 1. ALL CAPS words = acronyms (BJP, MSME, UBT, NDA)
     * 2. Capitalized words = proper nouns (Bengal, Modi, India, Budget)
     * 3. Longer common nouns (investment, employment, startup)
     * Always adds 'gstvnews'. Max 5 tags total.
     */
    function autoAddTagsFromTitle(englishTitle) {
        if (typeof addTag !== 'function') return;

        var stopWords = new Set([
            'a','an','the','and','or','but','in','on','at','to','for',
            'of','with','by','from','is','are','was','were','be','been',
            'has','have','had','as','it','its','this','that','these',
            'those','after','before','during','over','under','about',
            'not','no','so','if','when','while','because','since','than',
            'into','out','up','down','off','between','through','also',
            'will','would','could','should','may','might','must','shall',
            'he','she','they','we','you','his','her','their','our','your',
            'across','against','along','among','around','without','within',
            'today','first','last','new','good','big','old','high','low',
            'says','said','gets','hit','set','one','two','three','four','five',
            'six','seven','eight','nine','ten','then','just','now','here','there'
        ]);

        // Split on spaces and common punctuation (preserve hyphens in words like mid-term)
        var words = englishTitle.split(/[\s,\.\/\!\?\;\:\"\'\(\)\[\]…]+/);

        var properNouns = [];  // ALL CAPS acronyms + Capitalized proper nouns
        var commonNouns = [];  // meaningful lowercase nouns (length > 4)

        words.forEach(function (w) {
            var clean = w.replace(/[^a-zA-Z0-9\-]/g, '');
            if (!clean || clean.length < 2) return;
            var lower = clean.toLowerCase();
            if (stopWords.has(lower)) return;

            if (clean === clean.toUpperCase() && /[A-Z]/.test(clean) && clean.length >= 2) {
                // ALL CAPS = acronym (BJP, MSME, NDA)
                properNouns.push(clean);
            } else if (/^[A-Z]/.test(clean) && clean.length >= 3) {
                // Starts with capital = proper noun (Bengal, Modi, India)
                properNouns.push(clean);
            } else if (clean.length > 4) {
                // Common noun — only longer words (investment, budget, startup)
                commonNouns.push(lower);
            }
        });

        // Proper nouns first, then common nouns — max 5 unique tags
        var seen = new Set();
        var tags = [];
        // properNouns.concat(commonNouns).forEach(function (t) {
        //     var key = t.toLowerCase();
        //     if (!seen.has(key) && tags.length < 5) {
        //         seen.add(key);
        //         tags.push(t);
        //     }
        // });

        
        // Default GSTV tags
        var defaultTags = [
            "GSTV News",
            "Gujarati News"
        ];

        // Add default tags without duplicates
        defaultTags.forEach(function(tag) {
            var key = tag.toLowerCase();
            if (!seen.has(key)) {
                seen.add(key);
                tags.push(tag);
            }
        });

        // Add all tags
        tags.forEach(function(tag) {
            addTag(tag);
        });
    }

    /**
     * Auto-fill Related News by searching the GUJARATI title directly.
     * Splits the Gujarati title into words, takes first 2 meaningful words,
     * searches each in the news title (which is stored in Gujarati),
     * adds first 2-4 unique results to #relatednewsid select2.
     */
    function autoFillRelatedNews(englishTitle) {
        // Use the GUJARATI title from the title input — not the English translation
        var gujaratiTitle = (titleInput && titleInput.value) ? titleInput.value.trim() : '';
        if (!gujaratiTitle) return;

        var relatedSelect = document.getElementById('relatednewsid');
        if (!relatedSelect) return;

        // Split Gujarati title into words, skip very short words (< 3 chars)
        var words = gujaratiTitle.split(/\s+/).filter(function (w) {
            return w.length >= 3;
        });

        // Take first 2 meaningful Gujarati words as search terms
        var searchTerms = words.slice(0, 2);
        if (searchTerms.length === 0) return;

        var domainName = window.location.hostname;
        var searchUrl  = '//' + domainName + '/backend2/news/searchRelatedNews';
        var addedIds   = new Set();
        var maxResults = 4;
        var allResults = [];

        function searchNext(index) {
            if (index >= searchTerms.length) {
                addRelatedNewsResults(allResults.slice(0, maxResults));
                return;
            }

            fetch(searchUrl + '?q=' + encodeURIComponent(searchTerms[index]))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    // searchRelatedNews returns array of News objects: {id, title, ...}
                    var items = Array.isArray(data) ? data : (data.results || []);
                    items.forEach(function (item) {
                        // Normalize: select2 needs {id, text}
                        var id    = item.id;
                        var text  = item.title || item.text || '';
                        if (id && text && !addedIds.has(id) && allResults.length < maxResults * 2) {
                            addedIds.add(id);
                            allResults.push({ id: id, text: text });
                        }
                    });
                    searchNext(index + 1);
                })
                .catch(function () {
                    searchNext(index + 1);
                });
        }

        searchNext(0);
    }

    function addRelatedNewsResults(items) {
        if (!items || items.length === 0) return;
        if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;

        var $select = $('#relatednewsid');
        if (!$select.length) return;

        items.forEach(function (item) {
            // Skip if this option already exists in the select
            if ($select.find('option[value="' + item.id + '"]').length > 0) return;

            // Create a new Option with label text — this is the key for select2 to show the title
            var newOption = new Option(item.text, item.id, true, true);
            $select.append(newOption);
        });

        // Trigger select2 to re-render — this makes the title labels appear
        $select.trigger('change');
    }
})();
</script>

@endsection