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
                  @endphp
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td><a href="http://localhost:3000/preview/{{ $news->slug }}" target="_blank">{{ $news->title }}</a></td>
                    <td>{{ $news->categoryNames }}</td>
                    <td>{{ $viewer }}</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($news->updated_at))->format('d-m-Y h:i A')}}
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