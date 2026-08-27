@extends('layouts.app')

@section('content')
@php
function formatKey($key) {
    // Replace underscores with spaces and capitalize each word
    $key = str_replace('_', ' ', $key);
    $key = ucwords($key);
    return $key;
}
@endphp
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Users By Country</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Users By Country</li>
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
                    <!-- <h5 class="card-title">Election</h5> -->
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="electiontabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                        
                    <th scope="col">Country</th>
                    <th scope="col">Views</th>
                  </tr>
                </thead>
                <tbody>
                  @php 
                  $i = 0;
                  @endphp
                  @foreach ($data['getUsersByCountry'] as $key=>$cntry)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                          
                    <td>{{ $cntry[0] }}</td>
                    <td>{{ $cntry[1] }}</td>
                            
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