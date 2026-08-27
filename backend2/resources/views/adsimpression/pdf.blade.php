<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ads Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1{
            margin:0;
            padding:0;
        }
        .container {
            /* overflow: hidden; */
            margin-bottom: 20px;
        }
        .left {
            float: left;
            width: 50%;
        }
        .right {
            float: right;
            width: 50%;
            text-align: right;
        }
        .chart {
            text-align: left;
            margin-bottom: 20px;
            padding: 10px;
        }
        img {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td><h1>Ads Report</h1></td>
            <td style="text-align:right;">
                <a href="https://gstv.in" style="color:red;">www.gstv.in</a>
                <br>
            @if ($date === $end_date)
                <b>Date :</b> {{ $date }}
            @else
                <b>Date :</b> {{ $date }} - {{ $end_date }}
            @endif
            </td>
        </tr>
    </table>
    <hr>
    @if (!empty($imgtype))
        <b>Block :</b> {{ $imgtype }}
        <br>
    @endif
    <!-- Impressions Chart -->
    <div class="chart" style="page-break-after: always;">
        Total Impressions : {{$viewcount}}<br>
        <img src="{{ $viewChartImage }}" style="width:100%" alt="Impressions Chart">
    </div>

    <!-- Clicks Chart -->
    <div class="chart" style="page-break-after: always;">
        Total Clicks : {{$clickscount}}<br>
        <img src="{{ $clickChartImage }}" style="width:100%" alt="Clicks Chart">
    </div>

    <!-- CTR Chart -->
    <div class="chart" style="page-break-after: always;">
        Total CTR : {{$CTR}} %<br>
        <img src="{{ $ctrChartImage }}" style="width:100%" alt="CTR Chart">
    </div>
</body>
</html>
