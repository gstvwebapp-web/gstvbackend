@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Epaper</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/epaper') }}">Epapers</a></li>
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
              <form class="row g-3 needs-validation" id="newsform" method="POST" action="{{ url('/epaper/store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="col-md-12">
                  <label for="validationCustom07" class="form-label">Select Type</label>
                  <select class="form-select" name="cattype" onchange="getselectcat(this.value)" id="validationCustom07" required>
                        <option selected  value="">Choose...</option>
                        <option value="Newspaper" >Newspaper</option>
                        <option value="Magazine" >Magazine</option>
                        <!-- <option value="Buddhiprakash" >Buddhiprakash</option> -->
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
                    @foreach ($data['epapercategory'] as $epaper)
                        <option value="{{ $epaper->id }}" @if($epaper->id==old('ecatID')) {{ 'selected' }} @endif>{{ $epaper->title }}</option>
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
                  <input type="text" name="etitle" class="form-control" id="validationCustom01" value="{{ old('etitle') }}" required />
                  <div class="invalid-feedback">
                    Please enter the title.
                  </div>
                  @error('etitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-12">
                  <label for="validationCustom02" class="col-form-label">
                      English Title
                      <span id="translateSpinner" class="ms-2 text-muted small" style="display:none;">
                          <span class="spinner-border spinner-border-sm" role="status"></span>
                          Translating...
                      </span>
                  </label>
                  <input type="text" name="engtitle" class="form-control" id="validationCustom02" value="{{ old('engtitle') }}" required />
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
                    <!-- <input class="form-control" type="file" name="icon" accept="image/*" id="formFile"> -->
                    @error('files')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="inputNumber" class="col-form-label">Upload PDF</label>
                  <input class="form-control" type="file" name="pdf" accept="pdf/*" id="formFile">
                    @error('pdf')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom05" class="form-label">Date</label>
                  <input type="date" name="newspaperdate" class="form-control" id="validationCustom05" onclick="this.showPicker()" value="{{ old('newspaperdate') }}" required />
                  <div class="invalid-feedback">
                    Please select the date.
                  </div>
                  @error('newspaperdate')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom04" class="col-form-label">Meta Title</label>
                  <input type="text" name="metatitle" class="form-control" value="{{ old('metatitle') }}" id="validationCustom04" required />
                  <div class="invalid-feedback">
                    Please enter the meta title.
                  </div>
                  @error('metatitle')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12">
                  <label for="validationCustom08" class="col-form-label">Meta Keyword</label>
                  <input type="text" name="metakeyword" value="{{ old('metakeyword') }}" class="form-control" id="validationCustom08" />
                </div>
                <div class="col-md-12">
                  <label for="inputPassword" class="col-form-label">Meta Description</label>
                  <textarea class="form-control" name="metadesc" style="height: 100px">{{ old('metadesc') }}</textarea>
                </div>
                <div class="col-md-12">
                  <label for="notification" class="col-form-label"></label>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="notification" id="notification" value="1">
                      <label class="form-check-label" for="notification">
                      Send Notification
                      </label>
                  </div>
              </div>
                
                <div class="col-12">
                  <button class="btn btn-primary" id="newsSubmit" type="submit">Submit</button>
                </div>
              </form><!-- End Custom Styled Validation -->

            </div>
          </div>

        

        </div>
      </div>
    </section>

     

  </main><!-- End #main -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const submitBtn = document.getElementById('newsSubmit');
      const form = document.getElementById('newsform');

      // Add event listener to the form submit event
      form.addEventListener('submit', function (event) {
        // Check if the form has the 'was-validated' class
        if (form.classList.contains('was-validated')) {
          if (form.checkValidity()) {
            submitBtn.disabled = true;
          } else {
            event.preventDefault(); // Prevent form submission
          }
        } else {
          form.classList.add('was-validated');
        }
      });
    });
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
// ── Auto-translate Gujarati title → English Title + Auto Tags ─────────────
(function () {
    var titleInput   = document.getElementById('validationCustom01');
    var engInput     = document.getElementById('validationCustom02');
    var spinner      = document.getElementById('translateSpinner');
    var translateUrl = '{{ url("/news/translateTitle") }}';
    var debounceTimer;

    if (!titleInput || !engInput) return;

    titleInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var val = this.value.trim();
        if (!val) return;

        debounceTimer = setTimeout(function () {
            if (spinner) spinner.style.display = 'inline';

            fetch(translateUrl + '?text=' + encodeURIComponent(val))
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.translated) {
                        engInput.value = res.translated;

                        // ── Auto-add tags from English title ──────────────
                        autoAddTagsFromTitle(res.translated);

                        // ── Auto-fill related news from English title ─────
                        autoFillRelatedNews(res.translated);
                    }
                })
                .catch(function (err) {
                    console.warn('Auto-translate failed:', err);
                })
                .finally(function () {
                    if (spinner) spinner.style.display = 'none';
                });
        }, 700);
    });
  })();
</script>
@endsection