@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Results</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Election Results</li>
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
                    <h5 class="card-title">Election Results</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/electionresults/add') }}">Create</a>  
                    <a class="btn float-end addCls btn-success" href="{{ url('/electionresults/electionstar') }}" style="margin-right:10px;">Election Start</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="electionresultstabledata">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">State</th>
                    <th scope="col">Election Title</th>
                    <th scope="col">Total Seats</th>
                    <th scope="col">Results</th>
                    <th scope="col">Author</th>
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