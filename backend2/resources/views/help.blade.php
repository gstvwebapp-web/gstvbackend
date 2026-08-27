@extends('layouts.sudokuapp')

@section('meta-title')
GSTV | Sudoku
@endsection

@section('content')

<style>
    .fullscreen-video {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        object-fit: cover; /* screen fill */
        z-index: -1;
    }
</style>

<video
    class="fullscreen-video"
    autoplay
    muted
    loop
    playsinline
>
    <source src="https://staging.gstv.in/backend/public/media/2026/06/19/AHDWEATHERDEPTAV2026mp4_ssUufyCc1781859552.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

@endsection