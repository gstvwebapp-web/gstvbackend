@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Payment History</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Payment History</li>
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
                  
                  <div class="col-lg-12 pt-3">                   
                      <!-- <div class="row mb-3">
                          <div class="col-md-3">
                              <label>Start Date</label>
                              <input type="date" id="start_date" class="form-control">
                          </div>
                          <div class="col-md-3">
                              <label>End Date</label>
                              <input type="date" id="end_date" class="form-control">
                          </div>
                          <div class="col-md-3 align-self-end">
                              <button id="filterBtncoin" class="btn btn-primary">Filter</button>
                              <button id="resetBtncoin" class="btn btn-secondary">Reset</button>
                          </div>
                      </div> -->
                   
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="paymenttabledata" style="width:100%;">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Plan</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Order Id</th>
                    <th scope="col">Payment Id</th>
                    <th scope="col">Expire Date</th>
                    <!-- <th scope="col">Actions</th> -->
                    <!-- <th scope="col">Total Coin</th> -->
                    <!-- <th scope="col">Actions</th> -->
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