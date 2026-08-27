<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Analytics Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Google Analytics Report</h2>

        <form method="GET" action="{{ url('/analytics') }}" class="mb-3">
            <label for="days">Select Period:</label>
            <select name="days" id="days" class="form-select w-25 d-inline">
                <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Last 30 Days</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Page Title</th>
                    <th>Page Path</th>
                    <th>Views</th>
                    <th>Active Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analyticsData as $data)
                    <tr>
                        <td>{{ $data['pageTitle'] }}</td>
                        <td>{{ $data['pagePath'] }}</td>
                        <td>{{ $data['screenPageViews'] }}</td>
                        <td>{{ $data['activeUsers'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
