@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Epaper</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/epaper') }}">Epaper</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->
      <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation" method="POST" action="{{ url('/epaper/update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="eid" value="{{ $data['epaper']->id }}" />
                <div class="col-md-12">
                  <label for="validationCustom07" class="form-label">Select Type</label>
                  <select class="form-select" name="cattype" onchange="getselectcat(this.value)" id="validationCustom07" required>
                      <option selected value="">Choose...</option>
                      <option value="Newspaper"  @if($data['epapertitle'] == "Newspaper") {{ 'selected' }} @endif>Newspaper</option>
                      <option value="Magazine" @if($data['epapertitle'] == "Magazine") {{ 'selected' }} @endif)>Magazine</option>
                      <!-- <option value="Buddhiprakash" @if($data['epapertitle'] == "Buddhiprakash") {{ 'selected' }} @endif)>Buddhiprakash</option> -->
                  </select>
                  <div class="invalid-feedback">
                    Please Select Type.
                  </div>
                  @error('cattype')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom03" class="form-label">Category</label>
                  <select class="form-select" name="ecatID" id="validationCustom03" required>
                    <option selected  value="">Choose...</option>
                    @foreach ($data['epapercategory'] as $epapercat)
                        <option value="{{ $epapercat->id }}" @if($epapercat->id==$data['epaper']->ecatID) {{ 'selected' }} @endif>{{ $epapercat->title }}</option>
                    @endforeach
                  </select>
                  <div class="invalid-feedback">
                    Please select the category.
                  </div>
                  @error('ecatID')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom01" class="form-label">Title</label>
                  <input type="text" name="etitle" class="form-control" id="validationCustom01" value="{{ $data['epaper']->etitle }}" required />
                  <div class="invalid-feedback">
                    Please enter the title.
                  </div>
                  @error('etitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom02" class="form-label">English Title</label>
                  <input type="text" name="engtitle" class="form-control" id="validationCustom02" value="{{ $data['epaper']->engtitle }}" required />
                  <div class="invalid-feedback">
                    Please enter the English title.
                  </div>
                  @error('engtitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Select Multiple Images</label>
                  <input class="form-control" type="file" name="files[]" accept="image/*" multiple onchange="handleFileEpaperSelect(event)" id="formFile">
                   @error('files')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    @php
                      $decodedData = json_decode($data['epaper']->Story_data);
                      $i = 1;
                    @endphp
                   @if(empty($data['epaper']->pdf))
                   @isset($decodedData)
                   @if(count($decodedData) > 0)
                    <div class="file-container" id="fileContainer">
                    @foreach ($decodedData as $webdata)
                    <div class="col-md-2 file-item" id="rows{{$i}}">
                     @php
                        $imagePath = $webdata->image;
                        $filenameWithoutExt = pathinfo($imagePath, PATHINFO_FILENAME);
                        $extension = pathinfo($imagePath, PATHINFO_EXTENSION); // Get the file extension
                        $newImagePath = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . $filenameWithoutExt . '_small.' . $extension;
                    @endphp
                      <img src="{{ $webdata->image}}">
                      <input type="hidden" name="exitsfiles[]" value="{{ $webdata->image }}">
                      <button type="button" class="epaperbtn" onclick="removeFileEpaperSelect('rows{{$i}}')"><i class="bi bi-x-circle"></i></button>
                    </div>
                    @php
                      $i++;
                    @endphp
                    @endforeach
                    </div>
                   @endif
    @endisset
                  	@endif
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Upload PDF</label>
                  <input class="form-control" type="file" name="pdf" accept="pdf/*" id="formFile">
                    @error('pdf')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    @if(!empty($data['epaper']->pdf))
                    <div id="removepdf" class="file-item" style="float:left;width:100%">
                      <a href="{{ $data['epaper']->pdf }}" target="_blank">View PDF</a>
                      <button type="button" class="epaperbtn" onclick="removePDFEpaperSelect('{{ $data['epaper']->id }}')" style="position: unset;">
                        <i class="bi bi-x-circle" style="vertical-align:middle;"></i>
                      </button>
                    </div>
                    @foreach ($decodedData as $webdata)
                       <input type="hidden" name="exitsfiles[]" value="{{ $webdata->image }}">
                      <div class="col-md-2 file-item removepdfimages" id="rows{{$i}}">
                        <img src="{{ $webdata->image}}">
                      </div>
                      @php
                        $i++;
                      @endphp
                    @endforeach
                    @endif
                    <input type="hidden" name="oldpdf" id="oldpdf" value="{{ $data['epaper']->pdf }}"/>
                </div>
                <div class="col-md-12">
                
                  <label for="validationCustom05" class="form-label">Date</label>
                  <input type="date" name="newspaperdate" class="form-control" onclick="this.showPicker()" id="validationCustom05" value="{{ \Carbon\Carbon::parse($data['epaper']->newspaperdate)->format('Y-m-d') }}" required />
                  <div class="invalid-feedback">
                    Please select the date.
                  </div>
                  @error('newspaperdate')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom04" class="col-form-label">Meta Title</label>
                  <input type="text" name="metatitle" class="form-control" value="{{ $data['epaper']->metatitle }}" id="validationCustom04" required />
                  <div class="invalid-feedback">
                    Please enter the meta title.
                  </div>
                  @error('metatitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                  <input type="text" name="metakeyword" value="{{ $data['epaper']->metakeyword }}" class="form-control" id="validationCustom08" />
                </div>
                <div class="col-md-12">
                  <label for="inputPassword" class="col-form-label">Meta Description</label>
                  <textarea class="form-control" name="metadesc" style="height: 100px">{{ $data['epaper']->metadesc }}</textarea>
                </div>
                
                <div class="col-md-12">
                    <label for="notification" class="col-form-label"></label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notification" id="notification" value="1"
                              @if($data['epaper']->notification == 1) {{ 'checked' }} @endif>
                        <label class="form-check-label" for="notification">
                            Send Notification
                        </label>
                    </div>
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
function removePDFEpaperSelect(id){

   $('input[name="exitsfiles[]"]').each(function() {
         $(this).val(null); // Set the value to null
   });
   $.ajax({
      url: "{{ url('/epaper/deletepdfepaper/') }}/", 
        type: "GET",
        data: { id: id },
        success: function(response) { 
            $("#oldpdf").val('');
            $("#removepdf").hide();
            $(".removepdfimages").hide();
        },
        error: function(xhr) {
            console.error("An error occurred:", xhr);
        }
    });
}
</script>
@endsection