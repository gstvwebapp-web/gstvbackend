@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Frontend Users</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Frontend Users</li>
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
                    <h5 class="card-title">Frontend Users</h5>
                  </div>
                  <div class="col-lg-6 pt-3">                   
                    
                  </div>
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['user'] as $user)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td><a href="{{ url('/frontendusers/userdetail',$user->id) }}">{{ $user->firstname.' '.$user->lastname }}</a></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->title }}</td>
                    <td>
                      @if($user->status=='Active')
                        <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/frontendusers/updateStatus/Inactive',$user->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                      @else
                        <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/frontendusers/updateStatus/Active',$user->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                      @endif
                      <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/frontendusers/edit',$user->id) }}"><i class="bi bi-pencil"></i></a>
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