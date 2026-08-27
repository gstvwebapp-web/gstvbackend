@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Web Stories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Web Stories</li>
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
      @if(session('status'))
        <div class="col-lg-12">
          <div class="alert alert-success">
              {{ session('status') }}
          </div>
        </div>
      @endif
       
      <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
            <div class="row">
                  <div class="col-lg-6">
                    <h5 class="card-title">Web Stories</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/webstory/add') }}">Create Web Story</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered" id="webstorytabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">View Count</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['webstory'] as $web)
                  @php
                    $viewer = 0;
                    if($web->viewer!=NULL || $web->viewer!=0) {
                      $viewer = $web->viewer; 
                    }
                    $baseUrl = config('app.front_url');
                    $newsUrl = $baseUrl."/web-stories/".$web->slug;
                  @endphp
                  <tr data-id="{{ $web->id }}" data-top="{{$web->userID}}">
                    <th scope="row">{{ ++$i }}</th>
                    <td><a href="{{ $newsUrl }}" target="_blank">{{ $web->title }}</a></td>
                    <td>{{ $viewer }}</td>
                    <td>{{ $web->authorName }}</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($web->updated_at))->format('d-m-Y h:i A')}}</td>
                    <td>
                    <form action="{{ url('/webstory/destroy',$web->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($web->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/webstory/updateStatus/Inactive',$web->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/webstory/updateStatus/Active',$web->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/webstory/edit',$web->id) }}"><i class="bi bi-pencil"></i></a>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this record?')" title="delete" class="btn iconbtn delCls btn-danger"><i class="bi bi-trash"></i></button>
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