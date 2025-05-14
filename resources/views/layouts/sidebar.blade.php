<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all ">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="/" class="navbar-brand">
            <!--Logo start-->
            <!--logo End-->

            <!--Logo start-->
            <div class="logo-main">
                <div class="logo-normal">
                    <img src="{{ asset('assets/images/bpkadlogomini.png') }}" alt="BPKAD Logo"
                        style="width: 50px; margin-bottom: 10px;">
                </div>
                <div class="logo-mini">
                    <img src="{{ asset('assets/images/bpkadlogomini.png') }}" alt="BPKAD Logo"
                        style="width: 50px; margin-bottom: 10px;">
                </div>
            </div>
            <!--logo End-->



            <!-- navbar -->
            <h4 class="logo-title">Analysis</h4>
        </a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list">
            <!-- Sidebar Menu Start -->
            <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Home</span>
                        <span class="mini-icon">-</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page"
                        href="{{ route('dashboard') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="icon-20">
                                <path opacity="0.4"
                                    d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z"
                                    fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Dashboard</span>
                    </a>
                </li>


                {{-- DATABASE --}}

               

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#database-menu" role="button"
                        aria-expanded="false" aria-controls="database-menu">
                        <i class="icon">

                            <svg width="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4"
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"
                                    fill="currentColor"></path>
                                <path
                                    d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Database</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="database-menu" data-bs-parent="#sidebar-menu">
                     
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tahapan.index') ? 'active' : '' }}"
                                href="{{ route('tahapan.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Jadwal Anggaran</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('data') ? 'active' : '' }}"
                                href="{{ route('data') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Data Anggaran</span>
                            </a>
                        </li>

                       



                    </ul>
                </li>
                {{-- DATABASE --}}







            

                {{-- database --}}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#compare-menu" role="button"
                        aria-expanded="false" aria-controls="compare-menu">
                        <i class="icon">

                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4"
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"
                                    fill="currentColor"></path>
                                <path
                                    d="M15.59 7.41L14.17 6l-4.59 4.59L5 6 3.59 7.41 8.17 12l-4.58 4.59L5 18l4.59-4.59L14.17 18l1.42-1.41L10.83 12l4.76-4.59z"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Compare Data</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="compare-menu" data-bs-parent="#sidebar-menu">
                       

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('compare-opd') ? 'active' : '' }}"
                                href="{{ route('compare-opd') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> R </i>
                                <span class="item-name">Belanja OPD</span>
                            </a>
                        </li>
                       
                       

                        

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('compare-rek') ? 'active' : '' }}"
                                href="{{ route('compare-rek') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> R </i>
                                <span class="item-name">Rek Belanja</span>
                            </a>
                        </li>

                        
                       <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('compareDataOpdRek') ? 'active' : '' }}"
                                href="{{ route('compareDataOpdRek') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> R </i>
                                <span class="item-name">Rek Belanja OPD</span>
                            </a>
                        </li>

                         <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('compare.sub-kegiatan') ? 'active' : '' }}"
                                href="{{ route('compare.sub-kegiatan') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> R </i>
                                <span class="item-name">Sub Kegiatan OPD</span>
                            </a>
                        </li>



                    </ul>
                </li>
                {{-- database --}}

                {{-- KERTAS KERJA --}}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#kertaskerja-menu" role="button"
                        aria-expanded="false" aria-controls="horizontal-menu">
                        <i class="icon">

                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4"
                                    d="M19 2H8C6.9 2 6 2.9 6 4V20C6 21.1 6.9 22 8 22H19C20.1 22 21 21.1 21 20V4C21 2.9 20.1 2 19 2ZM19 20H8V4H19V20Z"
                                    fill="currentColor"></path>
                                <path
                                    d="M16 10H11C10.45 10 10 9.55 10 9C10 8.45 10.45 8 11 8H16C16.55 8 17 8.45 17 9C17 9.55 16.55 10 16 10ZM16 14H11C10.45 14 10 13.55 10 13C10 12.45 10.45 12 11 12H16C16.55 12 17 12.45 17 13C17 13.55 16.55 14 16 14ZM16 18H11C10.45 18 10 17.55 10 17C10 16.45 10.45 16 11 16H16C16.55 16 17 16.45 17 17C17 17.55 16.55 18 16 18Z"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Kertas Kerja</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="kertaskerja-menu" data-bs-parent="#sidebar-menu">
                        <!-- Import Data -->



                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('set-rek') ? 'active' : '' }}"
                                href="{{ route('set-rek') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Set Persentase Rek</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('simulasi.set-opd-rek') ? 'active' : '' }}"
                                href="{{ route('simulasi.set-opd-rek') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Set Per OPD Per Rek</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('simulasi.perjalanan-dinas') ? 'active' : '' }}"
                                href="{{ route('simulasi.perjalanan-dinas') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Set Rek PD</span>
                            </a>
                        </li>

                        
                        



                    </ul>
                </li>
                {{-- KERTAS KERJA --}}

                {{-- SIMULASI --}}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#simulasi-menu" role="button"
                        aria-expanded="false" aria-controls="horizontal-menu">
                        <i class="icon">

                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4"
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"
                                    fill="currentColor"></path>
                                <path
                                    d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Simulasi</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="simulasi-menu" data-bs-parent="#sidebar-menu">
                        <!-- Import Data -->



                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('simulasi.rekening') ? 'active' : '' }}"
                                href="{{ route('simulasi.rekening') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Rekap Rekening Belanja</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('simulasi.opdsubkegrekpd') ? 'active' : '' }}"
                                href="{{ route('simulasi.opdsubkegrekpd') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">PD Sub Kegiatan </span>
                            </a>
                        </li>




                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('simulasi.pagu.opd') ? 'active' : '' }}"
                                href="{{ route('simulasi.pagu.opd') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">Per OPD</span>
                            </a>
                        </li>

                    </ul>
                </li>
                {{-- SIMULASI --}}


                {{-- progress --}}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#progress-menu" role="button"
                        aria-expanded="false" aria-controls="horizontal-menu">
                        <i class="icon">

                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4"
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"
                                    fill="currentColor"></path>
                                <path
                                    d="M12 6v6l4 2"
                                    fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Progres Perubahan</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="progress-menu" data-bs-parent="#sidebar-menu">
                        <!-- Import Data -->



                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('progress.index') ? 'active' : '' }}"
                                href="{{ route('progress.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 9h-4V7h-2v4H7v2h4v4h2v-4h4z" />
                                    </svg>
                                </i>
                                <i class="sidenav-mini-icon"> K </i>
                                <span class="item-name">OPD</span>
                            </a>
                        </li>

                        
                    </ul>
                </li>
                {{-- progress end --}}



            </ul>
            <!-- Sidebar Menu End -->
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>
