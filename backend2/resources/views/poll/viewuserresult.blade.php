@extends('layouts.app')

@section('content')
<style>
  .dt-buttons { display:none; }
  </style>
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Polls Result</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Polls Result</li>
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
                  <div class="col-lg-10">
                    <h5 class="card-title">{{$data['poll']->question}}</h5>
                  </div>
                  <div class="col-lg-2 pt-3"> 
                    <a class="btn float-end addCls btn-success" href="{{ url('/poll') }}">Back to Poll</a>  

                  </div>
                  
                </div>
                <div class="row">
                  <div class="col-lg-12">
                @if($data['winner'])  
                  <div id="winnerSection" style="margin-bottom:20px;">
                        <h4>🎉 Lucky Draw Winner 🎉</h4>
                        <div id="winnerResult">
                            <p style="color:gray;"><p><b>Name:</b> {{$data['winner']->authorName}}</p>
                                    <p><b>Mobile:</b> {{$data['winner']->mobile}}</p>
                                    <p><b>Answer:</b> {{$data['winner']->selectedAnswer}}</p>
                                    <p><b>Date:</b> {{$data['winner']->created_at}}</p></p>
                        </div>
                  </div>
                @else
                  <button class="btn btn-primary" id="drawWinnerBtn" data-pollid="{{$data['poll']->id}}">Draw Winner</button>
                  <div id="winnerResult" style="margin-top:20px;"></div>
                @endif
                  </div>
                  <button id="exportPollUsers" class="btn btn-success" style="width:auto; margin:10px;">Export CSV</button>
                </div>
              
               <input type="hidden" id="pollid" value="{{$data['poll']->id}}">
             
              <!-- Table with stripped rows -->
              <table class="table table-bordered"  id="pollusertabledata">
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Answer</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Created Date</th>
                  </tr>
                </thead>
                <tbody>
                  
                 </tbody>
              </table>
              <!-- End Table with stripped rows -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$('#exportPollUsers').on('click', function () {
    let pollid = $('#pollid').val();

    window.location.href = bsurl + "/poll/exportUserPoll?pollid=" + pollid;
});
$("#drawWinnerBtn").on("click", function() {
    let pollid = $(this).data("pollid");

    $.ajax({
        url: "{{ config('app.url') }}/poll/luckydraw",
        type: "POST",
        data: {
            pollid: pollid,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            if(response.status === 'success'){
                $("#winnerResult").html(`
                    <h4>🎉 Lucky Draw Winner 🎉</h4>
                    <p><b>Name:</b> ${response.winner.authorName}</p>
                    <p><b>Mobile:</b> ${response.winner.mobile}</p>
                    <p><b>Answer:</b> ${response.winner.selectedAnswer}</p>
                    <p><b>Vote Date:</b> ${response.winner.created_at}</p>
                `);
                $("#drawWinnerBtn").prop("disabled", true); // disable after winner chosen
            } else {
                $("#winnerResult").html(`<p style="color:red;">${response.message}</p>`);
            }
        },
        error: function(xhr){
            console.error(xhr.responseText);
            $("#winnerResult").html(`<p style="color:red;">Error: Could not run lucky draw</p>`);
        }
    });
});
</script>
@endsection