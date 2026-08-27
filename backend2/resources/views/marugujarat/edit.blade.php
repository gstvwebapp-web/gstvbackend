@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>I M Gujarat Samachar</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/marugujarat') }}">I M Journalist</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/marugujarat/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="gujaratid" value="{{ $data['marugujarat']->id }}" />
                 <div class="col-md-12">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" id="name" value="{{  $data['marugujarat']->name }}" required />
                    <div class="invalid-feedback">
                        Please enter the name.
                    </div>
                    @error('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>                

                <!-- City Input -->
                <div class="col-md-12">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" class="form-control" id="city" value="{{  $data['marugujarat']->city }}" />
                    <div class="invalid-feedback">
                        Please enter the City.
                    </div>
                    @error('city')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{  $data['marugujarat']->title }}" required />
                  <div class="invalid-feedback">
                    Please enter the pod cast Title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Description</label>
                  <!-- <input type="text" name="podcastTitle" class="form-control" id="validationCustom01" value="{{ old('podcastTitle') }}"  /> -->
                  <textarea name="description" class="form-control newfeatures" id="validationCustom01">{{ $data['marugujarat']->description }}</textarea>
                  <div class="invalid-feedback">
                    Please enter the podcast Description.
                  </div>
                  @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
               
               
                
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Upload Image (Upload Max 5 Images)</label>
                    <!-- <input class="form-control" type="file" name="uploadimages" accept="image/*" id="formFile"> -->
                    <input class="form-control" id="imageUpload" type="file" name="uploadimages[]" accept="image/*" id="formFile" multiple>
                    
                    <div class="row" style="margin-top: 20px;">
                      @if(!empty($data['marugujarat']->featureImage))
                        @foreach(json_decode($data['marugujarat']->featureImage, true) as $idx => $img)
                             
                            <div class="col-6 col-md-2 thumbOuter" id="img-old-{{ $idx }}">
                                <img src="{{ config('app.backendnewsurl').'/public/uploads/gujarat/'.$img }}" 
                                    class="img-thumbnail rotateable" 
                                    style="width:100%;height:80px;object-fit:cover;"
                                    data-index="{{ $idx }}"
                                    data-rotation="0">
                                <div class="mt-1 d-flex justify-content-between">
                                    <button type="button" class="btn btn-sm btn-warning" onclick="rotateImage(this, {{ $idx }})">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btnRemove" onclick="removeOldImage('{{ $img }}', {{ $idx }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="old_images[]" value="{{ $img }}">
                                <input type="hidden" name="rotations[{{ $img }}]" id="rotation-{{ $idx }}" value="0">
                            </div>
                        @endforeach
                      @endif
                    </div>
                    @error('uploadimages')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>  
                  @php
                      $imgurl = config('app.backendnewsurl').'/public/uploads/gujarat/'.$data['marugujarat']->video;
                  @endphp
                 <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Upload Video  (Upload only 1 Video)</label>
                    <input class="form-control" type="file" name="uploadvideo" accept="video/*" id="formFile">
                    <br>
                    @if($data['marugujarat']->video)
                      <div id="existingVideo">
                          <video width="250" controls>
                              <source src="{{ $imgurl }}" type="video/mp4">
                          </video>
                          <button class="btn btn-danger btnRemove" type="button" onclick="removeVideo()">
                            <i class="bi bi-trash"></i>
                          </button>
                      </div>
                    @endif

                    @error('audioclip')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div> 

                <div class="col-md-12">
                   <label for="inputNumber" class="col-form-label">Status</label>
                  <select class="form-control" name="status" onchange="toggleReasonBox()">
                      <option value="Active" {{ $data['marugujarat']->status == 'Active' ? 'selected' : '' }}>Active</option>
                      <option value="Inactive" {{ $data['marugujarat']->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                      <option value="Drop" {{ $data['marugujarat']->status == 'Drop' ? 'selected' : '' }}>Reject</option>
                  </select>
                </div>

                <div class="col-md-12" id="dropReasonDiv" style="{{ $data['marugujarat']->status == 'Drop' ? 'display:block;' : 'display:none;' }}">
                    <label for="drop_reason" class="form-label">Reason for Rejection</label>
                    <textarea name="drop_reason" id="drop_reason" class="form-control" rows="3">{{ $data['marugujarat']->drop_reason}}</textarea>
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
  <script>
     function toggleReasonBox() {
        let status = document.querySelector('select[name="status"]').value;
        let reasonDiv = document.getElementById('dropReasonDiv');

        if (status === "Drop") {
            reasonDiv.style.display = "block";
        } else {
            reasonDiv.style.display = "none";
        }
    } 
    function rotateImage(btn, idx) {
        const img = btn.closest('.thumbOuter').querySelector('img');
        const rotationInput = document.getElementById('rotation-' + idx);

        let currentRotation = parseInt(img.getAttribute('data-rotation')) || 0;
        currentRotation = (currentRotation + 90) % 360;

        img.style.transform = `rotate(${currentRotation}deg)`;
        img.setAttribute('data-rotation', currentRotation);
        rotationInput.value = currentRotation; // update hidden input
    }
    function removeVideo() {
      document.getElementById("existingVideo").style.display = "none";
      document.getElementById("videoUploadDiv").style.display = "block";
      // Optional: Add a hidden input to mark delete request
      let input = document.createElement("input");
      input.type = "hidden";
      input.name = "delete_video";
      input.value = "1";
      document.forms[0].appendChild(input);
  }
document.getElementById('imageUpload').addEventListener('change', function(event) {
  
    let container = event.target.closest('.col-md-12').querySelector('.row');
 
    // count how many images already displayed (old + new)
    let existingImages = container.querySelectorAll('.thumbOuter').length;

    if (existingImages + this.files.length > 5) {
        alert("You can only upload a maximum of 5 images (including existing ones).");
        this.value = ""; // reset input
        return;
    }

    [...event.target.files].forEach((file, idx) => {
        if (file.type.startsWith("image/")) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let col = document.createElement('div');
                col.classList.add('col-6', 'col-md-2', 'thumbOuter');

                let uniqueId = 'new-img-' + Date.now() + '-' + idx;

                col.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" 
                         style="width:100%;height:80px;object-fit:cover;">
                    <button type="button" class="btn btn-danger btnRemove" onclick="removeNewImage('${uniqueId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                    <input type="hidden" name="new_images[]" id="${uniqueId}" value="${file.name}">
                `;
                container.appendChild(col);
            }
            reader.readAsDataURL(file);
        }
    });
});
document.getElementById('formFile').addEventListener('change', function(event) {
    let container = event.target.closest('.col-md-12').querySelector('.row');

    [...event.target.files].forEach((file, idx) => {
        if (file.type.startsWith("image/")) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let col = document.createElement('div');
                col.classList.add('col-6', 'col-md-2', 'thumbOuter');

                let uniqueId = 'new-img-' + Date.now() + '-' + idx;

                col.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" 
                         style="width:100%;height:80px;object-fit:cover;">
                    <button type="button" class="btn btn-danger btnRemove" onclick="removeNewImage('${uniqueId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                    <input type="hidden" name="new_images[]" id="${uniqueId}" value="${file.name}">
                `;
                container.appendChild(col);
            }
            reader.readAsDataURL(file);
        }
    });
});

function removeNewImage(id) {
    let input = document.getElementById(id);
    if (input) {
        input.closest('.thumbOuter').remove();
    }
}

document.querySelector('input[name="uploadvideo"]').addEventListener('change', function(event) {
    let file = event.target.files[0];
    if (file && file.type.startsWith("video/")) {
        let container = event.target.closest('.col-md-12');

        // Remove existing old video preview (if any)
        let oldExisting = document.getElementById('existingVideo');
        if (oldExisting) oldExisting.remove();

        // Remove new preview if already exists
        let oldPrev = document.getElementById('new-video-preview');
        if (oldPrev) oldPrev.remove();

        // Create new preview
        let wrapper = document.createElement('div');
        wrapper.id = 'new-video-preview';
        wrapper.innerHTML = `
            <video controls width="200">
                <source src="${URL.createObjectURL(file)}" type="${file.type}">
            </video>
            <button type="button" class="btn btn-danger btnRemove mt-2" onclick="removeNewVideo()">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(wrapper);
    }
});


function removeNewVideo() {
    let preview = document.getElementById('new-video-preview');
    if (preview) preview.remove();
    let videoInput = document.querySelector('input[name="uploadvideo"]');
    videoInput.value = ""; // reset file input
}

function removeOldImage(imgName, idx) {
    document.getElementById('img-old-' + idx).remove();
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_images[]';
    input.value = imgName;
    document.querySelector('form').appendChild(input);
}
document.getElementById('imageUpload').addEventListener('change', function(e) {
    if (this.files.length > 5) {
        alert("You can only upload a maximum of 5 images.");
        this.value = ""; // reset input
    }
});
</script>

@endsection