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
      <div class="row dashtablecls mt-4">
        <div class="col-md-4">
          <table class="table table-bordered">
              <thead>
                  <tr>
                    <th scope="col">Title</th>
                    <th scope="col">View</th>
                  </tr>
              </thead>
              <tbody>
                 @if(count($data['sourceUsers'])>0)   
                  @foreach($data['sourceUsers'] as $sourceUsers)
                  <tr>
                    <td>{{ $sourceUsers['pageTitle'] }}</td>
                    <td>{{ $sourceUsers['views'] }}</td>
                  </tr>
                  @endforeach
                  @else
                  <tr>
                    <td class="text-center" colspan="2">No records found.</td>
                  </tr>
                @endif
              </tbody>
          </table>
        </div>
        <div class="col-md-7">
          <table class="table table-bordered">
              <thead>
                  <tr>
                    <th scope="col">Page Title</th>
                    <th scope="col">View</th>
                  </tr>
              </thead>
              <tbody>
                 @if(count($data['ViewsByPageTitleAndScreenName'])>0)   
                  @foreach($data['ViewsByPageTitleAndScreenName'] as $ViewsByPageTitleAndScreenName)
                  <tr>
                   
                    <td>{{ $ViewsByPageTitleAndScreenName['pageTitle'] }}</td>
                    <td>{{ $ViewsByPageTitleAndScreenName['views'] }}</td>
                  </tr>
                  @endforeach
                  @else
                  <tr>
                    <td class="text-center" colspan="2">No records found.</td>
                  </tr>
                @endif
              </tbody>
          </table>
        </div>
      </div>
      <!-- <div class="row dashcls">
        <div class="col dashcolcls text-center">
          <h4>Total Stories</h4>
          {{$data['news']}}
        </div>
        <div class="col dashcolcls1 text-center">
          <h4>Total Videos</h4>
          {{$data['video']}}
        </div>
        <div class="col dashcolcls2 text-center">
          <h4>Total Web Stories</h4>
          {{$data['webstory']}}
        </div>
      </div>
      <div class="row dashtablecls mt-4">
        <div class="col">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
                
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="day-tab" data-bs-toggle="tab" data-bs-target="#day" type="button" role="tab" aria-controls="day" aria-selected="false">Day</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="week-tab" data-bs-toggle="tab" data-bs-target="#week" type="button" role="tab" aria-controls="week" aria-selected="false">Week</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month" type="button" role="tab" aria-controls="month" aria-selected="true">Month</button>
                </li>
              </ul>
              <div class="tab-content pt-3" id="myTabContent">
                <div class="tab-pane fade show active" id="day" role="tabpanel" aria-labelledby="day-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                     @if(count($data['topviewernews'])>0)   
                      @foreach($data['topviewernews'] as $topviewernews)
                      <tr>
                        <td>{{ $topviewernews->title }}</td>
                        <td>{{ $topviewernews->viewer }}</td>
                      </tr>
                      @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="week" role="tabpanel" aria-labelledby="week-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(count($data['topviewernewsweek'])>0)   
                    @foreach($data['topviewernewsweek'] as $topviewernewsweek)
                    <tr>
                      <td>{{ $topviewernewsweek->title }}</td>
                      <td>{{ $topviewernewsweek->viewer }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="month" role="tabpanel" aria-labelledby="month-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(count($data['topviewernewsmonth'])>0)   
                    @foreach($data['topviewernewsmonth'] as $topviewernewsmonth)
                    <tr>
                      <td>{{ $topviewernewsmonth->title }}</td>
                      <td>{{ $topviewernewsmonth->viewer }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                </div>
        </div>
        </div>
        <div class="col">
        <ul class="nav nav-tabs" id="myTab1" role="tablist">
                
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="day1-tab" data-bs-toggle="tab" data-bs-target="#day1" type="button" role="tab" aria-controls="day1" aria-selected="false">Day</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="week1-tab" data-bs-toggle="tab" data-bs-target="#week1" type="button" role="tab" aria-controls="week1" aria-selected="false">Week</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="month1-tab" data-bs-toggle="tab" data-bs-target="#month1" type="button" role="tab" aria-controls="month1" aria-selected="true">Month</button>
                </li>
              </ul>
              <div class="tab-content pt-3" id="myTabContent">
                <div class="tab-pane fade show active" id="day1" role="tabpanel" aria-labelledby="day1-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(count($data['topviewervideo'])>0)   
                    @foreach($data['topviewervideo'] as $topviewervideo)
                    <tr>
                      <td>{{ $topviewervideo->title }}</td>
                      <td>{{ $topviewervideo->viewer }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="week1" role="tabpanel" aria-labelledby="week1-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(count($data['topviewervideoweek'])>0)   
                    @foreach($data['topviewervideoweek'] as $topviewervideoweek)
                    <tr>
                      <td>{{ $topviewervideoweek->title }}</td>
                      <td>{{ $topviewervideoweek->viewer }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="month1" role="tabpanel" aria-labelledby="month1-tab">
                  <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">View</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(count($data['topviewervideomonth'])>0)   
                    @foreach($data['topviewervideomonth'] as $topviewervideomonth)
                    <tr>
                      <td>{{ $topviewervideomonth->title }}</td>
                      <td>{{ $topviewervideomonth->viewer }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
                    </tbody>
                    </table>
                  </div>
            </div>
        </div>
        <div class="col">
        <ul class="nav nav-tabs" id="myTab2" role="tablist">
                
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="day2-tab" data-bs-toggle="tab" data-bs-target="#day2" type="button" role="tab" aria-controls="day2" aria-selected="false">Day</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="week2-tab" data-bs-toggle="tab" data-bs-target="#week2" type="button" role="tab" aria-controls="week2" aria-selected="false">Week</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="month2-tab" data-bs-toggle="tab" data-bs-target="#month2" type="button" role="tab" aria-controls="month2" aria-selected="true">Month</button>
                </li>
              </ul>
              <div class="tab-content pt-3" id="myTabContent">
                <div class="tab-pane fade show active" id="day2" role="tabpanel" aria-labelledby="day2-tab">
                   
        <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">View</th>
              </tr>
            </thead>
            <tbody>
            @if(count($data['topwebstory'])>0)   
          @foreach($data['topwebstory'] as $topwebstory)
          <tr>
            <td>{{ $topwebstory->title }}</td>
            <td>{{ $topwebstory->viewer }}</td>
          </tr>
          @endforeach
          @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
          </tbody>
          </table>
      </div>

      <div class="tab-pane fade" id="week2" role="tabpanel" aria-labelledby="week2-tab">
        <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">View</th>
              </tr>
            </thead>
            <tbody>
            @if(count($data['topwebstoryweek'])>0)   
          @foreach($data['topwebstoryweek'] as $topwebstoryweek)
          <tr>
            <td>{{ $topwebstoryweek->title }}</td>
            <td>{{ $topwebstoryweek->viewer }}</td>
          </tr>
          @endforeach
          @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
          </tbody>
          </table>
      </div>
      <div class="tab-pane fade" id="month2" role="tabpanel" aria-labelledby="month2-tab">
        <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">View</th>
              </tr>
            </thead>
            <tbody>
            @if(count($data['topwebstorymonth'])>0)   
          @foreach($data['topwebstorymonth'] as $topwebstorymonth)
          <tr>
            <td>{{ $topwebstorymonth->title }}</td>
            <td>{{ $topwebstorymonth->viewer }}</td>
          </tr>
          @endforeach
          @else
                    <tr>
                      <td class="text-center" colspan="2">No records found.</td>
                    </tr>
                    @endif
          </tbody>
          </table>
      </div> -->

      </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->
@endsection