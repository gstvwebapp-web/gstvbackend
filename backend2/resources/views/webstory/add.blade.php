@extends('layouts.app')

@section('content')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Add Web Story</h1>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body pt-4">
        <form method="POST" action="{{ url('/webstory/store') }}" enctype="multipart/form-data">
          @csrf

          <div class="mb-3">
            <label for="validationCustom01" >Title</label>
            <input type="text" id="validationCustom01" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="validationCustom02" class="col-form-label">
                English Title
                <span id="translateSpinner" class="ms-2 text-muted small" style="display:none;">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    Translating...
                </span>
            </label>
            <input type="text" name="englishTitle" id="validationCustom02" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Add Multiple Images with Titles</label>
            <div id="imageInputs" class="row" style="margin-left:0px;margin-right:0px;"></div>
            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addImageField()">+ Add More</button>
          </div>

          <div class="mb-3">
            <label>Meta Keyword</label>
            <input type="text" name="metakeyword" class="form-control">
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="notification" id="notification" value="1">
            <label class="form-check-label" for="notification">Send Notification</label>
          </div>

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </section>
</main>

<!-- SortableJS (for drag-and-drop sorting) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
  let count = 0;

  function addImageField() {
    count++;
    const container = document.getElementById('imageInputs');
    const div = document.createElement('div');
    div.className = "col-md-6 image-boxcls file-item";
    div.id = 'row' + count;
    const baseUrl = "{{ config('app.url') }}";

    div.innerHTML = `
      <div class="me-2">
        <label class="drag-area-img" for="fileInput${count}">
          <img id="preview${count}" src="${baseUrl}/public/assets/img/previewimg.png" alt="preview" width="80" height="80">
          <input type="file" id="fileInput${count}" name="files[]" accept="image/*" class="d-none" required onchange="previewImage(event, ${count})">
        </label>
      </div>
      <div class="flex-grow-1">
        <textarea name="titles[]" class="form-control" placeholder="Enter the title..." required></textarea>
        <input name="titles_credit[]" class="form-control" placeholder="Enter the image credit" required style="height:35px;margin-top: 6px;">
      </div>
      <button type="button" class="remove-btn-img" onclick="document.getElementById('row${count}').remove()"><i class="bi bi-x-circle"></i></button>
    `;
    container.appendChild(div);
  }

  function previewImage(event, index) {
    const input = event.target;
    const reader = new FileReader();
    reader.onload = function(){
      document.getElementById(`preview${index}`).src = reader.result;
    }
    if(input.files && input.files[0]) {
      reader.readAsDataURL(input.files[0]);
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    addImageField(); // Add one field by default

    // Enable drag-and-drop sorting
    new Sortable(document.getElementById('imageInputs'), {
      animation: 150,
      handle: '.image-boxcls',
      ghostClass: 'sortableimg-ghost'
    });
  });
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
