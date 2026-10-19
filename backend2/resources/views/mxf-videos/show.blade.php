@extends('layouts.main')

@section('title', 'MXF Video Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">MXF Video Details - #{{ $video->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('mxf-videos.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Status and Progress -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Status</h5>
                                    <p id="status-badge">{!! $video->status_badge !!}</p>
                                    <div id="progress-container">
                                        {!! $video->progress_bar !!}
                                    </div>
                                    @if($video->error_message)
                                        <div class="alert alert-danger mt-2">
                                            <strong>Error:</strong> {{ $video->error_message }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Information</h5>
                                    <p><strong>Title:</strong> {{ $video->title }}</p>
                                    <p><strong>Voice:</strong> {{ $video->voice_id === 'ZsGLGrFQfgRJUAKsDtXX' ? 'Krishna Akabari' : 'Pratik' }}</p>
                                    <p><strong>Duration:</strong> {{ $video->video_duration }} seconds</p>
                                    <p class="mb-0"><strong>Created:</strong> {{ $video->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Video Player -->
                    @if($video->status === 'completed' && $video->final_video)
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-video"></i> Final Video</h5>
                            </div>
                            <div class="card-body">
                                <video width="100%" controls>
                                    <source src="{{ asset($video->final_video) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="mt-2">
                                    <a href="{{ asset($video->final_video) }}" class="btn btn-primary" download>
                                        <i class="fas fa-download"></i> Download Final Video
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Source Videos -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-film"></i> Source Videos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>AV.mxf</strong></td>
                                            <td>{{ basename($video->av_video ?? 'N/A') }}</td>
                                            <td>
                                                @if($video->av_video)
                                                    <span class="badge bg-success">✓ Uploaded</span>
                                                @else
                                                    <span class="badge bg-danger">✗ Missing</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->av_video)
                                                    <a href="{{ asset($video->av_video) }}" class="btn btn-sm btn-info" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>

                                        @if(!empty($video->ab_videos))
                                            @foreach($video->ab_videos as $index => $abVideo)
                                                <tr>
                                                    <td><strong>AB_{{ $index + 1 }}.mxf</strong></td>
                                                    <td>{{ basename($abVideo) }}</td>
                                                    <td><span class="badge bg-success">✓ Uploaded</span></td>
                                                    <td>
                                                        <a href="{{ asset($abVideo) }}" class="btn btn-sm btn-info" download>
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td><strong>AB_*.mxf</strong></td>
                                                <td colspan="3"><em>No AB videos uploaded</em></td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td><strong>121.mxf</strong></td>
                                            <td>{{ $video->one_to_one_video ? basename($video->one_to_one_video) : 'N/A' }}</td>
                                            <td>
                                                @if($video->one_to_one_video)
                                                    <span class="badge bg-success">✓ Uploaded</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->one_to_one_video)
                                                    <a href="{{ asset($video->one_to_one_video) }}" class="btn btn-sm btn-info" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><strong>WT.mxf</strong></td>
                                            <td>{{ $video->walk_through_video ? basename($video->walk_through_video) : 'N/A' }}</td>
                                            <td>
                                                @if($video->walk_through_video)
                                                    <span class="badge bg-success">✓ Uploaded</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->walk_through_video)
                                                    <a href="{{ asset($video->walk_through_video) }}" class="btn btn-sm btn-info" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Document & Voice Over -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Document & Voice Over</h5>
                        </div>
                        <div class="card-body">
                            @if($video->document_file)
                                <p><strong>Document File:</strong> 
                                    <a href="{{ asset($video->document_file) }}" download>
                                        {{ basename($video->document_file) }}
                                    </a>
                                </p>
                            @endif

                            @if($video->document_text)
                                <div class="mb-3">
                                    <strong>Document Text:</strong>
                                    <div class="border p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                        {{ $video->document_text }}
                                    </div>
                                </div>
                            @endif

                            @if($video->voice_over_audio)
                                <div>
                                    <strong>Generated Voice Over:</strong>
                                    <audio controls class="w-100 mt-2">
                                        <source src="{{ asset($video->voice_over_audio) }}" type="audio/mpeg">
                                        Your browser does not support the audio tag.
                                    </audio>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <a href="{{ route('mxf-videos.edit', $video->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            @if($video->status === 'failed')
                                <a href="{{ route('mxf-videos.reprocess', $video->id) }}" 
                                   class="btn btn-info"
                                   onclick="return confirm('Reprocess this video?')">
                                    <i class="fas fa-redo"></i> Reprocess
                                </a>
                            @endif

                            <form action="{{ route('mxf-videos.destroy', $video->id) }}" 
                                  method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this video? This cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh status for processing videos
$(document).ready(function() {
    @if($video->status === 'processing' || $video->status === 'pending')
        var checkInterval = setInterval(function() {
            $.ajax({
                url: '{{ route('mxf-videos.status', $video->id) }}',
                method: 'GET',
                success: function(response) {
                    // Update status badge
                    var badges = {
                        'pending': '<span class="badge bg-warning">Pending</span>',
                        'processing': '<span class="badge bg-info">Processing</span>',
                        'completed': '<span class="badge bg-success">Completed</span>',
                        'failed': '<span class="badge bg-danger">Failed</span>'
                    };
                    $('#status-badge').html(badges[response.status]);

                    // Update progress bar
                    var color = response.status === 'failed' ? 'danger' : (response.status === 'completed' ? 'success' : 'info');
                    $('#progress-container').html(
                        '<div class="progress">' +
                        '<div class="progress-bar bg-' + color + '" role="progressbar" style="width: ' + response.progress + '%">' +
                        response.progress + '%' +
                        '</div>' +
                        '</div>'
                    );

                    // If completed or failed, reload page
                    if (response.status === 'completed' || response.status === 'failed') {
                        clearInterval(checkInterval);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function() {
                    clearInterval(checkInterval);
                }
            });
        }, 5000); // Check every 5 seconds
    @endif
});
</script>
@endpush
