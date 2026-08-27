@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Magazine Review</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Magazine Review</li>
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
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Magazine</label>
                        <select id="magazineFilter" class="form-control">
                            <option value="">All Magazine</option>
                            @foreach($magazines as $mag)
                                <option value="{{ $mag }}">{{ $mag }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" id="startDate" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" id="endDate" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                      <button id="filtermagazineBtn" class="btn btn-primary w-50">Filter</button>
                      <button id="resetmagazineBtn" class="btn btn-secondary w-50">Reset</button>
                    </div>

                    <!-- <div class="col-md-2 d-flex align-items-end">
                        <button id="filtermagazineBtn" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button id="resetmagazineBtn" class="btn btn-primary w-100">Reset</button>
                    </div> -->
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="magazinereviewtabledata" style="width:100%;">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Phone</th>
                    <th scope="col">City</th>
                    <th scope="col">Magazine</th>
                    <th scope="col">Message</th>
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
  <style>
    #careertabledata_wrapper{
      overflow: auto;
    }
    #magazinereviewtabledata td{
     text-transform: capitalize;
    }
  </style>
@endsection