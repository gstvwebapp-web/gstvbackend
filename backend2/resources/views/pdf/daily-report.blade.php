<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@font-face {
    font-family: 'NotoGujarati';
    src: url('{{ public_path('fonts/NotoSansGujarati-Regular.ttf') }}') format('truetype');
    font-weight: normal;
    font-style: normal;
}

body{
    font-family:'NotoGujarati';
    font-size:11px;
}

table{
    width:100%;
    border-collapse:collapse;
     table-layout: fixed;
    margin-bottom:15px;
}

th,td{
     border: 1px solid #000;
    padding: 3px;
    vertical-align: top;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
}

.section{
    background:#ccc;
    font-weight:bold;
}

.heading{
    text-align:center;
    font-size:18px;
    font-weight:bold;
    margin-bottom:15px;
}
</style>

</head>

<body>

<div class="heading">
Yesterday Report - {{ $reportDate }}
</div>

<table  style="width:30%; border-collapse:collapse;">
    <tr class="section">
        <td colspan="2">Total News</td>
    </tr>

    @foreach($newsSourceCount as $row)
    <tr>
        <td>{{ $row->source_name }}</td>
        <td>{{ $row->total_news }}</td>
    </tr>
    @endforeach
</table>

<table width="50%">

<tr class="section">
    <td colspan="3">Top 10 News</td>
</tr>

<tr>
    <th width="65%">Title</th>
    <th width="20%">Name</th>
    <th width="15%">Views</th>
</tr>

@foreach($news as $row)
<tr>
    <td>{{ $row['title'] }}</td>
    <td>{{ $row['user_name'] }}</td>
    <td>{{ number_format($row['views']) }}</td>
</tr>
@endforeach

</table>
  
  <table width="50%">

<tr class="section">
    <td colspan="3">Website -  Top 10 News</td>
</tr>

<tr>
    <th width="65%">Title</th>
    <th width="20%">Name</th>
    <th width="15%">Views</th>
</tr>

@foreach($newsDirect as $row)
<tr>
    <td>{{ $row['title'] }}</td>
    <td>{{ $row['user_name'] }}</td>
    <td>{{ number_format($row['views']) }}</td>
</tr>
@endforeach

</table>
  
  <table width="50%">

<tr class="section">
    <td colspan="3">TV - Top 10 News</td>
</tr>

<tr>
    <th width="65%">Title</th>
    <th width="20%">Name</th>
    <th width="15%">Views</th>
</tr>

@foreach($newsTV as $row)
<tr>
    <td>{{ $row['title'] }}</td>
    <td>{{ $row['user_name'] }}</td>
    <td>{{ number_format($row['views']) }}</td>
</tr>
@endforeach

</table>

<table  style="width:40%; border-collapse:collapse;">

<tr class="section">
    <td colspan="2">Web User Count</td>
</tr>

<tr>
    <th>Name</th>
    <th>News Count</th>
</tr>

@foreach($userData as $user)
<tr>
    <td>{{ $user->name }}</td>
    <td>{{ $user->today_news_count }}</td>
</tr>
@endforeach

</table>

<table  style="width:40%; border-collapse:collapse;">

<tr class="section">
    <td colspan="2">TV User Count</td>
</tr>

<tr>
    <th>Name</th>
    <th>News Count</th>
</tr>

@foreach($tvData as $user)
<tr>
    <td>{{ $user->name }}</td>
    <td>{{ $user->today_news_count }}</td>
</tr>
@endforeach

</table>

</body>
</html>