@extends('layouts.app')

@section('content')
<style>
.dashcls {
  gap:15px;
  margin: 3px;
}
.dashtablecls {
  gap:15px;
  margin: 3px;
  padding: 0;
}
.dashtablecls .col {padding: 0;}
.dashcls h4 { font-size:30px; }
.dashcolcls { 
    border: 1px solid #EA6A47;
    border-radius: 5px;
    padding: 30px 0;
    font-size:30px;
    background: #EA6A47;
    color: #fff;
    font-weight: bold;
    cursor:pointer;
 }
 .dashcolcls1 { 
    border: 1px solid #0091D5;
    border-radius: 5px;
    padding: 30px 0;
    font-size:30px;
    background: #0091D5;
    color: #fff;
    font-weight: bold;
    cursor:pointer;
 }
 .dashcolcls2 { 
    border: 1px solid #DBAE58;
    border-radius: 5px;
    padding: 30px 0;
    font-size:30px;
    background: #DBAE58;
    color: #fff;
    font-weight: bold;
    cursor:pointer;
 }
 /* Traffic sources horizontal table */
 .traffic-source-table th {
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    background: #f8f9fa;
 }
 .traffic-source-table td {
    text-align: right;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
 }
</style>
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      {{-- ── Active Users ── --}}
      <div class="row dashcls">
        <div class="col dashcolcls text-center">
          <h4>Total User 30 minutes</h4>
          {{$data['activeUsers']}}
        </div>
        <div class="col dashcolcls1 text-center">
          <h4>Total User 5 minutes</h4>
          {{$data['activeUsersbyminutes']}}
        </div>
      </div>

      {{-- ── Traffic Sources — horizontal (source names as columns, counts as one row) ── --}}
      <div class="row dashtablecls mt-4">
        <div class="col-md-12">
          <h5 class="mb-2">Traffic Sources</h5>
          <div class="table-responsive">
            @if(count($data['sourceUsers']) > 0)
              <table class="table table-bordered traffic-source-table" id="trafficSourceTable">
                <thead>
                  <tr>
                    @foreach($data['sourceUsers'] as $src)
                      <th style="text-align: center;">{{ $src['pageTitle'] }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    @foreach($data['sourceUsers'] as $src)
                      <td  style="text-align: center;">{{ number_format($src['views']) }}</td>
                    @endforeach
                  </tr>
                </tbody>
              </table>
            @else
              <p class="text-muted">No traffic source data available.</p>
            @endif
          </div>
        </div>
      </div>

      {{-- ── Real-Time: Last 30 Min (left) + Today (right) ── --}}
      <div class="row dashtablecls1 mt-4">

        {{-- Left: Last 30 Minutes --}}
        <div class="col-md-6">
          <h5 class="mb-2">Google Real-Time Page Views (Last 30 Minutes - Top 10)</h5>
          <table class="table table-bordered" id="realtimeTable30Min">
            <thead class="table-light">
              <tr>
                <th width="75%">Page Title</th>
                <th width="25%" class="text-center">Views</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data['ViewsByPageTitleLast30Minutes'] as $row30)
                <tr>
                  <td>{{ $row30['pageTitle'] }}</td>
                  <td class="text-center">{{ number_format($row30['views']) }}</td>
                </tr>
              @empty
                <tr><td class="text-center" colspan="2">No records found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Right: Today --}}
        <div class="col-md-6">
          <h5 class="mb-2">Google Real-Time Page Views (Today - Top 10)</h5>
          <table class="table table-bordered" id="realtimeTable">
            <thead class="table-light">
              <tr>
                <th width="75%">Page Title</th>
                <th width="25%" class="text-center">Views</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data['ViewsByPageTitleAndScreenName'] as $rowToday)
                <tr>
                  <td>{{ $rowToday['pageTitle'] }}</td>
                  <td class="text-center">{{ number_format($rowToday['views']) }}</td>
                </tr>
              @empty
                <tr><td class="text-center" colspan="2">No records found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
      <div class="row dashtablecls mt-4">
        <div class="col-md-12">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Today - Top 10 Stories</h5>
          </div>

          {{-- Tabs --}}
          <ul class="nav nav-tabs" id="todayTop10Tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-news" data-bs-toggle="tab"
                      data-bs-target="#pane-news" type="button" role="tab"
                      data-type="news">
                News
                <span class="badge bg-secondary ms-1" id="badge-news">—</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-videos" data-bs-toggle="tab"
                      data-bs-target="#pane-videos" type="button" role="tab"
                      data-type="videos">
                Videos
                <span class="badge bg-secondary ms-1" id="badge-videos">—</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-webstories" data-bs-toggle="tab"
                      data-bs-target="#pane-webstories" type="button" role="tab"
                      data-type="webstories">
                Web Stories
                <span class="badge bg-secondary ms-1" id="badge-webstories">—</span>
              </button>
            </li>
          </ul>

          <div class="tab-content border border-top-0 rounded-bottom p-3" id="todayTop10Content">

            {{-- News Tab --}}
            <div class="tab-pane fade show active" id="pane-news" role="tabpanel">
              <div id="loading-news" class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <span class="ms-2 text-muted">Loading...</span>
              </div>
              <table class="table table-bordered table-hover mb-2" id="table-news" style="display:none;">
                <thead class="table-light">
                  <tr><th>Title</th><th class="text-center" style="width:120px;">Views</th></tr>
                </thead>
                <tbody id="tbody-news"></tbody>
              </table>
              <a href="{{ url('/news') }}?start_date={{ date('Y-m-d') }}&end_date={{ date('Y-m-d') }}&sort_col=3&sort_dir=desc"
                 class="btn btn-sm btn-outline-success mt-1">View More &rarr;</a>
            </div>

            {{-- Videos Tab --}}
            <div class="tab-pane fade" id="pane-videos" role="tabpanel">
              <div id="loading-videos" class="text-center py-3" style="display:none;">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <span class="ms-2 text-muted">Loading...</span>
              </div>
              <table class="table table-bordered table-hover mb-2" id="table-videos" style="display:none;">
                <thead class="table-light">
                  <tr><th>Title</th><th class="text-center" style="width:120px;">Views</th></tr>
                </thead>
                <tbody id="tbody-videos"></tbody>
              </table>
              <a href="{{ url('/news') }}?tab=video&start_date={{ date('Y-m-d') }}&end_date={{ date('Y-m-d') }}&sort_col=3&sort_dir=desc"
                 class="btn btn-sm btn-outline-success mt-1">View More &rarr;</a>
            </div>

            {{-- Web Stories Tab --}}
            <div class="tab-pane fade" id="pane-webstories" role="tabpanel">
              <div id="loading-webstories" class="text-center py-3" style="display:none;">
                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                <span class="ms-2 text-muted">Loading...</span>
              </div>
              <table class="table table-bordered table-hover mb-2" id="table-webstories" style="display:none;">
                <thead class="table-light">
                  <tr><th>Title</th><th class="text-center" style="width:120px;">Views</th></tr>
                </thead>
                <tbody id="tbody-webstories"></tbody>
              </table>
              <a href="{{ url('/webstory') }}?start_date={{ date('Y-m-d', strtotime('-5 days')) }}&end_date={{ date('Y-m-d') }}&sort_col=2&sort_dir=desc"
                 class="btn btn-sm btn-outline-success mt-1">View More &rarr;</a>
            </div>

          </div>{{-- end tab-content --}}
        </div>
      </div>

    </section>

</main><!-- End #main -->

<script>
// Auto-refresh real-time data every 60 seconds
let refreshInterval;

function refreshRealtimeData() {
    fetch('{{ url("/dashboard/realtime") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                // Update active users
                const activeUsersElements = document.querySelectorAll('.dashcolcls, .dashcolcls1');
                if (activeUsersElements.length >= 2) {
                    const activeUsers30 = data.data.realtimeActiveUsers || 0;
                    activeUsersElements[0].innerHTML = '<h4>Total User 30 minutes</h4>' + activeUsers30;
                }

                // Update Traffic Sources horizontal table
                const trafficTable = document.querySelector('#trafficSourceTable');
                if (trafficTable && data.data.sourceUsers && data.data.sourceUsers.length > 0) {
                    let headHtml = '<tr>';
                    let bodyHtml = '<tr>';
                    data.data.sourceUsers.forEach(src => {
                        headHtml += `<th>${src.pageTitle}</th>`;
                        bodyHtml += `<td>${Number(src.views).toLocaleString()}</td>`;
                    });
                    headHtml += '</tr>';
                    bodyHtml += '</tr>';
                    trafficTable.querySelector('thead').innerHTML = headHtml;
                    trafficTable.querySelector('tbody').innerHTML = bodyHtml;
                }

                // Update Last 30 Minutes table
                const body30 = document.querySelector('#realtimeTable30Min tbody');
                if (body30) {
                    const rows = data.data.ViewsByPageTitleLast30Minutes || [];
                    if (rows.length > 0) {
                        body30.innerHTML = rows.map(p =>
                            `<tr><td>${p.pageTitle}</td><td class="text-center">${Number(p.views).toLocaleString()}</td></tr>`
                        ).join('');
                    } else {
                        body30.innerHTML = '<tr><td class="text-center" colspan="2">No records found.</td></tr>';
                    }
                }

                // Update Today table
                const bodyToday = document.querySelector('#realtimeTable tbody');
                if (bodyToday) {
                    const rows = data.data.ViewsByPageTitleAndScreenName || [];
                    if (rows.length > 0) {
                        bodyToday.innerHTML = rows.map(p =>
                            `<tr><td>${p.pageTitle}</td><td class="text-center">${Number(p.views).toLocaleString()}</td></tr>`
                        ).join('');
                    } else {
                        bodyToday.innerHTML = '<tr><td class="text-center" colspan="2">No records found.</td></tr>';
                    }
                }

                console.log('Dashboard refreshed at ' + (data.data.lastUpdated || new Date().toLocaleTimeString()));
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
        });
}

// ── Today Top 10 — AJAX tab loading ─────────────────────────────────────────
var top10Loaded = { news: false, videos: false, webstories: false };
var top10Url    = '{{ url("/dashboard/top10ajax") }}';

function loadTop10(type) {
    if (top10Loaded[type]) return; // already loaded — don't re-fetch

    var loading = document.getElementById('loading-' + type);
    var table   = document.getElementById('table-' + type);
    var tbody   = document.getElementById('tbody-' + type);
    var badge   = document.getElementById('badge-' + type);

    if (loading) loading.style.display = 'block';
    if (table)   table.style.display   = 'none';

    fetch(top10Url + '?type=' + type)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (loading) loading.style.display = 'none';

            if (!res.success || !tbody) return;

            var rows = res.data || [];
            if (badge) badge.textContent = rows.length;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No records found.</td></tr>';
            } else {
                tbody.innerHTML = rows.map(function(item) {
                    return '<tr>'
                        + '<td><a href="' + item.url + '" target="_blank">' + item.title + '</a></td>'
                        + '<td class="text-center fw-semibold">' + Number(item.views).toLocaleString() + '</td>'
                        + '</tr>';
                }).join('');
            }

            if (table) table.style.display = '';
            top10Loaded[type] = true;
        })
        .catch(function(err) {
            if (loading) loading.style.display = 'none';
            if (tbody)   tbody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error loading data.</td></tr>';
            if (table)   table.style.display = '';
            console.error('Top10 AJAX error (' + type + '):', err);
        });
}

// Load News tab on page load (default active tab)
document.addEventListener('DOMContentLoaded', function() {
    loadTop10('news');

    // Load other tabs on first click
    document.querySelectorAll('#todayTop10Tabs button[data-type]').forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function() {
            loadTop10(btn.getAttribute('data-type'));
        });
    });
});

// Start auto-refresh (every 60 seconds)
function startAutoRefresh() {
    refreshInterval = setInterval(refreshRealtimeData, 60000);
}

function stopAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
}

document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});

window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
</script>
@endsection
