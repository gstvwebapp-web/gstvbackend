@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Users</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/user') }}">Users</a></li>
          <li class="breadcrumb-item active">Profile</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
              <div class="card">
                  <div class="card-body">
                        <div class="row">
                          <div class="col-lg-4">
                            <h5 class="card-title">Name : {{ $data['user'][0]->name }}</h5>
                          </div>
                          <div class="col-lg-4">
                            <h5 class="card-title">Email : {{ $data['user'][0]->email }}</h5>
                          </div>
                          <div class="col-lg-4">
                            <h5 class="card-title">Role : {{ $data['user'][0]->title }}</h5>
                          </div>
                        </div>
                        <form action="{{ url()->current() }}" method="GET">
                          <div class="row mb-4">
                              <div class="col-lg-4 d-flex align-items-center">
                                  <label for="userstart_date" class="me-2" style="width:115px;">Start Date:</label>
                                  <input type="date" id="userstart_date" name="start_date" class="form-control" placeholder="Start Date" value="{{ request()->get('start_date') }}">
                              </div>
                              <div class="col-lg-4 d-flex align-items-center">
                                  <label for="userend_date" class="me-2" style="width:110px;">End Date:</label>
                                  <input type="date" id="userend_date" name="end_date" class="form-control" placeholder="End Date" value="{{ request()->get('end_date') }}">
                              </div>
                              <div class="col-lg-4 d-flex align-items-center">
                                  <input type="submit" value="Search"/>
                              </div>
                          </div>
                      </form>

                          <!-- Table with stripped rows -->
                        <table class="table table-bordered" id="userprofile">
                          <thead>
                            <tr>
                              <th scope="col">#</th>
                              <th scope="col">News Title</th>
                              <th scope="col">Category</th>
                              <th scope="col">View Count</th>
                              <th scope="col">Date</th>
                            </tr>
                          </thead>
                          <tbody>
                            @php
                            $i = 0;
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
                            @endphp
                            <tr>
                              <th scope="row">{{ ++$i }}</th>
                              @if (in_array('9', $catIDs))
                                <td><a href="{{ $newsUrl }}" target="_blank">{{ $news->title }}{{ $baseUrl }}</a></td>
                              @else
                                <td><a href="{{ $newsvideoUrl }}" target="_blank">{{ $news->title }}</a></td>
                              @endif
                              <td>{{ $news->categoryNames }}</td>
                              <td>{{ $viewer }}</td>
                              <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($news->created_at))->format('d-m-Y h:i A')}}
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