@extends('layouts.app')

@section('content')
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
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Data</th>
                    <th scope="col">Date </th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['ads'] as $ads)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td>{{ $ads->adsdata }}</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($ads->created_at))->format('d-m-Y h:i A')}}</td>
                    <td>
                      <form action="{{ url('/ads/destroy',$ads->id) }}" method="POST">
                          @csrf
                          @method('DELETE')
                          @if($ads->status=='1')
                            <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/ads/updateStatus/0',$ads->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                          @else
                            <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/ads/updateStatus/1',$ads->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                          @endif
                          <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/ads/edit',$ads->id) }}"><i class="bi bi-pencil"></i></a>
                      </form>
                    </td>
                  </tr>
                  @endforeach 
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