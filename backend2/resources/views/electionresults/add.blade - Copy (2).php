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
    <th width="20%">Party Name</th>
    <th width="20%">Seat Lead</th>
    <th width="20%">Seat Win</th>
    <th width="20%">Color Code</th>
    <th width="20%">Delete</th>
  </tr>
  <tr>
            <td><input type="hidden" name="uniqid[]" value="<?php echo uniqid(); ?>"/><input class="form-control" type="text" name="partyname[]" placeholder="Party Name" /></td>
            <td><input class="form-control" type="text" name="partylead[]" placeholder="Seat Lead" /></td>
            <td><input class="form-control" type="text" name="partyseat[]" placeholder="Seat Win" /></td>
            <td><input class="form-control" type="color" name="partycolor[]" value="#000000" /></td>
            <td>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addChildRow(this)">
                    <i class="bi bi-plus"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr> 
</table>

</div> </div>
<script>
function addChildRow(button) {
    // Find the current parent row
    let currentRow = button.closest("tr");
    let firstInputValue = currentRow.querySelector("td:first-child input").value; // Get the value of the first input in the first <td>

    // Find the table and the parent row
    let table = document.getElementById("myTable");
    let parentRowIndex = currentRow.rowIndex; // Get the parent row index

    let newRow = table.insertRow(currentRow.rowIndex + 1); // Insert directly below the parent row

    // Add cells for the child row
    let cell1 = newRow.insertCell(0);
    let cell2 = newRow.insertCell(1);
    let cell3 = newRow.insertCell(2);
    let cell4 = newRow.insertCell(3);
    let cell5 = newRow.insertCell(4);

    // Set content for child row
    cell1.innerHTML = `<input type="hidden" name="parentuniqid[]" value="${firstInputValue}" /><input class="form-control" type="text" name="child_partyname[]" placeholder="Child Party Name" />`;
    cell2.innerHTML = `<input class="form-control" type="text" name="child_partylead[]" placeholder="Child Party Lead" />`;
    cell3.innerHTML = `<input class="form-control" type="text" name="child_partyseat[]" placeholder="Child Party Seat" />`;
    cell4.innerHTML = `<input class="form-control" type="color" name="child_partycolor[]" value="#000000" />`;

    // Add a delete button for the child row
    cell5.innerHTML = `
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
}

function deleteRow(button) {
    // Delete the row containing the button
    let row = button.closest("tr");
    row.parentNode.removeChild(row);
}

function addParentRow() {
    let table = document.getElementById("myTable");
 
    // Generate a unique ID using Date.now() and random
    let uniqueId = 'id_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    // Create a new parent row at the end of the table
    let newRow = table.insertRow(-1);
    

    // Add cells for the parent row
    let cell1 = newRow.insertCell(0);
    let cell2 = newRow.insertCell(1);
    let cell3 = newRow.insertCell(2);
    let cell4 = newRow.insertCell(3);
    let cell5 = newRow.insertCell(4);

    // Set content for the parent row
    cell1.innerHTML = `<input type="hidden" name="uniqid[]" value="${uniqueId}"/><input class="form-control" type="text" name="partyname[]" placeholder="Party Name" />`;
    cell2.innerHTML = `<input class="form-control" type="text" name="partylead[]" placeholder="Seat Lead" />`;
    cell3.innerHTML = `<input class="form-control" type="text" name="partyseat[]" placeholder="Seat Win" />`;
    cell4.innerHTML = `<input class="form-control" type="color" name="partycolor[]" value="#000000" />`;

    // Add only the "Add Child" button in the parent row and a delete button
    cell5.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" onclick="addChildRow(this)" style="float: left; margin-right: 10px;">
            <i class="bi bi-plus"></i>
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
}


</script>
              <div class="row">
                <div class="col-lg-6"><button type="button" id="addRowBtn" onclick="addParentRow()" class="btn float-right iconbtn  btn-success"><i class="bi bi-plus"></i> Add Row</button></div>
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