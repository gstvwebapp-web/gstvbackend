@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Epapers</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Epapers</li>
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
                    <h5 class="card-title">Epapers</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/epaper/add') }}">Create Epaper</a>   
                  </div>
                </div>
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['epaper'] as $epaper)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td><a target="_blank" href="{{ config('app.front_url') }}/epaper/{{ $epaper->categoryNames }}/{{ \Carbon\Carbon::parse($epaper->newspaperdate)->format('d-m-Y') }}">{{ $epaper->etitle }}</a></td>
                    <td>{{ $epaper->categoryNames}}</td>
                    <td>
                    <form action="{{ url('/epaper/destroy',$epaper->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($epaper->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/epaper/updateStatus/Inactive',$epaper->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/epaper/updateStatus/Active',$epaper->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/epaper/edit',$epaper->id) }}"><i class="bi bi-pencil"></i></a>
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