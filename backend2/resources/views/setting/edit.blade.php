@extends('layouts.app')

@section('content')
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Setting</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Setting</li>
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
            <div class="card-body pt-4">
              
              <!-- Tabs Navigation -->
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="script-tab" data-bs-toggle="tab" data-bs-target="#script" type="button" role="tab" aria-controls="script" aria-selected="false">Script</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="time-spend-tab" data-bs-toggle="tab" data-bs-target="#time-spend" type="button" role="tab" aria-controls="time-spend" aria-selected="true">Time Spend</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="membership-plan-tab" data-bs-toggle="tab" data-bs-target="#membership-plan" type="button" role="tab" aria-controls="membership-plan" aria-selected="true">Membership Plan</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="liveapp-tab" data-bs-toggle="tab" data-bs-target="#liveapp" type="button" role="tab" aria-controls="liveapp" aria-selected="true">Live Script</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="banner-tab" data-bs-toggle="tab" data-bs-target="#banner" type="button" role="tab" aria-controls="banner" aria-selected="true">Banner</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="election-tab" data-bs-toggle="tab" data-bs-target="#election" type="button" role="tab" aria-controls="election" aria-selected="true">Election</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="newfeatures-tab" data-bs-toggle="tab" data-bs-target="#newfeatures" type="button" role="tab" aria-controls="newfeatures" aria-selected="true">New Features</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trending" type="button" role="tab" aria-controls="trending" aria-selected="true">Trending</button>
                </li>
              </ul>

              <!-- Tabs Content -->
              <div class="tab-content pt-3" id="myTabContent">
                
                <!-- SEO Tab -->
                <div class="tab-pane fade show active" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/update') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="seo-tab">
                    <div class="col-md-12">
                      <label for="validationCustom01" class="form-label">Site Title</label>
                      <input type="text" name="siteTitle" class="form-control" id="validationCustom01" value="{{ $data['setting']->siteTitle }}" required>
                      <div class="invalid-feedback">
                        Please enter the Site title.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    
                    <div class="col-md-12">
                      <label for="validationCustom02" class="form-label">Site Email</label>
                      <input type="email" name="email" class="form-control" id="validationCustom02" value="{{ $data['setting']->email }}" >
                      <div class="invalid-feedback">
                        Please enter the email address.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom03" class="form-label">Site Logo</label>
                      <input type="file" id="validationCustom03" name="logo" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the site logo.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->logo)
                        @php
                          $imgurl = config('app.backendnewsurl').'/public/uploads/logo/'.$data['setting']->logo;
                        @endphp
                        <img src="{{$imgurl }}" style="width:80px;"/>
                        <input type="hidden" name="oldImg" value="{{ $data['setting']->logo }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom03" class="form-label">Site Dark Logo</label>
                      <input type="file" id="validationCustom03" name="logo_dark" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the site logo.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->logo_dark)
                        @php
                          $imgurl = config('app.backendnewsurl').'/public/uploads/logo/'.$data['setting']->logo_dark;
                        @endphp
                        <img src="{{$imgurl }}" style="width:80px;"/>
                        <input type="hidden" name="oldImgdark" value="{{ $data['setting']->logo_dark }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Whatsapp Group Link</label>
                      <textarea class="form-control" name="newsdefaulttxt" style="height: 100px">{{ $data['setting']->newsdefaulttxt }}</textarea>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">News Default APP</label>
                      <textarea class="form-control" name="newsdefaultapptxt" style="height: 100px">{{ $data['setting']->newsdefaultapptxt }}</textarea>
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom04" class="form-label">Meta Title</label>
                      <input type="text" name="metatitle" class="form-control" value="{{ $data['setting']->metatitle }}" id="validationCustom04" >
                      <div class="invalid-feedback">
                        Please enter the meta title.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">Meta Keyword</label>
                      <input type="text" name="metakeyword" class="form-control" value="{{ $data['setting']->metakeyword }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Meta Description</label>
                      <textarea class="form-control" name="metadesc" style="height: 100px">{{ $data['setting']->metadesc }}</textarea>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

                <!-- Script Tab -->
                <div class="tab-pane fade" id="script" role="tabpanel" aria-labelledby="script-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/scriptupdate') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="script-tab">
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Header Script</label>
                      <textarea class="form-control" name="headerScript" style="height: 100px">{{ $data['setting']->headerScript }}</textarea>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Body Script</label>
                      <textarea class="form-control" name="bodyScript" style="height: 100px">{{ $data['setting']->bodyScript }}</textarea>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Footer Script</label>
                      <textarea class="form-control" name="footerScript" style="height: 100px">{{ $data['setting']->footerScript }}</textarea>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>
                <!-- Time Spend Tab -->
                <div class="tab-pane fade " id="time-spend" role="tabpanel" aria-labelledby="time-spend-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/pointupdate') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="time-spend-tab">
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">Spend Time (Add time in seconds)</label>
                      <input type="text" name="spendtime" class="form-control" value="{{ $data['setting']->spendtime }}" id="validationCustom44">
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">Points</label>
                      <input type="text" name="point" class="form-control" value="{{ $data['setting']->point }}" id="validationCustom44">
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">Points Info Title</label>
                      <input type="text" name="pointtitle" class="form-control" value="{{ $data['setting']->pointtitle }}">
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Points Info Description</label>
                      <textarea class="form-control" name="pointtxt" style="height: 100px">{{ $data['setting']->pointtxt }}</textarea>
                    </div>
                     <div class="col-md-12">
                      <label for="validationCustom46" class="form-label">Points Information</label>
                      <input type="text" name="pointinfo" class="form-control" value="{{ $data['setting']->pointinfo }}">
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

                <!-- Membership Plan Tab -->
                <div class="tab-pane fade " id="membership-plan" role="tabpanel" aria-labelledby="membership-plan-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/membership') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="membership-plan-tab">
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">One Month Plan Price</label>
                      <input type="text" name="pricemonth" class="form-control" value="{{ $data['setting']->pricemonth }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">One Month Plan Discount (%)</label>
                      <input type="text" name="pricemonthDiscount" class="form-control" value="{{ $data['setting']->pricemonthDiscount }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">One Year Plan Price</label>
                      <input type="text" name="priceyear" class="form-control" value="{{ $data['setting']->priceyear }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">One Year Plan Discount (%)</label>
                      <input type="text" name="priceyearDiscount" class="form-control" value="{{ $data['setting']->priceyearDiscount }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom434" class="form-label">Without Payment Full Content</label>
                      <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="fullcontent_status" name="fullcontent_status"  @if($data['setting']->fullcontent_status == 1) checked @endif>
                          <label class="form-check-label" for="fullcontent_status">Enable</label>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom434" class="form-label">Without Login Full Content</label>
                      <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="fullcontent_login" name="fullcontent_login"  @if($data['setting']->fullcontent_login == 1) checked @endif>
                          <label class="form-check-label" for="fullcontent_login">Enable</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>
                
                 <!-- Live Script Tab -->
                <div class="tab-pane fade" id="liveapp" role="tabpanel" aria-labelledby="liveapp-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/liveapp') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="liveapp-tab">
                   
                    
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Live Script</label>
                      <textarea class="form-control" name="livetvnews" style="height: 100px">{{ $data['setting']->livetvnews }}</textarea>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>
                
                <div class="tab-pane fade show" id="banner" role="tabpanel" aria-labelledby="banner-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/updatebanner') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="banner-tab">
                    
                    <div class="col-md-12">
                      <label for="validationCustom03" class="form-label">Upload Desktop Right Banner</label>
                      <input type="file" id="validationCustom03" name="rightbanner" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the Desktop Right Banner.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->rightbanner)
                        @php
                          $rightbannerurl = config('app.backendnewsurl').'/public/uploads/logo/'.$data['setting']->rightbanner;
                        @endphp
                       <div class="image-wrapper rightbanner1" style="position: relative; display: inline-block;">
                          <!-- Remove Icon -->
                          <button type="button" class="remove-image-btn" onclick="removeImage('rightbanner')" 
                                  style="position: absolute;top: 1px;right: 0px;background: red;color: white;border: none;padding: 0px;border-radius: 50%;height: 20px;width: 20px;font-size: 15px;">
                              &times;
                          </button>
                          <!-- Existing Image -->
                          <img src="{{ $data['setting']->rightbanner }}" style="width: 80px;">
                      </div>
                        <input type="hidden" name="oldrightBanner" id="rightbanner" value="{{ $data['setting']->rightbanner }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom44" class="form-label">Link</label>
                      <input type="text" name="rightbanner_link" class="form-control" value="{{ $data['setting']->rightbanner_link }}" id="validationCustom44" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom453" class="form-label">IOS Link</label>
                      <input type="text" name="rightbanner_ios_link" class="form-control" value="{{ $data['setting']->rightbanner_ios_link }}" id="validationCustom453" >
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rightbanner_status" name="rightbanner_status" @if($data['setting']->rightbanner_status == 1) checked @endif>
                        <label class="form-check-label" for="rightbanner_status">Enable</label>
                    </div>
                    <hr>
                    <div class="col-md-12">
                      <label for="validationCustom031" class="form-label">Upload Desktop Middle Banner</label>
                      <input type="file" id="validationCustom031" name="middlebanner" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the Desktop Middle Banner.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->middlebanner)
                        @php
                          $middlebannerurl = config('app.backendnewsurl').'/public/uploads/logo/'.$data['setting']->middlebanner;
                        @endphp
                        <div class="image-wrapper middlebanner1" style="position: relative; display: inline-block;">
                          <!-- Remove Icon -->
                          <button type="button" class="remove-image-btn" onclick="removeImage('middlebanner')" 
                                  style="position: absolute;top: 1px;right: 0px;background: red;color: white;border: none;padding: 0px;border-radius: 50%;height: 20px;width: 20px;font-size: 15px;">
                              &times;
                          </button>
                          <!-- Existing Image -->
                          <img src="{{ $data['setting']->middlebanner }}" style="width: 80px;">
                      </div>
                        <input type="hidden" name="oldmiddleBanner" id="middlebanner" value="{{ $data['setting']->middlebanner }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom444" class="form-label">Link</label>
                      <input type="text" name="middlebanner_link" class="form-control" value="{{ $data['setting']->middlebanner_link }}" id="validationCustom444" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom45414" class="form-label">IOS Link</label>
                      <input type="text" name="middlebanner_ios_link" class="form-control" value="{{ $data['setting']->middlebanner_ios_link }}" id="validationCustom45414" >
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="middlebanner_status" name="middlebanner_status"  @if($data['setting']->middlebanner_status == 1) checked @endif>
                        <label class="form-check-label" for="middlebanner_status">Enable</label>
                    </div>
                    
                    <hr>
                    <div class="col-md-12">
                      <label for="validationCustom0341" class="form-label">Upload Desktop Matter Banner</label>
                      <input type="file" id="validationCustom0341" name="matterbanner" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the Desktop Matter Banner.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->matterbanner)
                        <div class="image-wrapper matterbanner1" style="position: relative; display: inline-block;">
                          <!-- Remove Icon -->
                          <button type="button" class="remove-image-btn" onclick="removeImage('matterbanner')" 
                                  style="position: absolute;top: 1px;right: 0px;background: red;color: white;border: none;padding: 0px;border-radius: 50%;height: 20px;width: 20px;font-size: 15px;">
                              &times;
                          </button>
                          <!-- Existing Image -->
                          <img src="{{ $data['setting']->matterbanner }}" style="width: 80px;">
                      </div>
                        <input type="hidden" name="oldmatterBanner" id="matterbanner" value="{{ $data['setting']->matterbanner }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom464" class="form-label">Link</label>
                      <input type="text" name="matterbanner_link" class="form-control" value="{{ $data['setting']->matterbanner_link }}" id="validationCustom464" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom465" class="form-label">IOS Link</label>
                      <input type="text" name="matterbanner_ios_link" class="form-control" value="{{ $data['setting']->matterbanner_ios_link }}" id="validationCustom465" >
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="matterbanner_status" name="matterbanner_status"  @if($data['setting']->matterbanner_status == 1) checked @endif>
                        <label class="form-check-label" for="matterbanner_status">Enable</label>
                    </div>
                    
                    <hr>
                    <div class="col-md-12">
                      <label for="validationCustom031" class="form-label">Upload Mobile Banner</label>
                      <input type="file" id="validationCustom031" name="announcement" class="form-control" value="" >
                      <div class="invalid-feedback">
                        Please upload the Mobile Banner.
                      </div>
                      @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                      @if($data['setting']->announcement)
                        @php
                          $announcementurl = config('app.backendnewsurl').'/public/uploads/logo/'.$data['setting']->announcement;
                        @endphp
                        <div class="image-wrapper announcement1" style="position: relative; display: inline-block;">
                          <!-- Remove Icon -->
                          <button type="button" class="remove-image-btn" onclick="removeImage('announcement')" 
                                  style="position: absolute;top: 1px;right: 0px;background: red;color: white;border: none;padding: 0px;border-radius: 50%;height: 20px;width: 20px;font-size: 15px;">
                              &times;
                          </button>
                          <!-- Existing Image -->
                          <img src="{{ $data['setting']->announcement }}" style="width: 80px;">
                      </div>
                        <input type="hidden" name="oldannouncement" id="announcement" value="{{ $data['setting']->announcement }}" />
                      @endif
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom441" class="form-label">Link</label>
                      <input type="text" name="announcement_link" class="form-control" value="{{ $data['setting']->announcement_link }}" id="validationCustom441" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom4541" class="form-label">IOS Link</label>
                      <input type="text" name="announcement_ios_link" class="form-control" value="{{ $data['setting']->announcement_ios_link }}" id="validationCustom4541" >
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="announcement_status" name="announcement_status" 
                        @if($data['setting']->announcement_status == 1) checked @endif>
                        <label class="form-check-label" for="announcement_status">Enable</label>
                    </div>
                   
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

                <div class="tab-pane fade" id="election" role="tabpanel" aria-labelledby="election-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/election') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="election-tab">
                   
                    <div class="col-md-12">
                      <label for="validationCustom4264" class="form-label">Height</label>
                      <input type="text" name="election_height" class="form-control" value="{{ $data['setting']->election_height }}" id="validationCustom4264" >
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom4645" class="form-label">Election Link</label>
                      <input type="text" name="election_link" class="form-control" value="{{ $data['setting']->election_link }}" id="validationCustom4645" >
                    </div>
                    <div class="col-md-12">
                    <label for="validationCustom481" class="form-label">Election Status</label>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="election_status" name="election_status" 
                      @if($data['setting']->election_status == 1) checked @endif >
                        <label class="form-check-label" for="election_status"></label>
                    </div>
                    </div>
                    <div class="col-md-12">
                      <label for="validationCustom4645" class="form-label">Sudoku Link</label>
                      <input type="text" name="sudoku_link" class="form-control" value="{{ $data['setting']->sudoku_link }}" id="validationCustom4645" >
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

                <div class="tab-pane fade" id="newfeatures" role="tabpanel" aria-labelledby="newfeatures-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/newfeatures') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="newfeatures-tab">
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">New Features</label>
                      <textarea class="form-control newfeatures" name="newfeatures" style="height: 100px">{{ $data['setting']->newfeatures }}</textarea>
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="newfeatures_status" name="newfeatures_status" 
                        @if($data['setting']->newfeatures_status == 1) checked @endif>
                        <label class="form-check-label" for="newfeatures_status">Enable Features</label>
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="stockmarket" name="stockmarket" 
                        @if($data['setting']->stockmarket == 1) checked @endif>
                        <label class="form-check-label" for="stockmarket">Enable stockmarket</label>
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="livescore" name="livescore" 
                        @if($data['setting']->livescore == 1) checked @endif>
                        <label class="form-check-label" for="livescore">Enable Livescore</label>
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="rashi_status" name="rashi_status" 
                        @if($data['setting']->rashi_status == 1) checked @endif>
                        <label class="form-check-label" for="rashi_status">Enable Rashi Status</label>
                    </div>
                    <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="podcast_status" name="podcast_status" 
                        @if($data['setting']->podcast_status == 1) checked @endif>
                        <label class="form-check-label" for="podcast_status">Enable Podcast Status</label>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Video show big in Api Home Page</label>
                      <input class="form-control" name="home_page_video_big" value="{{ $data['setting']->home_page_video_big }}"/>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Rashi Language</label>
                      <select name="rashi_language" class="form-control">
                        <option value="gu" @if($data['setting']->rashi_language=='gu') {{ 'selected'}} @endif>Gujarati</option>
                        <option value="hi" @if($data['setting']->rashi_language=='hi') {{ 'selected'}} @endif>Hindi</option>
                        <option value="en" @if($data['setting']->rashi_language=='en') {{ 'selected'}} @endif>English</option>
                      </select>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Rashi Api</label>
                      <input class="form-control" name="rashi_api" value="{{ $data['setting']->rashi_api }}"/>
                    </div>

                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">To Email</label>
                      <input class="form-control" name="toemail" value="{{ $data['setting']->toemail }}"/>
                    </div>

                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">CC Email</label>
                      <input class="form-control" name="ccemail" value="{{ $data['setting']->ccemail }}"/>
                    </div>
                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Apply Post <span style="font-size: 11px;color: #d11414;"> (Note: e.g. Php Devloper,Designer)</span></label>
                      <input class="form-control" name="applypost" value="{{ $data['setting']->applypost }}"/>
                    </div>

                    <div class="col-md-12">
                      <label for="inputPassword" class="col-form-label">Whatsapp channel link </label>
                      <input class="form-control" name="whatsupchannellink" value="{{ $data['setting']->whatsupchannellink }}"/>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

                <div class="tab-pane fade" id="trending" role="tabpanel" aria-labelledby="trending-tab">
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/setting/trending') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="activeTab" value="trending-tab">

                    {{-- Build existing trending rows from JSON --}}
                    @php
                      $trendingItems = [];
                      try {
                        $raw = $data['setting']->trending ?? '[]';
                        $trendingItems = is_array($raw) ? $raw : (json_decode($raw, true) ?? []);
                      } catch(\Exception $e) { $trendingItems = []; }
                      if (empty($trendingItems)) {
                        $trendingItems = [['en' => '', 'gu' => '']];
                      }
                    @endphp

                    <div class="col-md-12" id="trendingRowsContainer">
                      @foreach($trendingItems as $idx => $item)
                      <div class="trending-row mb-2" id="trendingRow{{ $idx }}">
                        <div class="row g-2 align-items-end">
                          <div class="col-md-5">
                            <label class="form-label mb-1">English</label>
                            <input type="text" name="trending[{{ $idx }}][en]" class="form-control"
                                   placeholder="English trending text"
                                   value="{{ $item['en'] ?? '' }}">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label mb-1">Gujarati</label>
                            <input type="text" name="trending[{{ $idx }}][gu]" class="form-control"
                                   placeholder="Gujarati trending text"
                                   value="{{ $item['gu'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            @if($idx > 0)
                              <button type="button" class="btn btn-sm btn-outline-danger"
                                      onclick="removeTrendingRow({{ $idx }})">
                                <i class="bi bi-trash me-1"></i>Delete
                              </button>
                            @endif
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>

                    <div class="col-12 mb-3">
                      <button type="button" class="btn btn-success btn-sm" onclick="addTrendingRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add More
                      </button>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                  </form>
                </div>

              </div><!-- End Tabs Content -->

            </div>
          </div>
        </div>
      </div>
    </section>
</main><!-- End #main -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var activeTab = "{{ session('activeTab', 'seo-tab') }}";
    var tabElement = document.querySelector('button#' + activeTab);
    if(tabElement) {
        tabElement.click();
    }
});
  function removeImage(banner){
    $("#"+banner).val('1');
    $("."+banner+"1").hide();
  }

  // ── Trending rows: Add More / Delete ─────────────────────────────────────
  var trendingRowCount = {{ count($trendingItems ?? [['en'=>'','gu'=>'']]) }};

  function addTrendingRow() {
    var idx = trendingRowCount++;
    var html = '<div class="trending-row mb-2" id="trendingRow' + idx + '">'
      + '<div class="row g-2 align-items-end">'
      + '<div class="col-md-5">'
      + '<label class="form-label">English</label>'
      + '<input type="text" name="trending[' + idx + '][en]" class="form-control" placeholder="English trending text">'
      + '</div>'
      + '<div class="col-md-5">'
      + '<label class="form-label">Gujarati</label>'
      + '<input type="text" name="trending[' + idx + '][gu]" class="form-control" placeholder="Gujarati trending text">'
      + '</div>'
      + '<div class="col-md-2">'
      + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTrendingRow(' + idx + ')">'
      + '<i class="bi bi-trash"></i> Delete</button>'
      + '</div>'
      + '</div>'
      + '</div>';
    document.getElementById('trendingRowsContainer').insertAdjacentHTML('beforeend', html);
  }

  function removeTrendingRow(idx) {
    var el = document.getElementById('trendingRow' + idx);
    if (el) el.remove();
  }
</script>
@endsection
