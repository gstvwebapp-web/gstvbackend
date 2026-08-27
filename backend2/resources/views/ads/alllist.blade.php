@extends('layouts.app')

@section('content')
<style>
.dataTable-table > tbody > tr > td:last-child {
    min-width: 127px;
}
.dataTable-table > tbody > tr > td:nth-child(3) {
    min-width: 215px;
    max-width: 215px;
}
.dataTable-table > tbody > tr > td:nth-child(2) {
    word-break: break-all !important;
}
</style>
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Ads</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Ads</li>
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
                    <h5 class="card-title">Ads</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/ads/add') }}">Create Ads</a>   
                  </div>
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="adstabledata">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th style="width:60% !important" scope="col">Data</th>
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