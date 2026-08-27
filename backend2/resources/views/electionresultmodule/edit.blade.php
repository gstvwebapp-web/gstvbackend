@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Election Module</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('electionresultmodule') }}">Election Module</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
          	@if(session('success'))
                <div class="col-lg-12">
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <div class="col-lg-12">
                <form method="POST" action="{{ url('electionresultmodule/update/' . $group->id) }}" id="electionEditForm">
                    @csrf

                    {{-- ── Main Title ── --}}
                    <div class="card mb-3 border-success">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8 pt-3">
                                    <label class="form-label fw-semibold fs-6">
                                        <i class="bi bi-bookmark-fill text-success me-1"></i>
                                        Main Title <span class="text-danger">*</span>
                                        <small class="text-muted fw-normal ms-1">(Common title for all election entries below)</small>
                                    </label>
                                    <input type="text"
                                           name="main_title"
                                           class="form-control form-control-lg"
                                           value="{{ old('main_title', $group->main_title) }}"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Existing election blocks ── --}}
                    <div class="row" id="editBlocksContainer">
                    @foreach($electionBlocks as $bIdx => $block)
                    @php
                        $election = $block['election'];
                        $columns  = $block['columns'];
                        $rows     = $block['rows'];
                        $values   = $block['values'];
                    @endphp

                    <div class="card col-lg-6 mb-4 pt-3 election-edit-block"
                         id="editblock_{{ $bIdx }}"
                         data-block="{{ $bIdx }}"
                         data-row-count="{{ count($rows) }}">

                        {{-- election_id > 0 = existing record --}}
                        <input type="hidden" name="blocks[{{ $bIdx }}][election_id]" value="{{ $election->id }}">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-primary mb-0">
                                    <i class="bi bi-table me-1"></i> Election Entry #{{ $bIdx + 1 }}
                                    <span class="badge bg-secondary ms-1" style="font-size:10px;">ID: {{ $election->id }}</span>
                                </h6>
                            </div>

                            {{-- ── State / Title / Total Seats ── --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Select State <span class="text-danger">*</span></label>
                                    <select name="blocks[{{ $bIdx }}][state_id]" class="form-select" required>
                                        <option value="">Choose...</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}"
                                                {{ $election->state_id == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Election Title <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="blocks[{{ $bIdx }}][title]"
                                           class="form-control"
                                           value="{{ $election->title }}"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Total Seats <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="blocks[{{ $bIdx }}][total_seats]"
                                           class="form-control"
                                           value="{{ $election->total_seats }}"
                                           min="1" required>
                                </div>
                            </div>

                            {{-- ── Dynamic Table ── --}}
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="table_{{ $bIdx }}">
                                    <thead class="table-light">
                                        <tr id="header_{{ $bIdx }}">
                                            <th style="min-width:180px;">Party Name</th>
                                            <th style="width:80px;">Color</th>
                                            @foreach($columns as $col)
                                                <th style="min-width:170px;">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input type="text"
                                                               name="blocks[{{ $bIdx }}][columns][]"
                                                               class="form-control form-control-sm"
                                                               value="{{ $col->column_name }}" required>
                                                        <div class="d-flex flex-column align-items-center" title="Hide this column in API/frontend">
                                                            <input type="checkbox"
                                                                   name="blocks[{{ $bIdx }}][col_hidden][]"
                                                                   value="1"
                                                                   class="form-check-input mt-0"
                                                                   style="width:16px;height:16px;cursor:pointer;"
                                                                   {{ ($col->is_hidden ?? 0) ? 'checked' : '' }}>
                                                            <small class="text-muted" style="font-size:9px;line-height:1;">Hide</small>
                                                        </div>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger p-1 lh-1"
                                                                onclick="removeColumn({{ $bIdx }}, this)">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                </th>
                                            @endforeach
                                            <th style="width:60px;" class="text-center">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body_{{ $bIdx }}">
                                        @foreach($rows as $rIdx => $row)
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="blocks[{{ $bIdx }}][rows][{{ $rIdx }}][party]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $row->party_name }}" required>
                                                </td>
                                                <td style="width:80px;text-align:center;">
                                                    <input type="color"
                                                           name="blocks[{{ $bIdx }}][rows][{{ $rIdx }}][color_code]"
                                                           value="{{ $row->color_code ?? '#e67e22' }}"
                                                           style="width:44px;height:32px;padding:2px;border:1px solid #dee2e6;border-radius:4px;cursor:pointer;">
                                                </td>
                                                @foreach($columns as $col)
                                                    <td>
                                                        <input type="text"
                                                               name="blocks[{{ $bIdx }}][rows][{{ $rIdx }}][values][]"
                                                               class="form-control form-control-sm"
                                                               value="{{ optional(isset($values[$row->id]) ? $values[$row->id]->where('column_id', $col->id)->first() : null)->value }}">
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="removeRow(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="addColumn({{ $bIdx }})">
                                    <i class="bi bi-plus-lg me-1"></i> Add Column
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-white" onclick="addRow({{ $bIdx }})">
                                    <i class="bi bi-plus-lg me-1"></i> Add Row
                                </button>
                            </div>

                        </div>
                    </div>
                    @endforeach
                    </div>{{-- end #editBlocksContainer --}}

                    {{-- ── Add More button ── --}}
                    <div class="text-center my-3">
                        <button type="button" class="btn btn-warning px-4" id="editAddMoreBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add More
                        </button>
                    </div>

                    {{-- ── Submit / Cancel ── --}}
                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-check-circle me-1"></i> Update
                        </button>
                        <a href="{{ url('electionresultmodule') }}" class="btn btn-secondary ms-2 px-4">Cancel</a>
                    </div>

                </form>
            </div>

        </div>
    </section>

</main>

<script>
// ── State options from PHP ────────────────────────────────────────────────────
var editStateOptionsHtml = '<option value="">Choose...</option>';
@foreach($states as $state)
editStateOptionsHtml += '<option value="{{ $state->id }}">{{ addslashes($state->name) }}</option>';
@endforeach

// Start new block index after existing PHP-rendered blocks
var editNewBlockCount = {{ count($electionBlocks) }};

// ── Add More: creates a brand-new block (election_id = 0 = new insert) ────────
function addNewEditBlock() {
    var b = editNewBlockCount++;

    var blockHtml = '<div class="card mb-4 pt-3 election-edit-block border-warning" id="editblock_' + b + '" data-block="' + b + '" data-row-count="0">'
        + '<div class="card-body">'
        + '<div class="d-flex justify-content-between align-items-center mb-3">'
        + '<h6 class="fw-bold text-warning mb-0"><i class="bi bi-table me-1"></i> New Election Entry <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">NEW</span></h6>'
        + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNewBlock(this)"><i class="bi bi-x-circle"></i> Remove</button>'
        + '</div>'
        // election_id = 0 signals "new insert" to the controller
        + '<input type="hidden" name="blocks[' + b + '][election_id]" value="0">'
        + '<div class="row g-3 mb-3">'
        + '<div class="col-md-4">'
        + '<label class="form-label fw-semibold">Select State <span class="text-danger">*</span></label>'
        + '<select name="blocks[' + b + '][state_id]" class="form-select" required>'
        + editStateOptionsHtml
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

    document.getElementById('editBlocksContainer').insertAdjacentHTML('beforeend', blockHtml);

    // Add one default column and one default row
    addColumn(b);
    addRow(b);

    // Scroll to the new block
    document.getElementById('editblock_' + b).scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function removeNewBlock(btn) {
    btn.closest('.election-edit-block').remove();
}

// ── Add Column ────────────────────────────────────────────────────────────────
function addColumn(b) {
    var headerRow = document.getElementById('header_' + b);
    // Insert before Delete th (last th)
    var allThs   = headerRow.querySelectorAll('th');
    var deleteTh = allThs[allThs.length - 1];

    var th = document.createElement('th');
    th.style.minWidth = '170px';
    th.innerHTML = '<div class="d-flex align-items-center gap-1">'
        + '<input type="text" name="blocks[' + b + '][columns][]" class="form-control form-control-sm" placeholder="Column heading" required>'
        + '<div class="d-flex flex-column align-items-center" title="Hide this column in API/frontend">'
        + '<input type="checkbox" name="blocks[' + b + '][col_hidden][]" value="1" class="form-check-input mt-0" style="width:16px;height:16px;cursor:pointer;">'
        + '<small class="text-muted" style="font-size:9px;line-height:1;">Hide</small>'
        + '</div>'
        + '<button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" onclick="removeColumn(' + b + ', this)"><i class="bi bi-x"></i></button>'
        + '</div>';
    headerRow.insertBefore(th, deleteTh);

    // Add empty value cell to every existing row — before delete td (last td)
    var rows = document.querySelectorAll('#body_' + b + ' tr');
    rows.forEach(function(tr, rowIdx) {
        var allTds   = tr.querySelectorAll('td');
        var deleteTd = allTds[allTds.length - 1];
        var firstInput = tr.querySelector('input[name*="[party]"]');
        var match      = firstInput ? firstInput.name.match(/rows\[(\d+)\]/) : null;
        var rIdx       = match ? match[1] : rowIdx;
        var td         = document.createElement('td');
        td.innerHTML   = '<input type="text" name="blocks[' + b + '][rows][' + rIdx + '][values][]" class="form-control form-control-sm">';
        tr.insertBefore(td, deleteTd);
    });
}

// ── Remove Column ─────────────────────────────────────────────────────────────
function removeColumn(b, btn) {
    var th        = btn.closest('th');
    var headerRow = document.getElementById('header_' + b);
    var allThs    = Array.from(headerRow.querySelectorAll('th'));
    var colPos    = allThs.indexOf(th);
    // colPos 0 = Party Name, 1 = Color — never remove those
    if (colPos <= 1) return;
    th.remove();
    document.querySelectorAll('#body_' + b + ' tr').forEach(function(tr) {
        var tds = tr.querySelectorAll('td');
        if (tds[colPos]) tds[colPos].remove();
    });
}

// ── Add Row ───────────────────────────────────────────────────────────────────
function addRow(b) {
    var block    = document.getElementById('editblock_' + b);
    var rowCount = parseInt(block.getAttribute('data-row-count'));
    block.setAttribute('data-row-count', rowCount + 1);

    var headerRow = document.getElementById('header_' + b);
    var colCount  = headerRow.querySelectorAll('th').length - 3; // minus Party + Color + Delete

    // Party name
    var cells = '<td style="min-width:180px;"><input type="text" name="blocks[' + b + '][rows][' + rowCount + '][party]" class="form-control form-control-sm" placeholder="Party name" required></td>';

    // Color picker
    cells += '<td style="width:80px;text-align:center;">'
           + '<input type="color" name="blocks[' + b + '][rows][' + rowCount + '][color_code]" value="#e67e22" '
           + 'style="width:44px;height:32px;padding:2px;border:1px solid #dee2e6;border-radius:4px;cursor:pointer;">'
           + '</td>';

    // Value cells
    for (var i = 0; i < colCount; i++) {
        cells += '<td><input type="text" name="blocks[' + b + '][rows][' + rowCount + '][values][]" class="form-control form-control-sm"></td>';
    }

    // Delete
    cells += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>';

    var tr = document.createElement('tr');
    tr.innerHTML = cells;
    document.getElementById('body_' + b).appendChild(tr);
}

// ── Remove Row ────────────────────────────────────────────────────────────────
function removeRow(btn) {
    btn.closest('tr').remove();
}

// ── Bind Add More button ──────────────────────────────────────────────────────
document.getElementById('editAddMoreBtn').addEventListener('click', function() {
    addNewEditBlock();
});
</script>

@endsection
