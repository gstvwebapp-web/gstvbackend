@extends('layouts.app')

@section('content')
<main id="main" class="main">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>News</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/news') }}">News</a></li>
          <li class="breadcrumb-item active">Publish</li>
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
                <div class="card-body newslistdata">
                <div class="row">
                  <div class="col-lg-6">
                    <h5 class="card-title">News</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/news/add') }}">Create News</a>   
                  </div>
                </div> 
                <input type="hidden" id="statustype" value="Active"/>
                <div class="row">
                  <div class="col-lg-12 ">
                    <!-- <a href="{{ url('/news') }}">All</a> | 
                    <a href="{{ url('/news/publish') }}">Publish ({{$data['publish']}})</a> |  -->
                    <a href="{{ url('/news') }}" class="publishnewsdata btn btn-success">Publish ({{$data['publish']}})</a>
                    <!-- <button id="activeButton" class="publishnewsdata btn btn-success">Publish ({{$data['publish']}})</button> -->
                    <button id="unpublishButton" class="publishnewsdata btn btn-secondary">Draft ({{$data['unpublish']}})</button>                    
                    <button id="scheduleButton" class="publishnewsdata btn btn-secondary">Schedule ({{$data['schedule']}})</button>
                    <button id="videoButton" class="publishnewsdata btn btn-secondary">Videos ({{$data['videos']}})</button>
                    <!-- <a href="{{ url('/news/unpublish') }}">Draft ({{$data['unpublish']}})</a> | 
                    <a href="{{ url('/news/schedule') }}">Scheduled ({{$data['schedule']}})</a> | 
					           <a href="{{ url('/news/videos') }}">Videos ({{$data['videos']}})</a> -->
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-lg-12">
                  <form id="newsformsearch" class="" method="POST" action="{{ url('/news') }}" enctype="multipart/form-data" novalidate>
                	@csrf
                  <div class="row">
                    <div class="col-lg-2">
                    <input class="form-select" style="background-image:none;" type="text" placeholder="Search By title" name="title" value="{{  $data['title'] }}"  id="title"/>
                    </div>
                  <div class="col-lg-2">
                    <select class="form-select" name="userID" id="validationCustom33">
                      <option value="">Select User</option>
                      @foreach ($data['users'] as $user)
                          <option value="{{ $user->id }}" @if($user->id==$data['userID']) {{ 'selected' }} @endif>{{ $user->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 
                  <div class="col-lg-2">
                  <select class="form-select" id="selectcatID" name="catID" id="validationCustom33">
                  <option value="">Select Category</option>
                    @foreach ($data['category'] as $cat)
                        <option value="{{ $cat->id }}" @if($cat->id==$data['catID']) {{ 'selected' }} @endif>{{ $cat->title }}</option>
                    @endforeach
                  </select>
                </div>
                  <div class="col-lg-3">
                      <div class="col-lg-12 d-flex align-items-center">
                          <label for="userstart_date" class="me-2"  >Start Date:</label>
                          <input type="date" id="userstart_date" name="start_date" class="form-control" placeholder="Start Date" value="{{  $data['start_date'] }}">
                      </div>
                  </div>
                  <div class="col-lg-3">
                      <div class="col-lg-12 d-flex align-items-center">
                          <label for="userend_date" class="me-2"  >End Date:</label>
                          <input type="date" id="userend_date" name="end_date" class="form-control" placeholder="End Date" value="{{  $data['end_date'] }}">
                      </div>
                  </div>
                   <div class="col-lg-3">
                    <select class="form-select" name="newssource" id="validationCustom33">
                      <option value="">Select News Source</option>
                      @foreach ($data['newssourceList'] as $newssr)
                          <option value="{{ $newssr->id }}" @if($newssr->id==$data['newssource']) {{ 'selected' }} @endif>{{ $newssr->title }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-lg-12">
                  <button style="float:right" class="btn btn-primary" type="submit">Search</button>
                  </div>
                </div></form>
                  </div>
                </div>
               <br>
              <!-- Table with stripped rows -->
              <table class="table table-bordered display" id="newstabledata1">
              
        <thead>
            <tr>
                <th>No.</th>
                <th>Title</th>
                <th>Category</th>
                <th>Count</th>
                <th>Source</th>
                <th>Author</th>
                <th>Created Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
              <!-- End Table with stripped rows -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

@if($data['start_date'] || $data['sort_col'] != 5 || $data['sort_dir'] != 'desc' || $data['tab'] == 'video')
<script>
// Pre-apply filters, sort and tab from URL params (e.g. from dashboard "View More" link)
(function waitForDT() {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
        setTimeout(waitForDT, 50);
        return;
    }

    // Pre-fill date inputs if passed via URL
    @if($data['start_date'])
        document.getElementById('userstart_date').value = '{{ $data['start_date'] }}';
    @endif
    @if($data['end_date'])
        document.getElementById('userend_date').value = '{{ $data['end_date'] }}';
    @endif

    @if($data['tab'] == 'video')
    // Auto-activate Videos tab: set today's date, trigger video button, sort by count desc
    (function activateVideoTab() {
        var today = '{{ date('Y-m-d') }}';
        document.getElementById('userstart_date').value = today;
        document.getElementById('userend_date').value   = today;

        var checkDT = setInterval(function () {
            if ($.fn.DataTable.isDataTable('#newstabledata1')) {
                clearInterval(checkDT);

                // Highlight the video button as active
                $(".publishnewsdata").addClass('btn-secondary').removeClass('btn-success');
                $("#videoButton").removeClass('btn-secondary').addClass('btn-success');
                $("#statustype").val('video');
                $("#selectcatID").val('9');

                // Load video data with today's date filter and sort by count (col 3) desc
                var bsurl = $('meta[name="base-url"]').attr('content');
                var dt = $('#newstabledata1').DataTable();
                dt.ajax.url(
                    bsurl + '/news/fetchData?status=video'
                    + '&start_date=' + today
                    + '&end_date='   + today
                ).load(function () {
                    // After data loads, apply count column sort descending
                    dt.order([3, 'desc']).draw(false);
                });
            }
        }, 100);
    })();
    @else
    // Apply sort column and direction for other tabs
    var sortCol = {{ (int)$data['sort_col'] }};
    var sortDir = '{{ $data['sort_dir'] }}';

    var checkDT = setInterval(function () {
        if ($.fn.DataTable.isDataTable('#newstabledata1')) {
            clearInterval(checkDT);
            var dt = $('#newstabledata1').DataTable();
            dt.order([sortCol, sortDir]).draw();
        }
    }, 100);
    @endif
})();
</script>
@endif

@endsection