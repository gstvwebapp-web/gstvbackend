@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Upcoming List</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Upcoming List</li>
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
                    <!-- <h5 class="card-title">Rashi</h5> -->
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <!-- <a class="btn float-end addCls btn-success" href="{{ url('/rashidata/add') }}">Create Rashi</a>    -->
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="crickettabledata">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Series</th>
                    <th scope="col">Match Time</th>
                    <th scope="col">Team A</th>
                    <th scope="col">Team B</th>
                    <th scope="col">Venue</th>
                    <th scope="col">Match Type</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
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