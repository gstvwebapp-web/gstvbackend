@extends('layouts.app')

@section('content')
 <main id="main" class="main">
    <div class="pagetitle">
        
        <h1>{{ $data['event']->event_title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">{{ $data['event']->event_title }} Event</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
             <div class="col-lg-12">
                <div id="statusMessage" class="alert alert-success" style="display:none;"></div>
            </div>
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
                                <!-- <h5 class="card-title">Point List</h5> -->
                            </div>
                            <div class="col-lg-6 pt-3">
                                <a class="btn float-end addCls btn-success" href="{{ url('/events') }}">Back To Events</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered">
                            <tr>
                                <th width="10%">Title</th>
                                <td width="90%">{{ $data['event']->event_title }}</td>
                            </tr>
                            <tr>
                                <th>City</th>
                                <td>{{ $data['event']->event_city }}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{{ $data['event']->event_location }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{!! $data['event']->event_desc !!}</td>
                            </tr>
                            <tr>
                                <th>Start Date</th>
                                <td>{{ $data['event']->event_start_date }}</td>
                            </tr>
                            <tr>
                                <th>End Date</th>
                                <td>{{ $data['event']->event_end_date }}</td>
                            </tr>
                            <tr>
                                <th>Image</th>
                                <td>
                                    @if(!empty($data['event']->event_img))
                                        <img src="{{ config('app.backendnewsurl')}}/public/uploads/events/{{ $data['event']->event_img }}" width="200">
                                    @else
                                        No Image
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select class="form-select w-25" id="statusSelect" data-id="{{ $data['event']->event_id }}">
                                        <option value="Under Review" {{ $data['event']->status == 'Under Review' ? 'selected' : '' }}>Under Review</option>
                                        <option value="Active" {{ $data['event']->status == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Rejected" {{ $data['event']->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                        <!-- <option value="Inactive" {{ $data['event']->status == 'Inactive' ? 'selected' : '' }}>Inactive</option> -->
                                    </select>
                                </td>
                            </tr>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </section>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#statusSelect').change(function() {
        var eventID = $(this).data('id');
        var status = $(this).val();

        $.ajax({
            url: "{{ url('/events/update-status') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                event_id: eventID,
                status: status
            },
            success: function(response) {
                if(response.success){
                    $('#statusMessage').text(response.message).fadeIn();
                    setTimeout(function() {
                        $('#statusMessage').fadeOut();
                        location.reload();
                    }, 1000);
                }
            }
        });
    });
});
</script>

 
@endsection
