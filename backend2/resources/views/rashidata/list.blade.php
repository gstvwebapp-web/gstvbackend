@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Categories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Categories</li>
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
                    <h5 class="card-title">Categories</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/category/add') }}">Create Category</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered" id="categorytabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Icon</th>
                    <th scope="col">Category Title</th>
                    <th scope="col">Parent Category Title</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['category'] as $category)
                  <tr data-id="{{ $category->id }}">
                    <th scope="row">{{ ++$i }}</th>
                    <td>
                      @if($category->icon)
                        @php
                          $imgurl = config('app.backendnewsurl').'/public/uploads/category/icon/'.$category->icon;
                        @endphp
                      <img src="{{$imgurl}}" style="width:30px;"/>
                      @endif
                    </td>
                    <td>{{ $category->category_name }}</td>
                    <td>{{ $category->parent_category_name }}</td>
                    <td>{{ $category->authorName }}</td>
                    
                    <td>
                    <form action="{{ url('/category/destroy',$category->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($category->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/category/updateStatus/Inactive',$category->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/category/updateStatus/Active',$category->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/category/edit',$category->id) }}"><i class="bi bi-pencil"></i></a>
                        <input type="hidden" name="oldImg" value="{{ $category->icon }}" />
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