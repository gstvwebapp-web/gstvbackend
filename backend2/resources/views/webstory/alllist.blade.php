@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="pagetitle">
      <h1>Web Stories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Web Stories</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
      @if(session('error'))
        <div class="col-lg-12">
          <div class="alert alert-danger">{{ session('error') }}</div>
        </div>
      @endif
      @if(session('status'))
        <div class="col-lg-12">
          <div class="alert alert-success">{{ session('status') }}</div>
        </div>
      @endif

      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">

            {{-- ── Header ── --}}
            <div class="row mb-2">
              <div class="col-lg-6">
                <h5 class="card-title">Web Stories</h5>
              </div>
              <div class="col-lg-6 pt-3">
                <a class="btn float-end addCls btn-success" href="{{ url('/webstory/add') }}">Create Web Story</a>
              </div>
            </div>

            {{-- ── Filter Form ── --}}
            <form id="webstoryformsearch" method="GET" action="{{ url('/webstory') }}">
              <div class="row g-2 align-items-end mb-3">

                <div class="col-lg-3">
                  <div class="d-flex align-items-center gap-2">
                    <label for="ws_start_date" class="text-nowrap mb-0">Start Date:</label>
                    <input type="date" id="ws_start_date" name="start_date"
                           class="form-control form-control-sm"
                           value="{{ request('start_date') }}">
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="d-flex align-items-center gap-2">
                    <label for="ws_end_date" class="text-nowrap mb-0">End Date:</label>
                    <input type="date" id="ws_end_date" name="end_date"
                           class="form-control form-control-sm"
                           value="{{ request('end_date') }}">
                  </div>
                </div>

                <div class="col-lg-2">
                  <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                </div>

                <div class="col-lg-2">
                  <a href="{{ url('/webstory') }}" class="btn btn-secondary btn-sm w-100">Reset</a>
                </div>

              </div>
            </form>

            {{-- ── DataTable ── --}}
            <table class="table table-bordered display" id="webstorytabledata">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Title</th>
                  <th>Counts</th>
                  <th>Author</th>
                  <th>Created Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>

          </div>
        </div>
      </div>
    </section>

</main><!-- End #main -->

<script>
// Prevent default form submit — reload DataTable with filters instead
document.getElementById('webstoryformsearch').addEventListener('submit', function (e) {
    e.preventDefault();
    function waitAndReload() {
        if (typeof $ === 'undefined' || !$.fn.DataTable || !$.fn.DataTable.isDataTable('#webstorytabledata')) {
            setTimeout(waitAndReload, 50);
            return;
        }
        $('#webstorytabledata').DataTable().ajax.reload();
    }
    waitAndReload();
});

// ── Auto-apply URL params on page load (e.g. from dashboard "View More") ──
(function applyUrlParams() {
    var params = new URLSearchParams(window.location.search);
    var startDate = params.get('start_date');
    var endDate   = params.get('end_date');
    var sortCol   = parseInt(params.get('sort_col') || '0', 10);
    var sortDir   = (params.get('sort_dir') || 'desc').toLowerCase();

    // Pre-fill date inputs from URL
    if (startDate && document.getElementById('ws_start_date')) {
        document.getElementById('ws_start_date').value = startDate;
    }
    if (endDate && document.getElementById('ws_end_date')) {
        document.getElementById('ws_end_date').value = endDate;
    }

    // If sort params present, wait for DataTable then apply sort
    if (params.has('sort_col') || params.has('sort_dir')) {
        (function waitForDT() {
            if (typeof $ === 'undefined' || !$.fn.DataTable || !$.fn.DataTable.isDataTable('#webstorytabledata')) {
                setTimeout(waitForDT, 50);
                return;
            }
            var checkDT = setInterval(function () {
                if ($.fn.DataTable.isDataTable('#webstorytabledata')) {
                    clearInterval(checkDT);
                    // Reload with date filters then apply sort
                    var dt = $('#webstorytabledata').DataTable();
                    dt.ajax.reload(function () {
                        dt.order([sortCol, sortDir]).draw(false);
                    });
                }
            }, 100);
        })();
    }
})();
</script>

@endsection
