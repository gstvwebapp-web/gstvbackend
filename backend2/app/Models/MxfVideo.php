<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MxfVideo extends Model
{
    use HasFactory;

    protected $table = 'mxf_videos';

    protected $fillable = [
        'title',
        'category_id',
        'is_jacket',
        'social_media_platforms',
        'is_shared',
        'shared_at',
        'av_video',
        'ab_videos',
        'one_to_one_video',
        'walk_through_video',
        'document_text',
        'document_file',
        'voice_id',
        'voice_over_audio',
        'final_video',
        'video_duration',
        'status',
        'error_message',
        'progress',
    ];

    protected $casts = [
        'ab_videos' => 'array',
        'social_media_platforms' => 'array',
        'shared_at' => 'datetime',
    ];

    /**
     * Get formatted status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }

    /**
     * Get progress bar HTML
     */
    public function getProgressBarAttribute()
    {
        $color = $this->status === 'failed' ? 'danger' : ($this->status === 'completed' ? 'success' : 'info');
        return '<div class="progress">
            <div class="progress-bar bg-' . $color . '" role="progressbar" style="width: ' . $this->progress . '%">
                ' . $this->progress . '%
            </div>
        </div>';
    }
}
