@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>MXF Videos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">MXF Videos</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            @if(session('error'))
                <div class="col-lg-12">
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                </div>
            @endif
            @if(session('success'))
                <div class="col-lg-12">
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="row mt-3 mb-3">
                            <div class="col-md-6">
                                <h5 class="card-title">MXF Videos List</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ url('/mxf-videos/add') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add New MXF Video
                                </a>
                            </div>
                        </div>

                        <!-- Table with stripped rows -->
                        <div class="table-responsive">
                            <table class="table table-bordered" id="mxfvideostable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="20%">Title</th>
                                        <th width="15%">Status</th>
                                        <th width="10%">Progress</th>
                                        <th width="20%">Final Video</th>
                                        <th width="15%">Created Date</th>
                                        <th width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <!-- End Table with stripped rows -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main><!-- End #main -->

<style>
/* Hide the DataTables length and search controls */
#mxfvideostable_length,
#mxfvideostable_filter {
    display: none !important;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#mxfvideostable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('/mxf-videos/mxfvideosfetchData') }}",
                "type": "GET"
            },
            "columns": [
                { 
                    "data": "id"
                },
                { 
                    "data": "title"
                },
                { 
                    "data": "status",
                    "render": function(data, type, row) {
                        let badgeClass = '';
                        switch(data) {
                            case 'pending':
                                badgeClass = 'bg-warning';
                                break;
                            case 'processing':
                                badgeClass = 'bg-info';
                                break;
                            case 'completed':
                                badgeClass = 'bg-success';
                                break;
                            case 'failed':
                                badgeClass = 'bg-danger';
                                break;
                            default:
                                badgeClass = 'bg-secondary';
                        }
                        return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                    }
                },
                { 
                    "data": "progress"
                },
                { 
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row) {
                        if (row.status === 'completed') {
                            return '<a href="/backend2/mxf-videos/edit/' + row.id + '" class="btn btn-sm btn-success">View/Download</a>';
                        } else if (row.status === 'failed') {
                            return '<a href="/backend2/mxf-videos/reprocess/' + row.id + '" class="btn btn-sm btn-warning">Reprocess</a>';
                        } else {
                            return '<span class="text-muted">Processing...</span>';
                        }
                    }
                },
                { 
                    "data": "created_at"
                },
                { 
                    "data": null,
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row) {
                        let actions = '';
                        actions += '<a href="/backend2/mxf-videos/edit/' + row.id + '" class="btn btn-sm btn-primary me-1"><i class="bi bi-pencil"></i></a>';
                        
                        if (row.status === 'Active') {
                            actions += '<a href="/backend2/mxf-videos/updateStatus/Inactive/' + row.id + '" class="btn btn-sm btn-warning me-1" title="Deactivate"><i class="bi bi-x-circle"></i></a>';
                        } else {
                            actions += '<a href="/backend2/mxf-videos/updateStatus/Active/' + row.id + '" class="btn btn-sm btn-success me-1" title="Activate"><i class="bi bi-check-circle"></i></a>';
                        }
                        
                        actions += '<button class="btn btn-sm btn-danger delete-btn" data-id="' + row.id + '"><i class="bi bi-trash"></i></button>';
                        
                        return actions;
                    }
                }
            ],
            "order": [[0, 'desc']],
            "pageLength": 20,
            "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "language": {
                "processing": "Loading...",
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });

        // Delete functionality
        $('#mxfvideostable').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            
            if (confirm('Are you sure you want to delete this MXF Video?')) {
                $.ajax({
                    url: '/backend2/mxf-videos/destroy/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status == 1) {
                            alert(response.message);
                            table.ajax.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error deleting video');
                    }
                });
            }
        });

        // Auto-refresh table only if there are processing videos
        var refreshInterval = null;
        
        function checkAndStartPolling() {
            // Check if any row has "processing" status
            var hasProcessing = false;
            
            table.rows().every(function() {
                var data = this.data();
                if (data && data.status === 'processing') {
                    hasProcessing = true;
                    return false; // break the loop
                }
            });
            
            if (hasProcessing && !refreshInterval) {
                // Start polling only if we have processing videos and not already polling
                console.log('✅ Starting progress polling - video(s) are processing');
                refreshInterval = setInterval(function() {
                    console.log('🔄 Polling for progress updates...');
                    table.ajax.reload(function() {
                        checkAndStartPolling(); // Re-check after reload
                    }, false);
                }, 5000); // Poll every 5 seconds
            } else if (!hasProcessing && refreshInterval) {
                // Stop polling if no processing videos
                console.log('⏹️ Stopping progress polling - no videos processing');
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }
        
        // Check on initial load
        table.on('draw', function() {
            checkAndStartPolling();
        });
        
        // Initial check after first load
        checkAndStartPolling();
    });
</script>

@endsection
