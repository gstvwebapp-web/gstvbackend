<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

<div class="d-flex align-items-center justify-content-between">
  <a href="{{ url('/') }}" class="logo d-flex align-items-center">
    <img src="{{ URL::asset('public/assets/img/logo.png') }}" alt="">
  </a>
  <i class="bi bi-list toggle-sidebar-btn"></i>
</div><!-- End Logo -->

<!--<div class="search-bar">
  <form class="search-form d-flex align-items-center" method="POST" action="#">
    <input type="text" name="query" placeholder="Search" title="Enter search keyword">
    <button type="submit" title="Search"><i class="bi bi-search"></i></button>
  </form>
</div> End Search Bar -->
<style>
  .sizecls { color:#198754; font-weight:bold; }
</style>
<nav class="header-nav ms-auto">
  <ul class="d-flex align-items-center">
	<li class="nav-item dropdown pe-3"><a class="btn float-end addCls btn-success" href="{{ config('app.front_url') }}/backend2/redisclear">Clear Cache</a></li>
  <!--<li class="nav-item dropdown pe-3"><a class="btn float-end addCls btn-success" href="{{ url('/news/cloudflarecache') }}">Clear Cache</a></li>-->
    <li class="nav-item dropdown pe-3">

      <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
      @auth
          @if(Auth::user()->profileImg != '')
              <img src="{{ URL::asset('public/uploads/profile/' . Auth::user()->profileImg) }}" alt="Profile" class="rounded-circle">
          @else
              <img src="{{ URL::asset('public/assets/img/user.png') }}" alt="Profile" class="rounded-circle">
          @endif
      @else
          <img src="{{ URL::asset('public/assets/img/user.png') }}" alt="Profile" class="rounded-circle">
      @endauth
  </a>

      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
         @auth
         <h6>{{ ucfirst(Auth::user()->name) }}</h6>
        @endauth
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>

        <!--<li>
          <a class="dropdown-item d-flex align-items-center" href="{{ url('/') }}">
            <i class="bi bi-person"></i>
            <span>My Profile</span>
          </a>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>-->
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{url('/viewexport')}}"> <i class="bi bi-download"></i><span>Today Download Report</span></a>
        </li>
        <li>
          <form method="post" action="{{ route('logout') }}">
              @csrf
              <button class="dropdown-item d-flex align-items-center" type="submit"><i class="bi bi-box-arrow-right"></i>
            <span>Sign Out</span></button>
          </form>
        </li>

      </ul><!-- End Profile Dropdown Items -->
    </li><!-- End Profile Nav -->

  </ul>
</nav><!-- End Icons Navigation -->

</header><!-- End Header -->