@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Gift</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Gift</li>
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
                    <h5 class="card-title">Gift</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/gift/add') }}">Create Gift</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered" id="categorytabledata">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Coins</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Expire Date</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['gift'] as $gift)
                  <tr data-id="{{ $gift->id }}">
                    <th scope="row">{{ ++$i }}</th>
                    <td>
                      @if($gift->giftimage)
                      <img src="{{$gift->giftimage }}" style="width:30px;"/>
                      @endif
                    </td>
                    <td>{{ $gift->gifttitle }}</td>
                    <td>{{ $gift->coins }}</td>
                    <td>{{ $gift->qty }}</td>
                    <td>{{ date('d-m-Y', strtotime($gift->expiredate)) }}</td>
                    <td>
                    <form action="{{ url('/gift/destroy',$gift->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($gift->status=='Active') 
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/gift/updateStatus/Inactive',$gift->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/gift/updateStatus/Active',$gift->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/gift/edit',$gift->id) }}"><i class="bi bi-pencil"></i></a>
                        <input type="hidden" name="oldImg" value="{{ $gift->giftimage }}" />
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