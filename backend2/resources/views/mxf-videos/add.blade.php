@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>MXF Videos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/mxf-videos') }}">MXF Videos</a></li>
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

                        <form class="row g-3 needs-validation" method="POST" action="{{ url('/mxf-videos/store') }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Title (Optional)</label>
                                            <input type="text" name="title" class="form-control" id="title"
                                                value="{{ old('title') }}" placeholder="Auto-generated if left empty">
                                            @error('title')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select id="category_id" name="category_id" class="form-select">
                                                <option value="">Select Category</option>
                                                @foreach ($category as $parentCategory)
                                                    @if ($parentCategory->parentID == 0 || $parentCategory->parentID == $parentCategory->id)
                                                        <option value="{{ $parentCategory->id }}">
                                                            {{ $parentCategory->title }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_jacket" value="1" id="is_jacket">
                                                <label class="form-check-label" for="is_jacket">
                                                    Add Category Jacket (Overlay)
                                                </label>
                                            </div>
                                            <small class="text-muted">Adds category jacket with title and date overlay</small>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="voice_id" class="form-label">Voice Over <span class="text-danger">*</span></label>
                                            <select name="voice_id" id="voice_id" class="form-control" required>
                                                <option value="pooja_gu_conversational" selected>Pooja (Gujarati Female - Conversational)</option>
                                                <option value="pooja_gu_customer">Pooja (Gujarati Female - Customer Service)</option>
                                                <option value="bhavik_gu_conversation">Bhavik (Gujarati Male - Conversational)</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a voice.
                                            </div>
                                            @error('voice_id')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- AV Video (Required) -->
                                        <div class="col-md-12 mt-3">
                                            <label for="av_video" class="form-label">AV Video <span class="text-danger">*</span></label>
                                            <input type="file" name="av_video" class="form-control" id="av_video"
                                                accept="video/mxf,video/mp4,video/mov,video/avi,video/x-matroska" required>
                                            <small class="text-muted">Required. Formats: MXF, MP4, MOV, AVI (Max: 2GB)</small>
                                            <div class="invalid-feedback">
                                                Please select AV video file.
                                            </div>
                                            @error('av_video')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- AB Videos (Multiple) -->
                                        <div class="col-md-12 mt-3">
                                            <label for="ab_videos" class="form-label">AB Videos (Multiple)</label>
                                            <input type="file" name="ab_videos[]" class="form-control" id="ab_videos"
                                                accept="video/mxf,video/mp4,video/mov,video/avi,video/x-matroska" multiple>
                                            <small class="text-muted">Optional. Select multiple files (AB_1, AB_2, AB_3, etc.)</small>
                                            @error('ab_videos.*')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- 121 Video -->
                                        <div class="col-md-12 mt-3">
                                            <label for="one_to_one_video" class="form-label">121 Video (One to One)</label>
                                            <input type="file" name="one_to_one_video" class="form-control" id="one_to_one_video"
                                                accept="video/mxf,video/mp4,video/mov,video/avi,video/x-matroska">
                                            <small class="text-muted">Optional. Format: MXF, MP4, MOV, AVI</small>
                                            @error('one_to_one_video')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- WT Video (Walk Through) -->
                                        <div class="col-md-12 mt-3">
                                            <label for="walk_through_video" class="form-label">WT Video (Walk Through)</label>
                                            <input type="file" name="walk_through_video" class="form-control" id="walk_through_video"
                                                accept="video/mxf,video/mp4,video/mov,video/avi,video/x-matroska">
                                            <small class="text-muted">Optional. Format: MXF, MP4, MOV, AVI</small>
                                            @error('walk_through_video')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Document File -->
                                        <div class="col-md-12 mt-3">
                                            <label for="document_file" class="form-label">Document File (Optional)</label>
                                            <input type="file" name="document_file" class="form-control" id="document_file"
                                                accept=".doc,.docx,.txt,.pdf">
                                            <small class="text-muted">Optional. Formats: DOC, DOCX, TXT, PDF (Max: 10MB)</small>
                                            @error('document_file')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Document Text (Textarea) -->
                                        <div class="col-md-12 mt-3">
                                            <label for="document_text" class="form-label">Document Text (Voice Over Script)</label>
                                            <textarea name="document_text" class="form-control" id="document_text" rows="6"
                                                placeholder="Enter the voice-over script here. This text will be converted to speech and played over the AV video.">{{ old('document_text') }}</textarea>
                                            <small class="text-muted">Optional. Enter text for voice-over or upload document above.</small>
                                            @error('document_text')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Social Media Sharing -->
                                        <div class="col-md-12 mt-4">
                                            <label class="form-label d-block"><strong>Share on Social Media (After Processing)</strong></label>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="facebook" id="social_facebook"
                                                            {{ in_array('facebook', old('social_media', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_facebook">
                                                            <i class="bi bi-facebook text-primary"></i> Facebook
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="twitter" id="social_twitter"
                                                            {{ in_array('twitter', old('social_media', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_twitter">
                                                            <i class="bi bi-twitter text-info"></i> Twitter
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="youtube" id="social_youtube"
                                                            {{ in_array('youtube', old('social_media', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_youtube">
                                                            <i class="bi bi-youtube text-danger"></i> YouTube
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="instagram" id="social_instagram"
                                                            {{ in_array('instagram', old('social_media', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_instagram">
                                                            <i class="bi bi-instagram text-danger"></i> Instagram
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted">Select platforms where you want to share this video after processing completes</small>
                                        </div>

                                        <!-- Video Sequence Info -->
                                        <div class="col-md-12 mt-3">
                                            <div class="alert alert-info">
                                                <strong>Video Sequence:</strong><br>
                                                1. AV Video (with voice-over and original audio at 30%)<br>
                                                2. AB_1 Video (if uploaded)<br>
                                                3. AB_2 Video (if uploaded)<br>
                                                4. AB_3 Video (if uploaded)<br>
                                                5. 121 Video (if uploaded)<br>
                                                6. WT Video (if uploaded)<br><br>
                                                <strong>Note:</strong> The AV video duration will be adjusted to match the voice-over duration.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-upload"></i> Upload & Process Video
                                    </button>
                                    <a href="{{ url('/mxf-videos') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
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
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// File upload validation
document.getElementById('av_video').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // in MB
        if (fileSize > 2048) {
            alert('File size must not exceed 2GB (2048MB)');
            this.value = '';
        }
    }
});
</script>

@endsection
