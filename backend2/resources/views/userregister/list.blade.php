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
                </div>
              
               
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone no</th>
                    <th scope="col">Total Coin</th>
                    <th scope="col">Spent Coin</th>
                    <th scope="col">Remaining Coin</th>
                    <th scope="col">status</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['user'] as $user)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td>{{ $user->firstname.' '.$user->lastname }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->mobile }}</td>
                    <td>{{ $user->total_points }}</td>
                    <td>{{ $user->spent_points }}</td>
                    <td>{{ $user->remain_points }}</td>
                    <td>
                      @if ($user->plan_status === 'Active')
                          Subscribe
                      @elseif ($user->plan_status === 'Inactive')
                          Expire
                      @else
                          <!-- Display nothing -->
                      @endif
                   </td>
                    <td>
                        <!-- @if($user->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/frontend-users/updateStatus/Inactive',$user->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/frontend-users/updateStatus/Active',$user->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif -->
                        <a class="btn iconbtn viewCls btn-primary" title="View" href="{{ url('/frontend-users/view',$user->id) }}"><i class="bi bi-view-list"></i></a>
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