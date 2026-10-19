@extends('layouts.main')

@section('title', 'Upload New MXF Video')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload New MXF Video</h3>
                    <div class="card-tools">
                        <a href="{{ route('mxf-videos.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('mxf-videos.store') }}" method="POST" enctype="multipart/form-data" id="mxfForm">
                    @csrf

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Title -->
                        <div class="form-group mb-3">
                            <label for="title">Title (Optional)</label>
                            <input type="text" name="title" id="title" class="form-control" 
                                   value="{{ old('title') }}" placeholder="Auto-generated if empty">
                        </div>

                        <!-- AV Video (Required) -->
                        <div class="form-group mb-3">
                            <label for="av_video">AV.mxf <span class="text-danger">*</span></label>
                            <input type="file" name="av_video" id="av_video" class="form-control" required
                                   accept=".mxf,.mp4,.mov,.avi">
                            <small class="form-text text-muted">Main video file (required)</small>
                        </div>

                        <!-- Multiple AB Videos -->
                        <div class="form-group mb-3">
                            <label for="ab_videos">AB_1.mxf, AB_2.mxf, ... (Optional - Multiple)</label>
                            <input type="file" name="ab_videos[]" id="ab_videos" class="form-control" 
                                   accept=".mxf,.mp4,.mov,.avi" multiple>
                            <small class="form-text text-muted">Select multiple byte AB videos (will be added in sequence)</small>
                        </div>

                        <!-- 121 Video -->
                        <div class="form-group mb-3">
                            <label for="one_to_one_video">121.mxf (Optional)</label>
                            <input type="file" name="one_to_one_video" id="one_to_one_video" class="form-control"
                                   accept=".mxf,.mp4,.mov,.avi">
                            <small class="form-text text-muted">One-to-one video</small>
                        </div>

                        <!-- WT Video -->
                        <div class="form-group mb-3">
                            <label for="walk_through_video">WT.mxf (Optional)</label>
                            <input type="file" name="walk_through_video" id="walk_through_video" class="form-control"
                                   accept=".mxf,.mp4,.mov,.avi">
                            <small class="form-text text-muted">Walk-through video</small>
                        </div>

                        <hr>

                        <!-- Document Upload OR Text Input -->
                        <div class="form-group mb-3">
                            <label>Document / Script</label>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#text-tab">Enter Text</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#file-tab">Upload File</a>
                                </li>
                            </ul>
                            <div class="tab-content border border-top-0 p-3">
                                <div id="text-tab" class="tab-pane fade show active">
                                    <textarea name="document_text" id="document_text" class="form-control" rows="6"
                                              placeholder="Enter script text here for voice-over...">{{ old('document_text') }}</textarea>
                                    <small class="form-text text-muted">Voice-over will be generated from this text</small>
                                </div>
                                <div id="file-tab" class="tab-pane fade">
                                    <input type="file" name="document_file" id="document_file" class="form-control"
                                           accept=".doc,.docx,.txt,.pdf">
                                    <small class="form-text text-muted">Upload DOC/DOCX/TXT/PDF file (text will be extracted)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Voice Over Selection -->
                        <div class="form-group mb-3">
                            <label for="voice_id">Voice Over <span class="text-danger">*</span></label>
                            <select name="voice_id" id="voice_id" class="form-control" required>
                                @foreach($voices as $id => $name)
                                    <option value="{{ $id }}" {{ old('voice_id', 'ZsGLGrFQfgRJUAKsDtXX') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select voice for text-to-speech generation</small>
                        </div>

                        <hr>

                        <!-- Video Sequence Info -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Video Processing Sequence:</h5>
                            <ol class="mb-0">
                                <li><strong>AV.mxf</strong> - Main video with voice-over overlay</li>
                                <li><strong>AB_1.mxf, AB_2.mxf, ...</strong> - Byte videos (if provided)</li>
                                <li><strong>121.mxf</strong> - One-to-one video (if provided)</li>
                                <li><strong>WT.mxf</strong> - Walk-through video (if provided)</li>
                            </ol>
                            <p class="mt-2 mb-0"><small>Final MP4 will be created by concatenating all videos in this sequence.</small></p>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-upload"></i> Upload & Process
                        </button>
                        <a href="{{ route('mxf-videos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#mxfForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    });
    
    // File size warnings
    $('input[type="file"]').on('change', function() {
        var maxSize = 500 * 1024 * 1024; // 500MB
        var files = this.files;
        
        for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                alert('File ' + files[i].name + ' is larger than 500MB!');
                $(this).val('');
                return;
            }
        }
    });
});
</script>
@endpush
