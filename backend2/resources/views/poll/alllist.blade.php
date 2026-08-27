@extends('layouts.app')

@section('content')
<main id="main" class="main">
<style>
  tr.winner-row {
      background: #800d00 !important;
      color: #fff;
  }
</style>
    <div class="pagetitle">
      <h1>Polls</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Polls</li>
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
                    <h5 class="card-title">Polls</h5>
                  </div>
                  <div class="col-lg-6 pt-3 "> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/poll/add') }}">Create Poll</a>   
                    <a class="btn  addCls btn-success" href="{{ url('/poll/userpollcount') }}" style="float: right;margin-right: 10px;">User Draw Poll count</a>                 </div>
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="table table-bordered"  id="polltabledata">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Question</th>
                    <th scope="col">Result</th>
                    <th scope="col">Voter / Viewer</th>
                    <th scope="col">Created Date</th>
                    <th scope="col">Author</th>
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