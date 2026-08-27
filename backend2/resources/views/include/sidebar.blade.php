 <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link {{ Request::is('dashboard') ? '' : 'collapsed' }}" href="{{ config('app.backend_url') }}/dashboard">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      @if(isset($sidebar) && count($sidebar) > 0)
        @foreach($sidebar as $index => $module)
          <li class="nav-item">
            @if(count($module->submenus) > 0)
              {{-- Menu with Submenus --}}
              <a class="nav-link collapsed" data-bs-target="#menu-{{ $module->id }}" data-bs-toggle="collapse" href="#">
                <i class="bi bi-menu-button-wide"></i>
                <span>{{ $module->title }}</span>
                <i class="bi bi-chevron-down ms-auto"></i>
              </a>
              <ul id="menu-{{ $module->id }}" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                @foreach($module->submenus as $submenu)
                  <li>
                    <a href="{{ url($submenu->moduleLink) }}" class="{{ Request::is($submenu->moduleLink) ? 'active' : '' }}">
                      <i class="bi bi-circle"></i>
                      <span>{{ $submenu->title }}</span>
                    </a>
                  </li>
                @endforeach
              </ul>
            @else
              {{-- Menu without Submenus --}}
                @if($module->moduleLink == 'electionresults')
                <a class="nav-link {{ Request::is($module->moduleLink) ? '' : 'collapsed' }}" href="{{ url($module->moduleLink) }}/all">
                  <i class="bi bi-menu-button-wide"></i>
                  <span>{{ $module->title }}</span>
                </a>
                @else 
                <a class="nav-link {{ Request::is($module->moduleLink) ? '' : 'collapsed' }}" href="{{ url($module->moduleLink) }}">
                  <i class="bi bi-menu-button-wide"></i>
                  <span>{{ $module->title }}</span>
                </a>
              @endif
            @endif
          </li>
        @endforeach
      @endif

    </ul>

  </aside><!-- End Sidebar-->