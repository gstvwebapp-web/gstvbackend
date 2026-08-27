@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Export Today Report</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">View Export</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
      <div class="col-lg-12">

            <div class="card">
                <div class="card-body pt-3">

                 <!-- Table with stripped rows -->
              <table class="table table-bordered" id="viewexporttabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">News Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">View Count</th>
                    <th scope="col">Date</th>
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