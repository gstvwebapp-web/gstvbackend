@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
    <h3>Election List</h3>

    <a href="{{ url('electionresultmodule/add') }}" class="btn btn-success mb-2">Add</a>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Total Seats</th>
            <th>Action</th>
        </tr>

        @foreach($data as $row)
        <tr>
            <td>{{ $row->id }}</td>
            <td>{{ $row->title }}</td>
            <td>{{ $row->total_seats }}</td>
            <td>
                <a href="{{ url('electionresultmodule/edit/'.$row->id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ url('electionresultmodule/delete/'.$row->id) }}" class="btn btn-danger">Delete</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
 </main><!-- End #main -->
@endsection