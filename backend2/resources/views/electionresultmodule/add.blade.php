@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Election Module</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('electionresultmodule') }}">Election Module</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">

            @if($errors->any())
                <div class="col-lg-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="col-lg-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <div class="col-lg-12">
                <form method="POST" action="{{ url('electionresultmodule/store') }}" id="electionForm">
                    @csrf

                    {{-- ── Main Title (page-level, applies to all blocks) ── --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8 pt-3">
                                    <label class="form-label fw-semibold fs-6">
                                        <i class="bi bi-bookmark-fill text-success me-1"></i>
                                        Main Title <span class="text-danger">*</span>
                                        <small class="text-muted fw-normal ms-1">(This title applies to all election entries below)</small>
                                    </label>
                                    <input type="text"
                                           name="main_title"
                                           id="mainTitleInput"
                                           class="form-control form-control-lg"
                                           placeholder="e.g. Gujarat Election Results 2024"
                                           value="{{ old('main_title') }}"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="electionBlocksContainer"></div>

                    <div class="text-center my-3">
                        <button type="button" class="btn btn-warning px-4" id="addMoreBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add More
                        </button>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-check-circle me-1"></i> Submit
                        </button>
                        <a href="{{ url('electionresultmodule') }}" class="btn btn-secondary ms-2 px-4">Cancel</a>
                    </div>

                </form>
            </div>

        </div>
    </section>

</main>

<script>
// State options HTML built from PHP
var stateOptionsHtml = '<option value="">Choose...</option>';
@foreach($states as $state)
stateOptionsHtml += '<option value="{{ $state->id }}">{{ addslashes($state->name) }}</option>';
@endforeach

var blockCount = 0;

function addElectionBlock() {
    var b = blockCount++;

    var blockHtml = '<div class="card mb-4 pt-3 election-block" id="block_' + b + '" data-block="' + b + '" data-col-count="0" data-row-count="0">'
        + '<div class="card-body">'
        + '<div class="d-flex justify-content-between align-items-center mb-3">'
        + '<h6 class="fw-bold text-success mb-0"><i class="bi bi-table me-1"></i> Election Entry #' + (b + 1) + '</h6>'
        + (b > 0 ? '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock(' + b + ')"><i class="bi bi-x-circle"></i> Remove</button>' : '')
        + '</div>'
        + '<div class="row g-3 mb-3">'
        + '<div class="col-md-4">'
        + '<label class="form-label fw-semibold">Select State <span class="text-danger">*</span></label>'
        + '<select name="blocks[' + b + '][state_id]" class="form-select" required>'
        + stateOptionsHtml
        + '</select>'
        + '</div>'
        + '<div class="col-md-4">'
        + '<label class="form-label fw-semibold">Election Title <span class="text-danger">*</span></label>'
        + '<input type="text" name="blocks[' + b + '][title]" class="form-control" placeholder="e.g. Election Title 2024" required>'
        + '</div>'
        + '<div class="col-md-4">'
        + '<label class="form-label fw-semibold">Total Seats <span class="text-danger">*</span></label>'
        + '<input type="number" name="blocks[' + b + '][total_seats]" class="form-control" placeholder="e.g. 182" min="1" required>'
        + '</div>'
        + '</div>'
        + '<div class="table-responsive">'
        + '<table class="table table-bordered align-middle" id="table_' + b + '">'
        + '<thead class="table-light">'
        + '<tr id="header_' + b + '">'
        + '<th style="min-width:180px;">Party Name</th>'
        + '<th style="width:80px;">Color</th>'
        + '<th style="width:60px;" class="text-center">Delete</th>'
        + '</tr>'
        + '</thead>'
        + '<tbody id="body_' + b + '"></tbody>'
        + '</table>'
        + '</div>'
        + '<div class="d-flex gap-2 mt-2">'
        + '<button type="button" class="btn btn-success btn-sm" onclick="addColumn(' + b + ')"><i class="bi bi-plus-lg me-1"></i> Add Column</button>'
        + '<button type="button" class="btn btn-info btn-sm text-white" onclick="addRow(' + b + ')"><i class="bi bi-plus-lg me-1"></i> Add Row</button>'
        + '</div>'
        + '</div>'
        + '</div>';

    document.getElementById('electionBlocksContainer').insertAdjacentHTML('beforeend', blockHtml);

    // Add one default column and one default row
    addColumn(b);
    addRow(b);
}

function removeBlock(b) {
    var el = document.getElementById('block_' + b);
    if (el) el.remove();
}

function addColumn(b) {
    var block  = document.getElementById('block_' + b);
    var colIdx = parseInt(block.getAttribute('data-col-count'));
    block.setAttribute('data-col-count', colIdx + 1);

    var headerRow = document.getElementById('header_' + b);
    // Insert before the last 2 ths: Color + Delete
    var allThs   = headerRow.querySelectorAll('th');
    var deleteTh = allThs[allThs.length - 1]; // Delete th

    var th = document.createElement('th');
    th.style.minWidth = '170px';
    th.innerHTML = '<div class="d-flex align-items-center gap-1">'
        + '<input type="text" name="blocks[' + b + '][columns][]" class="form-control form-control-sm col-input" placeholder="Column heading" required>'
        + '<div class="d-flex flex-column align-items-center" title="Hide this column in API/frontend">'
        + '<input type="checkbox" name="blocks[' + b + '][col_hidden][]" value="1" class="form-check-input mt-0" style="width:16px;height:16px;cursor:pointer;">'
        + '<small class="text-muted" style="font-size:9px;line-height:1;">Hide</small>'
        + '</div>'
        + '<button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" onclick="removeColumn(' + b + ', this)" title="Remove column"><i class="bi bi-x"></i></button>'
        + '</div>';
    headerRow.insertBefore(th, deleteTh);

    // Add a cell to every existing row — insert before the last 2 tds: color + delete
    var rows = document.querySelectorAll('#body_' + b + ' tr');
    rows.forEach(function(tr, rowIdx) {
        var allTds   = tr.querySelectorAll('td');
        var deleteTd = allTds[allTds.length - 1]; // delete td
        var firstInput = tr.querySelector('input[name*="[party]"]');
        var match = firstInput ? firstInput.name.match(/rows\[(\d+)\]/) : null;
        var rIdx  = match ? match[1] : rowIdx;
        var td = document.createElement('td');
        td.innerHTML = '<input type="text" name="blocks[' + b + '][rows][' + rIdx + '][values][]" class="form-control form-control-sm">';
        tr.insertBefore(td, deleteTd);
    });
}

function removeColumn(b, btn) {
    var th        = btn.closest('th');
    var headerRow = document.getElementById('header_' + b);
    var allThs    = Array.from(headerRow.querySelectorAll('th'));
    var colPos    = allThs.indexOf(th);

    // colPos 0 = Party Name, 1 = Color — never remove those
    if (colPos <= 1) return;

    th.remove();

    var rows = document.querySelectorAll('#body_' + b + ' tr');
    rows.forEach(function(tr) {
        var tds = tr.querySelectorAll('td');
        if (tds[colPos]) tds[colPos].remove();
    });

    var block = document.getElementById('block_' + b);
    block.setAttribute('data-col-count', Math.max(0, parseInt(block.getAttribute('data-col-count')) - 1));
}

function addRow(b) {
    var block   = document.getElementById('block_' + b);
    var rowIdx  = parseInt(block.getAttribute('data-row-count'));
    block.setAttribute('data-row-count', rowIdx + 1);

    var headerRow = document.getElementById('header_' + b);
    var colCount  = headerRow.querySelectorAll('th').length - 3; // minus Party Name + Color + Delete

    // Party name cell
    var cells = '<td style="min-width:180px;"><input type="text" name="blocks[' + b + '][rows][' + rowIdx + '][party]" class="form-control form-control-sm" placeholder="Party name" required></td>';

    // Color picker cell (right after party name)
    cells += '<td style="width:80px;text-align:center;">'
           + '<input type="color" name="blocks[' + b + '][rows][' + rowIdx + '][color_code]" value="#e67e22" '
           + 'style="width:44px;height:32px;padding:2px;border:1px solid #dee2e6;border-radius:4px;cursor:pointer;">'
           + '</td>';

    // Value cells
    for (var i = 0; i < colCount; i++) {
        cells += '<td><input type="text" name="blocks[' + b + '][rows][' + rowIdx + '][values][]" class="form-control form-control-sm"></td>';
    }

    // Delete cell
    cells += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>';

    var tr = document.createElement('tr');
    tr.innerHTML = cells;
    document.getElementById('body_' + b).appendChild(tr);
}

function removeRow(btn) {
    btn.closest('tr').remove();
}

// Bind Add More button
document.getElementById('addMoreBtn').addEventListener('click', function() {
    addElectionBlock();
});

// Add first block on page load
addElectionBlock();
</script>

@endsection
