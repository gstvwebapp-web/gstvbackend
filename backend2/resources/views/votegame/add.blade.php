@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Vote Game</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/votegame') }}">Vote Game</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/votegame/store') }}" enctype="multipart/form-data" novalidate>
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
                  <label for="validationCustom03" class="form-label">Who Win</label>
                  <input type="text" name="whowin" class="form-control" id="validationCustom03" value="{{ old('whowin') }}" />
                </div>
                <div class="col-md-12">
                  <label for="validationCustom04" class="form-label">Total Vote for Win</label>
                  <input type="text" name="totalvote" class="form-control" id="validationCustom04" value="{{ old('totalvote') }}" />
                </div>
                <div class="col-md-12">
                  <label for="validationCustom05" class="form-label">First total Vote then add vote for Winner</label>
                  <input type="text" name="voteforwin" class="form-control" id="validationCustom05" value="{{ old('voteforwin') }}" />
                </div>
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