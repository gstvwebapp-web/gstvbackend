@extends('layouts.main')

@section('title', 'MXF Videos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">MXF Videos</h3>
                    <a href="{{ route('mxf-videos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload New MXF Video
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Videos</th>
                                    <th>Voice</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Duration</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($videos as $video)
                                    <tr>
                                        <td>{{ $video->id }}</td>
                                        <td>{{ $video->title }}</td>
                                        <td>
                                            <small>
                                                AV: {{ $video->av_video ? '✓' : '✗' }}<br>
                                                AB: {{ !empty($video->ab_videos) ? count($video->ab_videos) : '0' }}<br>
                                                121: {{ $video->one_to_one_video ? '✓' : '✗' }}<br>
                                                WT: {{ $video->walk_through_video ? '✓' : '✗' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{ $video->voice_id === 'ZsGLGrFQfgRJUAKsDtXX' ? 'Krishna' : 'Pratik' }}</small>
                                        </td>
                                        <td>{!! $video->status_badge !!}</td>
                                        <td style="min-width: 120px;">{!! $video->progress_bar !!}</td>
                                        <td>{{ $video->video_duration }}s</td>
                                        <td>{{ $video->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('mxf-videos.show', $video->id) }}" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($video->status === 'failed')
                                                    <a href="{{ route('mxf-videos.reprocess', $video->id) }}" 
                                                       class="btn btn-sm btn-warning" title="Reprocess"
                                                       onclick="return confirm('Reprocess this video?')">
                                                        <i class="fas fa-redo"></i>
                                                    </a>
                                                @endif
                                                
                                                <form action="{{ route('mxf-videos.destroy', $video->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Delete this video?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No MXF videos found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $videos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh page for processing videos
$(document).ready(function() {
    var hasProcessing = {{ $videos->where('status', 'processing')->count() > 0 ? 'true' : 'false' }};
    
    if (hasProcessing) {
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    }
});
</script>
@endpush
