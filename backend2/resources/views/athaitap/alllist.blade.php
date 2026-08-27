@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
      <h1>Athai Tap</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Athai Tap</li>
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
       
      <div class="col-lg-12" style="overflow:auto;">
            <div class="card">
                <div class="card-body">
                <div class="row">
                  <div class="col-lg-6">
                    <!-- <h5 class="card-title">Frontend Users</h5> -->
                  </div>
                  <div class="col-lg-6 pt-3"> 
                  </div>
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="athaitaptabledata" style="width:100%;">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Days</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Image</th>
                    <th scope="col">Video</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
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