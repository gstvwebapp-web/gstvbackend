@extends('layouts.app')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>{{ $data['user']->firstname }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">{{ $data['user']->firstname }} Profile</li>
            </ol>
        </nav>
    </div>

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
                                <h5 class="card-title">User Profile</h5>
                            </div>
                            <div class="col-lg-6 pt-3">
                                <a class="btn float-end addCls btn-success" href="{{ url('/frontend-users') }}">Back Frontend Users</a>
                            </div>
                        </div>
                        <div class="row">
                           
                            <div class="col-lg-6">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Profile Pic</th>
                                        <td>
                                            @if(!empty($data['user']->profileImg))
                                                <img src="{{ $data['user']->profileImg }}" 
                                                    class="img-fluid"
                                                    style="width:120px;height:120px;object-fit:cover;">
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>First Name</th>
                                        <td>{{ $data['user']->firstname ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Last Name</th>
                                        <td>{{ $data['user']->lastname ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $data['user']->email ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Mobile</th>
                                        <td>{{ $data['user']->mobile ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Gender</th>
                                        <td>{{ !empty($data['user']->gender) ? ucfirst($data['user']->gender) : '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>City</th>
                                        <td>{{ $data['user']->city_name ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Birth Date</th>
                                        <td>
                                            {{ !empty($data['user']->birthdate) ? date('d-m-Y', strtotime($data['user']->birthdate)) : '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Birth Time</th>
                                        <td>
                                            {{ !empty($data['user']->bdaytime) ? $data['user']->bdaytime.' '.strtoupper($data['user']->bdaytimeampm) : '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Birth Place</th>
                                        <td>{{ $data['user']->birthdateplace ?? '-' }}</td>
                                    </tr>

                                </tbody>
                            </table>
                            </div>
                        </div>
                       

                        <!-- Table with stripped rows -->
                        <!-- <table class="datatable table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $i = 0;
                                $dates = [];
                                $durations = [];
                                @endphp
                                @foreach ($data['userduration'] as $user)
                                    @php
                                    $seconds =  $user->total_duration ;
                                    $minutes = floor($seconds / 60);
                                    $remainingSeconds = $seconds % 60;

                                    $hours = floor($minutes / 60);
                                    $remainingMinutes = $minutes % 60;
                                    $duration =  "$hours hours, $remainingMinutes minutes, and $remainingSeconds seconds.\n";

                                    $dates[] = date("d-m-Y", strtotime($user->date));
                                    $durations[] = $user->total_duration;
                                    @endphp
                                    <tr>
                                        <th scope="row">{{ ++$i }}</th>
                                        <td>{{ date("d-m-Y", strtotime($user->date)) }}</td>
                                        <td>{{ $duration }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table> -->
                        <!-- End Table with stripped rows -->

                        <!-- Tabs for Week, Month, Daily -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab" aria-controls="daily" aria-selected="true">Daily</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab" aria-controls="weekly" aria-selected="false">Weekly</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab" aria-controls="monthly" aria-selected="false">Monthly</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">
                                <canvas id="dailyChart"></canvas>
                            </div>
                            <div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                            <div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dailyCtx = document.getElementById('dailyChart').getContext('2d');
        var weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        var monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

        // Daily Data
        var dailyData = {
            labels: @json(array_keys($data['dailyData'])),
            datasets: [{
                label: 'Total Duration (minutes)',
                data: @json(array_values($data['dailyData'])),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        var dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: dailyData,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Total Duration: {{ $data['totalDailyMinutes'] }} minutes'
                    }
                }
            }
        });

        // Weekly Data
        var weeklyData = {
            labels: @json(array_keys($data['weeklyData'])),
            datasets: [{
                label: 'Total Duration (minutes)',
                data: @json(array_values($data['weeklyData'])),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        var weeklyChart = new Chart(weeklyCtx, {
            type: 'bar',
            data: weeklyData,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Weekly Total Duration: {{ $data['totalWeeklyMinutes'] }} minutes'
                    }
                }
            }
        });

        // Monthly Data
        var monthlyData = {
            labels: @json(array_keys($data['monthlyData'])),
            datasets: [{
                label: 'Total Duration (hours)',
                data: @json(array_values($data['monthlyData'])),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        var monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: monthlyData,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Total Duration: {{ $data['totalMonthlyHours'] }} hours'
                    }
                }
            }
        });
    });
</script>
@endsection
