@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Ads Report</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Ads Export</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Date Filter Form -->
                        <div class="col-md-2" style="float:right;text-align:right">
                            <button id="downloadPdf" class="btn btn-primary">Download PDF</button>
                        </div>
                        <form method="GET" action="{{ url('/adsimpression/graph') }}">
                            <div class="row">
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_date">Block</label>
                                        @php
                                        $options = [
                                                "Riverfront" => "Riverfront",
                                                "Aarogyaprad_Bhojan" => "Aarogyaprad_Bhojan",
                                                "Renewable_energy" => "Renewable_energy",
                                                "Nmo_Lakshmi_Yojana" => "Nmo_Lakshmi_Yojana",
                                                "Cashless_sarvar" => "Cashless_sarvar",
                                                "Nmo_Sarsavati_Yojana" => "Nmo_Sarsavati_Yojana",
                                                "Smart_and_Digital" => "Smart_and_Digital",
                                                "Vidhya_smiksha_candra" => "Vidhya_smiksha_candra",
                                                "MSME_Ekmo" => "MSME_Ekmo",
                                                "U_S_Dollar" => "U_S_Dollar",
                                                "Milk_utpadan" => "Milk_utpadan",
                                                "Prakrutik_kheti" => "Prakrutik_kheti",
                                                "van_bandhu_kalyan_yojana" => "van_bandhu_kalyan_yojana",
                                                "mata_pita_yojana" => "mata_pita_yojana",
                                                "10-06-2024_SachitraBalpothi_01" => "SachitraBalpothi_01",
                                                "10-06-2024_ShalaPraveshutsavG1_Page_1" => "ShalaPraveshutsavG1_Page_1",
                                                "10-06-2024_ShalaPraveshutsavG1_Page_2" => "ShalaPraveshutsavG1_Page_2",
                                                "10-06-2024_ShalaPraveshutsavG2" => "ShalaPraveshutsavG2",
                                                "10-06-2024_ShalaPraveshutsavG3" => "ShalaPraveshutsavG3",
                                                "10-06-2024ShikshitGujarat" => "ShikshitGujarat",
                                                "14-06_SP_G7" => "SP G7",
                                                "14-06_SP_G8" => "SP G8",
                                                "14-06_SP_G91" => "SP G91",
                                                "14-06_SP_G92" => "SP G92",
                                                "14-06_SP_G93" => "SP G93",
                                                "14-06_SP_G94" => "SP G94",
                                                "14-06_SP_G102" => "SP G102",
                                                "14-06-2024_BhaarVinanuBanatar" => "BhaarVinanuBanatar",
                                                "14-06-2024_GujaratAbhiyaan" => "GujaratAbhiyaan",
                                                "14-06-2024_Gunotshv_01" => "Gunotshv_01",
                                                "14-06-2024_NipurnGujarat" => "NipurnGujarat",
                                                "14-06-2024_VikashnuEndhan" => "VikashnuEndhan",
                                                "19-06-2024_Kanya_KelavanidikriParki" => "Kanya_KelavanidikriParki",
                                                "19-06-2024_Kanya_Kelavanidikrodikri" => "Kanya_Kelavanidikrodikri",
                                                "20-06-2024_KalaAbhyash" => "KalaAbhyash",
                                                "20-06-2024_OnlyEducation" => "OnlyEducation",
                                                "20-06-2024_Sanskari" => "Sanskari",
                                                "20-06-2024_Sau_Bhane_Sau_Gane" => "Sau_Bhane_Sau_Gane",
                                                "22-06-2024_E LearningPlatform" => "E LearningPlatform",
                                            ];

                                            // Function to replace underscores with spaces
                                            function formatOptionText($text) {
                                                return str_replace('_', ' ', $text);
                                            }
                                        @endphp
                                        <select name="imgtype" class="form-control">
                                            <option value="">Select Ads</option>
                                            @foreach ($options as $value => $text)
                                                <option value="{{ $value }}" {{ $imgtype === $value ? 'selected' : '' }}>{{ str_replace('_', ' ', $text) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_date">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $date ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ $end_date ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    </div>
                                </div>
                                
                            </div>
                        </form>
                        <!-- End Date Filter Form -->
                        <br>
                        <!-- Charts -->
                        <div class="row">
                            <div class="col-lg-4">
                                <h3>Impressions Chart</h3>
                                Total Impressions : {{$viewcount}}
                                <canvas id="viewChart" width="1200" height="450"></canvas>
                            </div>
                            <div class="col-lg-4">
                                <h3>Clicks Chart</h3>
                                Total Clicks : {{$clickscount}}
                                <canvas id="clickChart" width="1200" height="450"></canvas>
                            </div>
                            <div class="col-lg-4">
                                <h3>CTR (Click-Through Rate) Chart</h3>
                                Total CTR : {{$CTR}} %
                                <canvas id="ctrChart" width="1200" height="450"></canvas>
                            </div>
                        </div> 
                        <!-- End Charts -->
                        
                        <!-- CTR Chart -->
                        <div class="row mt-4">
                            
                        </div>
                        <!-- End CTR Chart -->
                    </div>    
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
<script>
document.addEventListener('DOMContentLoaded', function() {

            
    var ads = @json($ads);
    // var images = ads.map(function(ad,index) {
    //     // return ad.image;
    //     var datesplit = ad.created_at.split(' '); // ['2024-06-28', '12:00:00']
    //     return datesplit[0];
    // });
    
if ("<?= $imgtype ?>" === "") {
    var images = ads.map(function(ad) {
        return ad.date; // Use the date field directly
    });

} else {
    var images = ads.map(function(ad, index) {
        // if ("<?= $imgtype ?>" === "") {
        //     return 'Block-' + (index + 1); // Display 'Block-1', 'Block-2', etc. if $imgtype is empty
        // } else {
            var datesplit = ad.created_at.split(' '); // Splitting created_at by space
            return datesplit[0]; // Display date if $imgtype is not empty
            // var modifiedString = ad.image.replace(/_/g, ' ');
            // return modifiedString; // Display date if $imgtype is not empty
        // }
    });
}
    var views = ads.map(function(ad) {
        return ad.total_views;
    });
    var clicks = ads.map(function(ad) {
        return ad.total_clicks;
    });
    
    var ctr = ads.map(function(ad) {
            return ad.CTR;
        });
    // Views Chart
    var ctxViews = document.getElementById('viewChart').getContext('2d');
    var viewsChart = new Chart(ctxViews, {
        type: 'line',
        data: {
            labels: images,
            datasets: [{
                label: 'Impressions',
                data: views,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.4,
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            plugins: {
                title: {
                    display: false,
                    text: 'Ads Impressions',
                    font: {
                        size: 18
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: false,
                        text: 'Image'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: false,
                        text: 'Impressions'
                    },
                    min: 0,
                    beginAtZero: true
                }
            }
        }
    });

    // Clicks Chart
    var ctxClicks = document.getElementById('clickChart').getContext('2d');
    var clicksChart = new Chart(ctxClicks, {
        type: 'line',
        data: {
            labels: images,
            datasets: [{
                label: 'Clicks Count',
                data: clicks,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.4,
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            plugins: {
                title: {
                    display: false,
                    text: 'Ads Clicks Count',
                    font: {
                        size: 18
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: false,
                        text: 'Image'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: false,
                        text: 'Clicks Count'
                    },
                    min: 0,
                    beginAtZero: true
                }
            }
        }
    });

    // CTR Chart
    var ctxCtr = document.getElementById('ctrChart').getContext('2d');
    var ctrChart = new Chart(ctxCtr, {
        type: 'line',
        data: {
            labels: images,
            datasets: [{
                label: 'CTR (%)',
                data: ctr,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            plugins: {
                title: {
                    display: false,
                    text: 'Click-Through Rate (CTR)',
                    font: {
                        size: 18
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: false,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: false,
                        text: 'CTR (%)'
                    },
                    min: 0,
                    beginAtZero: true
                }
            }
        }
    });

    // Export charts as images
    var exportCharts = function() {
        var viewChartImage = viewsChart.toBase64Image();
        var clickChartImage = clicksChart.toBase64Image();
        var ctrChartImage = ctrChart.toBase64Image();

        // Send the images to the backend
        fetch('{{ url("/adsimpression/pdf") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                start_date: '{{$date}}',
                end_date: '{{$end_date}}',
                imgtype: '{{$imgtype}}',
                viewChartImage: viewChartImage,
                clickChartImage: clickChartImage,
                ctrChartImage: ctrChartImage
            })
        }).then(response => response.blob()).then(blob => {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            // var startDate = '{{ $date }}';  
           // var dateParts = startDate.split('-');
           // var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];  // '21-11-2024'
    
            //a.download = formattedDate +'.pdf';
            a.download = 'ads_report.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });
    };

    // Add event listener to download button
    document.getElementById('downloadPdf').addEventListener('click', exportCharts);
    
});
</script>



@endsection
