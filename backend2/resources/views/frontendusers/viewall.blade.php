@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Export Today Report</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">View Export</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
      <div class="col-lg-12">

            <div class="card">
                <div class="card-body pt-3">

                 <!-- Table with stripped rows -->
              <table class="table table-bordered" id="userprofile">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">News Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">View Count</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data as $news)
                  @php
                    $viewer = 0;
                    if($news->viewer!=NULL || $news->viewer!=0) {
                      $viewer = $news->viewer; 
                    }
                  @endphp
                  <!-- <tr><td colspan="6">{{ $news->authorName }}</td></tr> -->
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td><b>{{ $news->authorName }}</b></td>
                    <td>{{ $news->title }}</td>
                    <td>{{ $news->categoryNames }}</td>
                    <td>{{ $viewer }}</td>                   
                    <td>{{ \Carbon\Carbon::createFromTimestamp(strtotime($news->updated_at))->format('d-m-Y h:i A')}}
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