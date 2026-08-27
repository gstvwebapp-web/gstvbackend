@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Poll</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/poll') }}">Polls</a></li>
          <li class="breadcrumb-item active">Add</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/poll/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Question</label>
                  <input type="text" name="question" class="form-control" id="validationCustom01" value="{{ old('question') }}" required>
                  <div class="invalid-feedback">
                    Please enter the Question.
                  </div>
                  @error('question')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Answer Options <span class="msgcls">( Multiple Answer add seperate by : )</span></label>
                  <input type="text" name="answerOption" class="form-control" id="validationCustom02" value="{{ old('answerOption') }}" required>
                  <div class="invalid-feedback">
                    Please enter the answer options.
                  </div>
                  @error('answerOption')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-12">
                  <label for="validationCustom04" class="form-label">Poll Close Message</label>
                  <input type="text" name="pollmsg" class="form-control" id="validationCustom04" value="પોલનો સમય સમાપ્ત થઈ ગયો છે. નવો પોલ ટૂંક સમયમાં શરુ થશે." />
                </div>
                @php
                date_default_timezone_set('Asia/Kolkata');
				$expiredate = date('Y-m-d', strtotime('+1 day')) . ' 19:00:00';
                @endphp
                <div class="col-md-12">
                  <label for="validationCustom05" class="form-label">Poll Expire Date/Time</label>
                  <input type="text" name="expiredate" class="form-control" id="validationCustom05" value="{{ $expiredate }}" />
                </div>
                
                <!--<div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Poll Detail</label>
                  <textarea class="form-control" name="pollDetail" style="height: 100px">{{ old('pollDetail') }}</textarea>
                </div>-->
                <div class="col-12">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div>
              </form><!-- End Custom Styled Validation -->

            </div>
          </div>

        

        </div>
      </div>
    </section>

     

  </main><!-- End #main -->
@endsection