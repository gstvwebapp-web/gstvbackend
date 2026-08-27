@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Start</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/electionresults/electionstar') }}">Election Start</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/electionresults/submitelectionstar') }}" enctype="multipart/form-data" novalidate>
                @csrf
                
                  <div class="col-md-12">
                      <label for="validationCustom07" class="form-label">Select State</label>
                      <select class="form-select" name="stateId" id="validationCustom01" required>
                        <option value="">Choose...</option>
                        @foreach ($data['state'] as $state)
                            <option value="{{ $state->id }}" @if($state->id==old('stateId')) {{ 'selected' }} @endif>{{ $state->name }}</option>
                        @endforeach
                      </select>
                      <div class="invalid-feedback">
                        Please Select status.
                      </div>
                      @error('party_status')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom07" class="form-label">Select status</label>
                  <select class="form-select" name="party_status" id="validationCustom07" required>
                        <option selected  value="">Choose...</option>
                        <option value="Won" >Won</option>
                        <option value="Lost" >Lost</option>
                  </select>
                  <div class="invalid-feedback">
                    Please Select status.
                  </div>
                  @error('party_status')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" id="validationCustom01" value="{{ old('title') }}" required />
                  <div class="invalid-feedback">
                    Please enter the title.
                  </div>
                  @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">City</label>
                  <input type="text" name="city" class="form-control" id="validationCustom01" value="{{ old('city') }}" required />
                  <div class="invalid-feedback">
                    Please enter the city.
                  </div>
                  @error('city')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">Party Type</label>
                  <input type="text" name="partytype" class="form-control" id="validationCustom02" value="{{ old('partytype') }}" required />
                  <div class="invalid-feedback">
                    Please enter the Party Type.
                  </div>
                  @error('partytype')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Select Image</label>
                  <input class="form-control" type="file" name="files" accept="image/*" onchange="handleFileEpaperSelect(event)" id="formFile">
                    <!-- <input class="form-control" type="file" name="icon" accept="image/*" id="formFile"> -->
                    @error('files')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
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
  <script>
function getselectcat(cattype) {
    $.ajax({
      url: "{{ url('/epaper/getepapercat/') }}/", 
        type: "GET",
        data: { cattype: cattype },
        success: function(response) {
            // Clear the current options in the ecatID dropdown
            let categoryDropdown = $('#validationCustom03');
            categoryDropdown.empty();
            categoryDropdown.append('<option selected value="">Choose...</option>');

            // Populate the dropdown with new options
            $.each(response, function(index, category) {
                categoryDropdown.append('<option value="' + category.id + '">' + category.title + '</option>');
            });
        },
        error: function(xhr) {
            console.error("An error occurred:", xhr);
        }
    });
}
</script>
@endsection