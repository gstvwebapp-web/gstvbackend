@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Star</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Election Star</li>
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
                    <h5 class="card-title">Election Star</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/electionresults/addelectionstar') }}">Create Election Star</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">City</th>
                    <th scope="col">Party Type</th>
                    <th scope="col">Party Status</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['electionstar'] as $estar)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td><img src="{{ $estar->image }}" style="height:50px;"/></td>
                    <td>{{ $estar->title }}</td>
                    <td>{{ $estar->city}}</td>
                    <td>{{ $estar->partytype}}</td>
                    <td>{{ $estar->party_status}}</td>
                    <td>
                    <form action="{{ url('/electionresults/destroyelectionstar',$estar->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($estar->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/electionresults/updateelectionstarStatus/Inactive',$estar->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/electionresults/updateelectionstarStatus/Active',$estar->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif                                                         
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/electionresults/editelectionstar',$estar->id) }}"><i class="bi bi-pencil"></i></a>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete" class="btn iconbtn delCls btn-danger"><i class="bi bi-trash"></i></button>
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