@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Gift Reedem</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Gift Reedem</li>
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
                  <div class="col-lg-6">
                    <h5 class="card-title">Gift Reedem</h5>
                  </div>
                  <!-- <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/gift/add') }}">Create Gift</a>   
                  </div> -->
                </div>
              
              <!-- Table with stripped rows -->
              <table class="table table-bordered" id="giftreedemtabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th>Gift Title</th>
                    <th scope="col">Gift Image</th>
                    <th scope="col">User</th>
                    <th scope="col">Code</th>
                    <th scope="col">Spent Coins</th>
                    <th scope="col">Address</th>
                    <th scope="col">Mobile</th>
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