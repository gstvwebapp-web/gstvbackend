@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>MXF Videos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/mxf-videos') }}">MXF Videos</a></li>
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

                        <form class="row g-3 needs-validation" method="POST" action="{{ url('/mxf-videos/update') }}" novalidate>
                            @csrf
                            <input type="hidden" name="id" value="{{ $data['mxfvideo']->id }}">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Status Badge -->
                                    <div class="col-md-12 mb-3">
                                        @php
                                            $statusClass = 'secondary';
                                            switch($data['mxfvideo']->status) {
                                                case 'pending': $statusClass = 'warning'; break;
                                                case 'processing': $statusClass = 'info'; break;
                                                case 'completed': $statusClass = 'success'; break;
                                                case 'failed': $statusClass = 'danger'; break;
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }} fs-6">
                                            Status: {{ strtoupper($data['mxfvideo']->status) }}
                                        </span>
                                        @if($data['mxfvideo']->status == 'processing')
                                            <span class="badge bg-info fs-6 ms-2">
                                                Progress: {{ $data['mxfvideo']->progress }}%
                                            </span>
                                        @endif
                                    </div>

                                    @if($data['mxfvideo']->error_message)
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-danger">
                                            <strong>Error:</strong> {{ $data['mxfvideo']->error_message }}
                                        </div>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" id="title"
                                                value="{{ $data['mxfvideo']->title }}">
                                            @error('title')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select id="category_id" name="category_id" class="form-select">
                                                <option value="">Select Category</option>
                                                @foreach ($data['category'] as $parentCategory)
                                                    @if ($parentCategory->parentID == 0 || $parentCategory->parentID == $parentCategory->id)
                                                        <option value="{{ $parentCategory->id }}" 
                                                            {{ $data['mxfvideo']->category_id == $parentCategory->id ? 'selected' : '' }}>
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
                                                <input class="form-check-input" type="checkbox" name="is_jacket" value="1" id="is_jacket"
                                                    {{ $data['mxfvideo']->is_jacket == 1 ? 'checked' : '' }}>
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
                                                <option value="pooja_gu_conversational" {{ $data['mxfvideo']->voice_id == 'pooja_gu_conversational' ? 'selected' : '' }}>Pooja (Gujarati Female - Conversational)</option>
                                                <option value="pooja_gu_customer" {{ $data['mxfvideo']->voice_id == 'pooja_gu_customer' ? 'selected' : '' }}>Pooja (Gujarati Female - Customer Service)</option>
                                                <option value="bhavik_gu_conversation" {{ $data['mxfvideo']->voice_id == 'bhavik_gu_conversation' ? 'selected' : '' }}>Bhavik (Gujarati Male - Conversational)</option>
                                            </select>
                                            @error('voice_id')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Document Text -->
                                        <div class="col-md-12 mt-3">
                                            <label for="document_text" class="form-label">Document Text (Voice Over Script)</label>
                                            <textarea name="document_text" class="form-control" id="document_text" rows="6">{{ $data['mxfvideo']->document_text }}</textarea>
                                            @error('document_text')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Social Media Sharing -->
                                        <div class="col-md-12 mt-4">
                                            <label class="form-label d-block"><strong>Share on Social Media</strong></label>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="facebook" id="social_facebook"
                                                            {{ in_array('facebook', $data['mxfvideo']->social_media_platforms ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_facebook">
                                                            <i class="bi bi-facebook text-primary"></i> Facebook
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="twitter" id="social_twitter"
                                                            {{ in_array('twitter', $data['mxfvideo']->social_media_platforms ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_twitter">
                                                            <i class="bi bi-twitter text-info"></i> Twitter
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="youtube" id="social_youtube"
                                                            {{ in_array('youtube', $data['mxfvideo']->social_media_platforms ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_youtube">
                                                            <i class="bi bi-youtube text-danger"></i> YouTube
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="social_media[]" value="instagram" id="social_instagram"
                                                            {{ in_array('instagram', $data['mxfvideo']->social_media_platforms ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="social_instagram">
                                                            <i class="bi bi-instagram text-danger"></i> Instagram
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted">Select platforms where you want to share this video</small>
                                            
                                            @if($data['mxfvideo']->is_shared && $data['mxfvideo']->shared_at)
                                            <div class="alert alert-success mt-2">
                                                <i class="bi bi-check-circle"></i> Shared on {{ $data['mxfvideo']->shared_at->format('d-m-Y H:i') }}
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Uploaded Files Info -->
                                        <div class="col-md-12 mt-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <strong>Uploaded Files</strong>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group">
                                                        @if($data['mxfvideo']->av_video)
                                                            <li class="list-group-item">
                                                                <i class="bi bi-file-earmark-play text-primary"></i> 
                                                                <strong>AV Video:</strong> 
                                                                <a href="{{ $data['mxfvideo']->av_video }}" target="_blank">View</a>
                                                            </li>
                                                        @endif
                                                        
                                                        @if(!empty($data['mxfvideo']->ab_videos))
                                                            @foreach($data['mxfvideo']->ab_videos as $index => $abVideo)
                                                                <li class="list-group-item">
                                                                    <i class="bi bi-file-earmark-play text-info"></i> 
                                                                    <strong>AB_{{ $index + 1 }} Video:</strong> 
                                                                    <a href="{{ $abVideo }}" target="_blank">View</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                        
                                                        @if($data['mxfvideo']->one_to_one_video)
                                                            <li class="list-group-item">
                                                                <i class="bi bi-file-earmark-play text-success"></i> 
                                                                <strong>121 Video:</strong> 
                                                                <a href="{{ $data['mxfvideo']->one_to_one_video }}" target="_blank">View</a>
                                                            </li>
                                                        @endif
                                                        
                                                        @if($data['mxfvideo']->walk_through_video)
                                                            <li class="list-group-item">
                                                                <i class="bi bi-file-earmark-play text-warning"></i> 
                                                                <strong>WT Video:</strong> 
                                                                <a href="{{ $data['mxfvideo']->walk_through_video }}" target="_blank">View</a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Final Video -->
                                        @if($data['mxfvideo']->status == 'completed' && $data['mxfvideo']->final_video)
                                        <div class="col-md-12 mt-3">
                                            <div class="card border-success">
                                                <div class="card-header bg-success text-white">
                                                    <strong><i class="bi bi-check-circle"></i> Final Processed Video</strong>
                                                </div>
                                                <div class="card-body">
                                                    <video width="100%" controls style="max-width: 800px;">
                                                        <source src="{{ $data['mxfvideo']->final_video }}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <div class="mt-2">
                                                        <a href="{{ $data['mxfvideo']->final_video }}" class="btn btn-success" download>
                                                            <i class="bi bi-download"></i> Download Final Video
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Voice Over Audio -->
                                        @if($data['mxfvideo']->voice_over_audio)
                                        <div class="col-md-12 mt-3">
                                            <label class="form-label">Voice Over Audio</label>
                                            <div>
                                                <audio controls style="width: 100%; max-width: 500px;">
                                                    <source src="{{ $data['mxfvideo']->voice_over_audio }}" type="audio/mpeg">
                                                    Your browser does not support the audio tag.
                                                </audio>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-save"></i> Update MXF Video
                                    </button>
                                    
                                    @if($data['mxfvideo']->status == 'failed')
                                        <a href="{{ url('/mxf-videos/reprocess/' . $data['mxfvideo']->id) }}" class="btn btn-warning">
                                            <i class="bi bi-arrow-clockwise"></i> Reprocess Video
                                        </a>
                                    @endif
                                    
                                    @if($data['mxfvideo']->status == 'completed' && $data['mxfvideo']->final_video && !empty($data['mxfvideo']->social_media_platforms))
                                        <button type="button" class="btn btn-success" id="shareButton">
                                            <i class="bi bi-share"></i> Share on Social Media
                                        </button>
                                    @endif
                                    
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

// Auto-refresh status if processing
@if($data['mxfvideo']->status == 'processing' || $data['mxfvideo']->status == 'pending')
setInterval(function() {
    fetch('{{ url("/mxf-videos/status/" . $data["mxfvideo"]->id) }}')
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $data["mxfvideo"]->status }}') {
                location.reload();
            }
        });
}, 5000); // Check every 5 seconds
@endif

// Social Media Share Button
@if($data['mxfvideo']->status == 'completed' && $data['mxfvideo']->final_video)
document.getElementById('shareButton')?.addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sharing...';

    fetch('{{ url("/mxf-videos/share") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: {{ $data['mxfvideo']->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success) {
            let message = 'Video sharing links generated!\n\n';
            
            if (data.share_urls.facebook) {
                message += 'Facebook: Opening share dialog...\n';
                window.open(data.share_urls.facebook, '_blank', 'width=600,height=400');
            }
            
            if (data.share_urls.twitter) {
                message += 'Twitter: Opening share dialog...\n';
                setTimeout(() => window.open(data.share_urls.twitter, '_blank', 'width=600,height=400'), 500);
            }
            
            if (data.share_urls.youtube) {
                message += 'YouTube: Opening upload page...\n';
                setTimeout(() => window.open(data.share_urls.youtube, '_blank'), 1000);
            }
            
            if (data.share_urls.instagram) {
                message += 'Instagram: Please upload manually from your mobile app.\n';
            }
            
            message += '\nVideo URL: ' + data.video_url;
            
            alert(message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Error sharing video: ' + error.message);
    });
});
@endif
</script>

@endsection
