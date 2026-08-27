@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Multiple News Upload</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/news') }}">News</a></li>
          <li class="breadcrumb-item active">CSV File Upload</li>
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
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
             
            <div class="tab-content pt-2" id="myTabContent">
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/csvfileupload/csvupload') }}" enctype="multipart/form-data" novalidate>
                @csrf
                  <div class="col-md-12">
                    <label for="csvFile" class="col-form-label">CSV/Excel File Upload <span class="msgcls">( Only CSV, XLS, XLSX are allowed )</span></label>
                    <input class="form-control" type="file" name="csvfile" accept=".csv, .xls, .xlsx" id="csvFile">
                    @error('csvfile')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-12 pt-3">
                  <button class="btn btn-primary" type="submit">Submit</button>
                  </div>
                  
              </form>


           



            </div>
          </div>
        </div>
      </div>
    </section>
  </main><!-- End #main -->
 
@endsection
