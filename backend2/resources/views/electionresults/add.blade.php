@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Election Results</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/electionresults') }}">Election Results</a></li>
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
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/electionresults/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                
                <div class="row">
                  <div class="col-lg-3">
                    <label for="validationCustom01" class="form-label">Select State</label>
                    <select class="form-select" name="stateId" id="validationCustom01" required>
                      <option value="">Choose...</option>
                      @foreach ($data['state'] as $state)
                          <option value="{{ $state->id }}" @if($state->id==old('stateId')) {{ 'selected' }} @endif>{{ $state->name }}</option>
                      @endforeach
                    </select>
                    <div class="invalid-feedback">
                      Please select the state.
                    </div>
                    @error('stateId')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-lg-3">
                    <label for="validationCustom02" class="form-label">Election Title</label>
                    <input type="text" class="form-control" id="validationCustom02" name="electiontitle" value="" required />
                    <div class="invalid-feedback">
                      Please enter election title.
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <label for="validationCustom03" class="form-label">Total Seats</label>
                    <input type="text" class="form-control" id="validationCustom03" name="totalseats" value="" required />
                    <div class="invalid-feedback">
                      Please enter total seats.
                    </div>
                  </div>
                </div>
                <div class="row">
                <div class="col-lg-12 pt-2">
                <table class="datatable table table-bordered dataTable-table" id="myTable">
  <tr>
    <th>Party Name</th>
    <th>Seat Lead</th>
    <th>Seat Win</th>
    <th>Color Code</th>
    <th>Delete</th>
  </tr>
  <tr>
    <td><input type="text" class="form-control" name="partyname[]" value="" required /></td>
    <td><input type="text" class="form-control" name="partylead[]" value="" required /></td>
    <td><input type="text" class="form-control" name="partyseat[]" value="" required /></td>
    <td><input type="color" class="form-control" name="partycolor[]" value="" required /></td>
    <td>&nbsp;</td>
  </tr> 
</table>

</div> </div>
<script>
  function addRow() {
    let table = document.getElementById("myTable");
    let newRow = table.insertRow(-1);
    let cell1 = newRow.insertCell(0);
    let cell2 = newRow.insertCell(1);
    let cell3 = newRow.insertCell(2);
    let cell4 = newRow.insertCell(3);
    let cell5 = newRow.insertCell(4);
    cell1.innerHTML = '<input class="form-control" type="text" name="partyname[]" value="" />';
    cell2.innerHTML = '<input class="form-control" type="text" name="partylead[]" value="" />';
    cell3.innerHTML = '<input class="form-control" type="text" name="partyseat[]" value="" />';
    cell4.innerHTML = '<input class="form-control" type="text" name="partycolor[]" value="" />';
    cell5.innerHTML = '<button type="button" onclick="deleteRow(this)" title="Delete" class="btn iconbtn delCls btn-danger"><i class="bi bi-trash"></i></button>';
  }
  function deleteRow(button) {
    let row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);
  }
</script>
              <div class="row">
                <div class="col-lg-6"><button type="button" id="addRowBtn" onclick="addRow()" class="btn float-right iconbtn  btn-success"><i class="bi bi-plus"></i> Add Row</button></div>
                <div class="col-lg-6">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div></div>
              </form><!-- End Custom Styled Validation -->

            </div>
          </div>

        

        </div>
      </div>
    </section>

     

  </main><!-- End #main -->
@endsection