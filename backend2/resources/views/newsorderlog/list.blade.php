@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>News Order Logs</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">News Order Logs</li>
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
                  <div class="col-lg-9">
                    <!-- <h5 class="card-title">Rashi</h5> -->
                  </div>
                  <div class="col-lg-3 pt-3" style="display:block ruby"> 
                    <!-- Select Date  -->
                     <!-- <input type="date" id="logdate" style="width:auto;" class="form-control" value="{{ date('Y-m-d') }}"> -->
                  </div>
                </div>
 
 

                <table class="table table-bordered" id="newsordertabledata">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>View</th>
                        </tr>
                    </thead>
                </table>


            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->
  <style>
    button.dt-button.buttons-excel.buttons-html5.btn.btn-primary,#newsordertabledata_filter{
      visibility: hidden;
    }
  </style>
@endsection