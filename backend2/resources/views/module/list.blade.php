@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Modules</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Modules</li>
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
                    <h5 class="card-title">Modules</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/module/add') }}">Create Module</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Module URL</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['module'] as $module)
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    
                    <td>{{ $module->title }}</td>
                    <td>{{ $module->moduleLink }}</td>
                    <td>
                    <form action="{{ url('/module/destroy',$module->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                      <?php /* @if($module->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/module/updateStatus/Inactive',$module->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/module/updateStatus/Active',$module->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif*/ ?>
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/module/edit',$module->id) }}"><i class="bi bi-pencil"></i></a>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this record?')" title="delete" class="btn iconbtn delCls btn-danger"><i class="bi bi-trash"></i></button>
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