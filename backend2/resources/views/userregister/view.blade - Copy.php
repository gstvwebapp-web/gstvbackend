@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>{{ $data['user']->firstname }}</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">{{ $data['user']->firstname }} Profile</li>
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
                    <h5 class="card-title">Point List</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/frontend-users') }}">Back Frontend Users</a>   
                  </div>
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Duration</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  print_r($data['userduration']);
                  @endphp
                  @foreach ($data['userduration'] as $user)
                  @php 
                  $seconds =  $user->total_duration ;
                  $minutes = floor($seconds / 60);
                  $remainingSeconds = $seconds % 60;

                  $hours = floor($minutes / 60);
                  $remainingMinutes = $minutes % 60;
                  $duration =  "$hours hours, $remainingMinutes minutes, and $remainingSeconds seconds.\n";
                  @endphp
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td>{{ date("d-m-Y",strtotime($user->date)) }}</td>
                    <td>{{ $duration }}</td> 
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