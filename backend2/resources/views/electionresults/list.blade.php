@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Results</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Election Results</li>
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
                    <h5 class="card-title">Election Results</h5>
                  </div>
                  <div class="col-lg-6 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/electionresults/add') }}">Create</a>  
                    <a class="btn float-end addCls btn-success" href="{{ url('/electionresults/electionstar') }}" style="margin-right:10px;">Election Start</a>   
                  </div>
                </div>
              
              <!-- Table with stripped rows -->
              <table class="datatable table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">State</th>
                    <th scope="col">Election Title</th>
                    <th scope="col">Total Seats</th>
                    <th scope="col">Results</th>
                    <th scope="col">Author Name</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $i = 0;
                  @endphp
                  @foreach ($data['electionresults'] as $electionresults)
                  @php
                    $partyData = json_decode($electionresults->party, true);
                  @endphp
                  <tr>
                    <th scope="row">{{ ++$i }}</th>
                    <td>{{ $electionresults->stateName }}</td>
                    <td>{{ $electionresults->electiontitle }}</td>
                    <td>{{ $electionresults->totalseats }}</td>
                    <td>
                    @if(isset($partyData['partyname']) && isset($partyData['partyseat']))
                        @foreach($partyData['partyname'] as $index => $party)
                            {{ $party }} : {{ $partyData['partyseat'][$index] }}<br/>
                        @endforeach
                    @endif
                    </td>
                    <td>{{ $electionresults->authorName }}</td>                    
                    <td>
                    <form action="{{ url('/electionresults/destroy',$electionresults->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        @if($electionresults->status=='Active')
                          <a class="btn iconbtn showhideCls btn-success" title="Show" href="{{ url('/electionresults/updateStatus/Inactive',$electionresults->id) }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        @else
                          <a class="btn iconbtn showhideCls btn-success" title="Hidden" href="{{ url('/electionresults/updateStatus/Active',$electionresults->id) }}"><i class="bi bi-eye-slash" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn iconbtn editCls btn-primary" title="Edit" href="{{ url('/electionresults/edit',$electionresults->id) }}"><i class="bi bi-pencil"></i></a>
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