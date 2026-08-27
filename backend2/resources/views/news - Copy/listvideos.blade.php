@extends('layouts.app')

@section('content')
<main id="main" class="main">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>News</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">News</li>
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
                <div class="row">
                  <div class="col-lg-6">
                    <a href="{{ url('/news') }}">All</a> | 
                    <a href="{{ url('/news/publish') }}">Publish ({{$data['publish']}})</a> | 
                    <a href="{{ url('/news/unpublish') }}">Draft ({{$data['unpublish']}})</a> | 
                    <a href="{{ url('/news/schedule') }}">Scheduled ({{$data['schedule']}})</a> | 
					<a href="{{ url('/news/videos') }}">Videos  ({{$data['videos']}})</a>
                  </div>
                  <div class="col-lg-6">
                 
                  <form id="newsformsearch" class="" method="POST" action="{{ url('/news/videos') }}" enctype="multipart/form-data" novalidate>
                @csrf
                    <div class="row">
                      <div class="col-lg-3">
                  		<input class="form-select" type="text" style="background-image:none;" placeholder="Search By title" value="{{  $data['title'] }}" name="title" id="title"/>
                      </div>
                    <div class="col-lg-3">
                  <select class="form-select" name="userID" id="validationCustom33">
                    <option value="">Select User</option>
                    @foreach ($data['users'] as $user)
                        <option value="{{ $user->id }}" @if($user->id==$data['userID']) {{ 'selected' }} @endif>{{ $user->name }}</option>
                    @endforeach
                  </select></div>
                  <div class="col-lg-3">
                  <select class="form-select" name="catID" id="validationCustom33">
                  <option value="">Select Category</option>
                    @foreach ($data['category'] as $cat)
                        <option value="{{ $cat->id }}" @if($cat->id==$data['catID']) {{ 'selected' }} @endif>{{ $cat->title }}</option>
                    @endforeach
                  </select></div>
                  <div class="col-lg-3">
                  <button class="btn btn-primary" type="submit">Search</button>
                  </div>
                </div></form>
                
                  </div>
                </div>
                

<table class="datatable table table-bordered" id="newstabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">News Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">View Count</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $j = 0;
                  @endphp
                  
                  @foreach ($data['news'] as $news)
                  @php
                    $viewer = 0;
                    if($news->viewer!=NULL || $news->viewer!=0) {
                      $viewer = $news->viewer; 
                    }
                    $catIDs = explode(',', $news->catID);
                    $baseUrl = config('app.front_url');
                    $newsUrl = $baseUrl."/".$news->categorySlugs."/".$news->slug;
                    $newsvideoUrl = $baseUrl."/news/".$news->categorySlugs."/".$news->slug;
                    $bgstyle = '';
                    if($j<=3)
                    {
                        $bgstyle = 'style=background:#f1f1f1;';
                    }
                  
                  @endphp
                  <tr data-id="{{ $news->id }}" data-top="{{$data['userID'].$data['catID']}}" {{$bgstyle}}>
                    <th scope="row">{{ ++$j }}</th>
                      @if (in_array('9', $catIDs))
                        <td><a href="{{ $newsUrl }}" target="_blank">{{ $news->title }}</a></td>
                      @else
                        <td><a href="{{ $newsvideoUrl }}" target="_blank">{{ $news->title }}</a></td>
                      @endif
                    <td>{{ $news->categoryNames }}</td>
                    <td>{{ $viewer }}</td>
                    <td>{{ $news->authorName }}</td> 
                    <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($news->created_at))->format('d-m-Y h:i A')}}</td> 
                    <td>
                    <form action="{{ url('/news/destroy',$news->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($news->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Publish" href="{{ url('/news/updateStatus/Inactive',$news->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @elseif($news->status=='Inactive')
                          <a class="btn iconbtn unpublishcls showhideCls btn-success" title="Unpublish" href="{{ url('/news/updateStatus/Active',$news->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @elseif($news->status=='Schedule')
                          <a class="btn iconbtn schedulecls showhideCls btn-success" title="Schedule" href="{{ url('/news/edit',$news->id) }}"><i class="bi bi-calendar-date" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/news/edit',$news->id) }}"><i class="bi bi-pencil"></i></a>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete" class="btn delCls iconbtn btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    </td>
                  </tr>
                  @endforeach 
                 </tbody>
              </table>
         
      


             
              <!-- End Table with stripped rows -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->
@endsection