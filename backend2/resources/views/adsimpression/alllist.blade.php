@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Ads Report</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Ads Export</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-3">

              <!-- Date Range Inputs -->
              <div class="row mb-3">
              <div class="col-sm-6"></div>
                <div class="col-sm-2">
                    <label for="startDate" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" id="startDate" name="startDate">
                </div>
                <div class="col-sm-2">
                    <label for="endDate" class="form-label">End Date:</label>
                    <input type="date" class="form-control" id="endDate" name="endDate">
                </div>
                <div class="col-md-2" style="float:right;text-align:right;margin-top: 35px;">
                  <a href="{{ url('/adsimpression/graph') }}" style="background-color: #0d6efd;padding: 10px;color: #fff;">Show Graph</a>
                </div>
            </div>
            <!-- End Date Range Inputs -->

              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="adsimpressiontabledata" width="100%">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Title</th>
                    <th scope="col">Impressions</th>
                    <th scope="col">Clicks</th>
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
 
