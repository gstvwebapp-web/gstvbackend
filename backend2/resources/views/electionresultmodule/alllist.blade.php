@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Election Module</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Election Module</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">

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
                <div class="card">
                    <div class="card-body">

                        <div class="row align-items-center mb-2">
                            <div class="col-lg-6">
                                <h5 class="card-title mb-0">Election Module</h5>
                            </div>
                            <div class="col-lg-6 text-end pt-3">
                                <a class="btn btn-success" href="{{ url('electionresultmodule/add') }}">
                                    <i class="bi bi-plus-circle me-1"></i> Create
                                </a>
                            </div>
                        </div>

                        <table class="table table-bordered table-hover" id="electionModuleTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Main Title & States</th>
                                    <th>Status</th>
                                    <th>Author</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </section>

</main>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete this election group? This will also delete all states, columns, rows and values under it.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- DataTables loaded by layout for electionresultmodule path --}}

<script>
// Wait for jQuery + DataTables to be available (loaded after this by the layout)
function initElectionModuleTable() {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
        setTimeout(initElectionModuleTable, 50);
        return;
    }
    $('#electionModuleTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("electionresultmodule/fetchData") }}',
            type: 'GET'
        },
        columns: [
            { data: 0, orderable: false },   // No.
            { data: 1, orderable: false },   // Main Title + child table
            { data: 2, orderable: false },   // Status
            { data: 3, orderable: false },   // Author
            { data: 4, orderable: false }    // Actions
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        createdRow: function(row) {
            // Allow HTML in cells
            $(row).find('td').css('vertical-align', 'top');
        }
    });
}
initElectionModuleTable();

var deleteId = null;

function deleteGroup(id) {
    deleteId = id;
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!deleteId) return;
    fetch('{{ url("electionresultmodule/delete") }}/' + deleteId, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        if (res.success) {
            $('#electionModuleTable').DataTable().ajax.reload();
            showAlert('Deleted successfully.', 'success');
        } else {
            showAlert(res.message, 'danger');
        }
        deleteId = null;
    });
});

function showAlert(msg, type) {
    var div = document.createElement('div');
    div.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed top-0 end-0 m-3';
    div.style.zIndex = 9999;
    div.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(div);
    setTimeout(function() { div.remove(); }, 3000);
}
</script>

@endsection
