@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>News</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/news') }}">News</a></li>
          <li class="breadcrumb-item active">Draft</li>
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
                <div class="card-body">
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
                    <a href="{{ url('/news') }}">All ({{$data['news']}})</a> | 
                    <a href="{{ url('/news/publish') }}">Publish ({{$data['publish']}})</a> | 
                    <a href="{{ url('/news/unpublish') }}">Unpublish ({{$data['unpublish']}})</a> | 
                    <a href="{{ url('/news/schedule') }}">Scheduled ({{$data['schedule']}})</a> |
                    <a href="{{ url('/news/draft') }}">Draft ({{count($data['draft'])}})</a> |
                    <a href="{{ url('/news/videos') }}">Videos  ({{$data['videos']}})</a>
                  </div>
                  </div>
                  <div class="col-lg-6">
                  @if(Auth::user()->roleID==1 || Auth::user()->roleID==2)
                  <form id="newsformsearch" class="" method="POST" action="{{ url('/news/draft') }}" enctype="multipart/form-data" novalidate>
                @csrf
                    <div class="row">
                    <div class="col-lg-4">
                  <select class="form-select" name="userID" id="validationCustom33">
                    <option value="">Select User</option>
                    @foreach ($data['users'] as $user)
                        <option value="{{ $user->id }}" @if($user->id==$data['userID']) {{ 'selected' }} @endif>{{ $user->name }}</option>
                    @endforeach
                  </select></div>
                  <div class="col-lg-5">
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
                @endif
                  </div>
                </div>
               
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">News Title</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['draft'] as $news)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td>{{ $news->title }}</td>
                    <td>{{ $news->authorName }}</td>
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
                        @elseif($news->status=='Draft')
                          <a class="btn iconbtn drftcls showhideCls btn-success" title="Draft" href="{{ url('/news/edit',$news->id) }}"><i class="bi bi-crop" aria-hidden="true"></i></a>
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