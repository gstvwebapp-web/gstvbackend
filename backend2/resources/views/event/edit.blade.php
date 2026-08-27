@extends('layouts.app')

@section('content')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Edit Event</h1>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body pt-4">
        <form method="POST" action="{{ url('/events/update') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="event_id" value="{{ $data['event']->event_id }}">

          <div class="mb-3">
            <label>Event Title</label>
            <input type="text" name="event_title" class="form-control" value="{{ $data['event']->event_title }}" required>
          </div>

          <div class="mb-3">
            <label>English Title</label>
            <input type="text" name="event_eng_title" class="form-control" value="{{ $data['event']->event_eng_title }}" required>
          </div>

          <div class="mb-3">
            <label>City</label>
            <input type="text" name="event_city" class="form-control" value="{{ $data['event']->event_city }}" required>
          </div>

          <div class="mb-3">
            <label>Location</label>
            <input type="text" name="event_location" class="form-control" value="{{ $data['event']->event_location }}" required>
          </div>

          <div class="mb-3">
            <label>Description</label>
            <textarea name="event_desc" class="form-control newfeatures" required rows="4">{{ $data['event']->event_desc }}</textarea>
          </div>

          @php
            $stime = explode(' ',$data['event']->event_start_date);
            $sttime =explode(':',$stime[1]);
            $st_time = $sttime[0].':'.$sttime[1];
            $etime = explode(' ',$data['event']->event_end_date);
            $ettime =explode(':',$etime[1]);
            $et_time = $ettime[0].':'.$ettime[1];
          @endphp

          <div class="row">
            <div class="col-lg-6 mb-3">
              <label>Start Date</label>
              <input type="date" name="event_start_date" class="form-control" value="{{ $stime[0] }}" required>
            </div>

            <div class="col-lg-6 mb-3">
              <label>Start Time</label>
              <input type="time" name="event_start_time" class="form-control" value="{{ $st_time }}" required>
            </div>

            <div class="col-lg-6 mb-3">
              <label>End Date</label>
              <input type="date" name="event_end_date" class="form-control" value="{{ $etime[0] }}" required>
            </div>

            <div class="col-lg-6 mb-3">
              <label>End Time</label>
              <input type="time" name="event_end_time" class="form-control" value="{{ $et_time }}" required>
            </div>
          </div>

          <div class="mb-3">
              <label>Image</label>
              @php $media = $data['event']->event_img; @endphp

              <div id="image-upload-section">
                 

                  <div id="new-image-upload">
                      <input type="file" name="event_img" id="event_img" class="form-control" accept="image/*" {{ $media ? '' : 'required' }} onchange="previewSelectedImage(event)">
                      <div id="image-preview" class="mt-2" style="display:none;">
                          <img id="preview-img" src="" alt="Preview" style="height:80px;">
                      </div>
                  </div>
                   @if($media)
                      <div class="mt-2" id="existing-image">
                          <img src="{{ config('app.backendnewsurl') }}/public/uploads/events/{{ $media }}" alt="Event Image" style="height:80px;">
                          <br>
                          <button type="button" class="btn btn-danger btn-sm mt-1" onclick="deleteExistingImage()">Delete</button>
                      </div>
                  @endif

              </div>

              <input type="hidden" name="delete_image" id="delete_image" value="0">
          </div>
        
          <div class="col-lg-6 mb-3">
            <label>Status</label>
            <select class="form-select form-control"  name="status" style="width:100%">
              <option value="Under Review" {{ $data['event']->status == 'Under Review' ? 'selected' : '' }}>Under Review</option>
              <option value="Active" {{ $data['event']->status == 'Active' ? 'selected' : '' }}>Active</option>
              <option value="Rejected" {{ $data['event']->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

          </div>

         

          <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
      </div>
    </div>
  </section>
</main>

 <script>
function deleteExistingImage() {
    // Hide existing image
    document.getElementById('existing-image').style.display = 'none';

    // Show file input
    document.getElementById('new-image-upload').style.display = 'block';

    // Set hidden field to notify backend that image needs to be deleted
    document.getElementById('delete_image').value = '1';

    // Make input required
    document.getElementById('event_img').required = true;
}

function previewSelectedImage(event) {
    const input = event.target;
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('preview-img').src = reader.result;
        document.getElementById('image-preview').style.display = 'block';
    };
    if (input.files && input.files[0]) {
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
