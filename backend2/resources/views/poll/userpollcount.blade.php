@extends('layouts.app')

@section('content')
<main id="main" class="main">
<style>
  .dt-buttons { display:none; }
  </style>
    <div class="pagetitle">
      <h1>Draw Polls</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Draw Polls</li>
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
                    <h5 class="card-title">Draw Polls</h5>
                  </div>
                  <div class="col-lg-6">
                    <button id="exportPollUsers" class="btn btn-success" style="float:right; margin:10px;">Export CSV</button>
                  </div>
                  <div class="col-lg-12">
                    <div class="row mb-3">
    <div class="col-md-3">
        <label for="startDate">Start Date</label>
        <input type="date" id="startDate" class="form-control">
    </div>

    <div class="col-md-3">
        <label for="endDate">End Date</label>
        <input type="date" id="endDate" class="form-control">
    </div>

    <div class="col-md-2">
        <label>&nbsp;</label>
        <button id="filterBtn" class="btn btn-primary w-100">Filter</button>
    </div>

    <div class="col-md-2">
        <label>&nbsp;</label>
        <button id="resetBtn" class="btn btn-secondary w-100">Reset</button>
    </div>
</div>
                  </div>
                </div>
              <!-- Table with stripped rows -->
              <table class="table table-bordered"  id="userpollcountfetchData">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Author</th>
                    <th scope="col">Poll Count</th>
                    <th scope="col">Poll Point</th>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#exportPollUsers').on('click', function () {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();  
         var bsurl = "{{ url('/') }}";
          window.location.href = bsurl + "/poll/exportUserPollpoint?startDate=" + startDate + "&endDate=" + endDate;
      });
    });
  </script>
@endsection