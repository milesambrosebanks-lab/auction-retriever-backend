<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset(settings('logo') ?? 'default/logo.svg') }}"
                     id="header-brand-logo" alt="logo"
                     width="{{ settings('logo_width') ?? 67 }}"
                     height="{{ settings('logo_height') ?? 67 }}">
            </a>
        </div>

        {{-- <div class="main-sidemenu">
            <input class="form-control form-control-dark w-100 border-0"
                   id="menuSearching" type="text"
                   placeholder="Search menu..." aria-label="Search">
            <ul id="customMenulist" class="side-menu"></ul>
        </div> --}}

        <div class="main-sidemenu">
            <ul class="side-menu mt-2">

                {{-- ── MAIN ──────────────────────────────────── --}}
                <li><h3>Main</h3></li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.dashboard') ? 'has-link active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-house side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.users.*') ? 'has-link active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="fa-solid fa-users side-menu__icon"></i>
                        <span class="side-menu__label">Users</span>
                    </a>
                </li>

                {{-- ── AUCTION ───────────────────────────────── --}}
                <li><h3>Auction</h3></li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.auction.listings.index') ? 'has-link active' : '' }}"
                       href="{{ route('admin.auction.listings.index') }}">
                        <i class="fa-solid fa-list side-menu__icon"></i>
                        <span class="side-menu__label">Listings</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.auction.listings.scrape.logs') ? 'has-link active' : '' }}"
                       href="{{ route('admin.auction.listings.scrape.logs') }}">
                        <i class="fa-solid fa-rotate side-menu__icon"></i>
                        <span class="side-menu__label">Extraction Logs</span>
                    </a>
                </li>

                {{-- ── BILLING ───────────────────────────────── --}}
                <li><h3>Billing</h3></li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.my_plan.*') ? 'has-link active' : '' }}"
                       href="{{ route('admin.my_plan.index') }}">
                        <i class="fa-solid fa-tags side-menu__icon"></i>
                        <span class="side-menu__label">Subscriptions Plan</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.subscriptions.*') ? 'has-link active' : '' }}"
                       href="{{ route('admin.subscriptions.index') }}">
                        <i class="fa-solid fa-star side-menu__icon"></i>
                        <span class="side-menu__label">Subscribers</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.transaction.*') ? 'has-link active' : '' }}"
                       href="{{ route('admin.transaction.index') }}">
                        <i class="fa-solid fa-money-bill-transfer side-menu__icon"></i>
                        <span class="side-menu__label">Revenue</span>
                    </a>
                </li>

                {{-- ── SYSTEM ────────────────────────────────── --}}
                @role('admin')
                <li><h3>System</h3></li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                       data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-gear side-menu__icon"></i>
                        <span class="side-menu__label">Settings</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li>
                            <a href="{{ route('admin.setting.general.index') }}"
                               class="slide-item">General Settings</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.setting.logo.index') }}"
                               class="slide-item">Logo Settings</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.setting.profile.index') }}"
                               class="slide-item">Profile Settings</a>
                        </li>
                    </ul>
                </li>
                @endrole

                {{-- ── DIVIDER + LOGOUT ──────────────────────── --}}
                <li class="slide"><hr/></li>

                <li class="slide">
                    <a class="side-menu__item text-danger"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket side-menu__icon"></i>
                        <span class="side-menu__label">Log out</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}"
                          method="POST" style="display:none;">
                        @csrf
                    </form>
                </li>

                <li class="slide"><hr/></li>

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/>
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->

<script>
    const menuSearchInput  = document.getElementById('menuSearching');
    const customMenuList   = document.getElementById('customMenulist');
    const menus = @json(App\Models\Menu::where('status', 1)->orderBy('id', 'DESC')->get());

    function sideMenu() {
        menus.forEach(menu => {
            if (menu.name.toLowerCase().includes(menuSearchInput.value.toLowerCase())) {
                customMenuList.innerHTML += `
                    <li class="slide">
                        <a class="side-menu__item" href="#">
                            <i class="fa-solid fa-bars side-menu__icon"></i>
                            <span class="side-menu__label">${menu.name}</span>
                        </a>
                    </li>
                `;
            }
        });
    }

    menuSearchInput.addEventListener('input', function () {
        customMenuList.innerHTML = '';
        if (menuSearchInput.value.trim() !== '') {
            sideMenu();
        }
    });
</script>
