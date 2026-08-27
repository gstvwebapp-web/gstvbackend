@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>People News</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">People News</li>
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
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="card-title">People News</h5>
                            </div>
                            <div class="col-lg-6 pt-3"> 
                                <!-- Uncomment to add a button for creating user news -->
                                <!-- <a class="btn float-end addCls btn-success" href="{{ url('/usernews/add') }}">Create user news</a> -->
                            </div>
                        </div>

                        <!-- Tabs for Active, Inactive, and Under Review news -->
                        <ul class="nav nav-tabs" id="newsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active" role="tab" aria-controls="active" aria-selected="true">Active</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="inactive-tab" data-bs-toggle="tab" href="#inactive" role="tab" aria-controls="inactive" aria-selected="false">Rejected</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="under-review-tab" data-bs-toggle="tab" href="#under-review" role="tab" aria-controls="under-review" aria-selected="false">Under Review</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="newsTabContent" style="padding:20px; 0px">
                            <!-- Active News Tab -->
                            <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                                <table class=" table table-bordered mt-3" id="userActivenews" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="5%">No.</th>
                                            <th scope="col" width="20%">Image</th>
                                            <th scope="col" width="50%">Title</th>
                                            <th scope="col" width="15%">Author</th>
                                            <th scope="col">Status</th>
                                            <!-- <th scope="col" width="10%">Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <!-- Inactive News Tab -->
                            <div class="tab-pane fade" id="inactive" role="tabpanel" aria-labelledby="inactive-tab">
                                <table class=" table table-bordered mt-3" id="userRejectednews" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="5%">#</th>
                                            <th scope="col" width="20%">Image</th>
                                            <th scope="col" width="50%">Title</th>
                                            <th scope="col" width="15%">Author</th>
                                            <th scope="col" width="15%">Reason for Reject</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <!-- Under Review News Tab -->
                            <div class="tab-pane fade" id="under-review" role="tabpanel" aria-labelledby="under-review-tab">
                                <table class=" table table-bordered mt-3" id="usernews" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="5%">#</th>
                                            <th scope="col" width="20%">Image</th>
                                            <th scope="col" width="50%">Title</th>
                                            <th scope="col" width="15%">Author</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div> <!-- End Tab Content -->
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->
<!-- Modal Box -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject News</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    @csrf
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="reject" name="rejectnews" placeholder="Enter reason" required></textarea>
                    </div>
                    <input type="hidden" id="newsId" name="newsId">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="rejectFormsubmit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script> 

     function openmodalreject(id) { 
        $('#newsId').val(id); // Set the news ID in the hidden input
        $('#rejectModal').modal('show'); // Show the modal
    }

    // Handle AJAX Submission
    $(document).ready(function () {
    $('#rejectFormsubmit').click(function (e) {
        e.preventDefault(); // Prevent default button action

        let formData = $('#rejectForm').serialize(); // Serialize form data
        $.ajax({
            url: "{{ url('/usernews/rejectnews') }}", // Backend URL for handling rejection
            type: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#rejectModal').modal('hide'); // Hide modal on success
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert(response.message); // Show error message
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText); // Log server error
                alert('An error occurred while processing your request.');
            }
        });
    });
});
</script>
@endsection
