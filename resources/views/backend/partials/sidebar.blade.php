<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset($settings->logo ?? 'default/logo.svg') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.svg') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.svg') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.svg') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>
            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-house side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <!-- <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.subcategory.*') ? 'has-link active' : '' }}" href="{{ route('admin.subcategory.index') }}">
                        <i class="fa-solid fa-list-check side-menu__icon"></i>
                        <span class="side-menu__label">Sub Category</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.post.*') ? 'has-link active' : '' }}" href="{{ route('admin.post.index') }}">
                        <i class="fa-solid fa-blog side-menu__icon"></i>
                        <span class="side-menu__label">Post</span>
                    </a>
                </li> -->
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.news.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.news.index') }}">
                        <i class="fa-solid fa-newspaper side-menu__icon"></i>
                        <span class="side-menu__label">News</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.blog.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.blog.index') }}">
                        <i class="fa-solid fa-blog side-menu__icon"></i>
                        <span class="side-menu__label">Blog</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.leader.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.leader.index') }}">
                        <i class="fa-solid fa-chess-king side-menu__icon"></i>
                        <span class="side-menu__label">Leader</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.member.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-chess-king side-menu__icon"></i>
                        <span class="side-menu__label">CMC</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.member.index', ['type' => 'member']) }}"
                                class="slide-item">Member</a></li>
                        <li><a href="{{ route('admin.member.index', ['type' => 'representative']) }}"
                                class="slide-item">Representative</a></li>
                        <li><a href="{{ route('admin.member.index', ['type' => 'senator']) }}"
                                class="slide-item">Senator</a></li>
                    </ul>
                </li>


                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.image-gallery.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.image-gallery.index') }}">
                        <i class="fa-solid fa-image side-menu__icon"></i>
                        <span class="side-menu__label">Photo Gallery</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.event.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.event.index') }}">
                        <i class="fa-solid fa-calendar-days side-menu__icon"></i>
                        <span class="side-menu__label">Events</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.survey.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.survey.index') }}">
                        <i class="fa-solid fa-gavel side-menu__icon"></i>
                        <span class="side-menu__label">Survey</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.category.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.category.index') }}">
                        <i class="fa-solid fa-list side-menu__icon"></i>
                        <span class="side-menu__label">Category</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.product.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.product.index') }}">
                        <i class="fa-solid fa-parachute-box side-menu__icon"></i>
                        <span class="side-menu__label">Product</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.order.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.order.index') }}">
                        <i class="fa-solid fa-credit-card side-menu__icon"></i>
                        <span class="side-menu__label">Order History</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.transaction.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.transaction.index') }}">
                        <i class="fa-solid fa-money-bill-transfer side-menu__icon"></i>
                        <span class="side-menu__label">Transactions</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.donation.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.donation.index') }}">
                        <i class="fa-solid fa-hand-holding-dollar side-menu__icon"></i>
                        <span class="side-menu__label">Donations</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.area.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.area.index') }}">
                        <i class="fa-solid fa-map side-menu__icon"></i>
                        <span class="side-menu__label">Area</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.branch.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.branch.index') }}">
                        <i class="fa-solid fa-map-location-dot side-menu__icon"></i>
                        <span class="side-menu__label">Branch</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.party.police.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.party.police.index') }}">
                        <i class="fa-solid fa-building-shield side-menu__icon"></i>
                        <span class="side-menu__label">Party Policy</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.party.resource.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.party.resource.index') }}">
                        <i class="fa-solid fa-building-shield side-menu__icon"></i>
                        <span class="side-menu__label">Party Resource</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('ajax.gallery.*') ? 'has-link active' : '' }}"
                        href="{{ route('ajax.gallery.index') }}">
                        <i class="fa-solid fa-image side-menu__icon"></i>
                        <span class="side-menu__label">Image Gallery</span>
                    </a>
                </li>







                <li>
                    <h3>Components</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.contact.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.contact.index') }}">
                        <i class="fa-solid fa-address-card side-menu__icon"></i>
                        <span class="side-menu__label">Contact</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.subscriber.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.subscriber.index') }}">
                        <i class="fa-solid fa-people-group side-menu__icon"></i>
                        <span class="side-menu__label">Subscriber</span>
                    </a>
                </li>
                <!-- <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.chat.*') ? 'has-link active' : '' }}" href="{{ route('admin.chat.index') }}">
                        <i class="fa-brands fa-rocketchat side-menu__icon"></i>
                        <span class="side-menu__label">Chat</span>
                    </a>
                </li> -->
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-sheet-plastic side-menu__icon"></i>
                        <span class="side-menu__label">Apply</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.focus.index') }}" class="slide-item">Area Of Focus</a></li>
                        <li><a href="{{ route('admin.apply.index') }}" class="slide-item">Apply</a></li>
                    </ul>
                </li>
                <!-- <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32">
                            <rect width="416" height="416" rx="48" ry="48" />
                            <path d="m192 256 128 0" />
                        </svg>
                        <span class="side-menu__label">User Access</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.users.index') }}" class="slide-item">User</a></li>
                        <li><a href="{{ route('admin.roles.index') }}" class="slide-item">Roll</a></li>
                        <li><a href="{{ route('admin.permissions.index') }}" class="slide-item">Permission</a></li>
                    </ul>
                </li> -->
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.setting.general.index') }}" class="slide-item">General
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.env.index') }}" class="slide-item">Environment
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.profile.index') }}" class="slide-item">Profile
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.mail.index') }}" class="slide-item">Mail Settings</a>
                        </li>
                        <li><a href="{{ route('admin.setting.stripe.index') }}" class="slide-item">Stripe
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.firebase.index') }}" class="slide-item">Firebase
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.social.index') }}" class="slide-item">Social
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.google.map.index') }}" class="slide-item">Google Map
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.captcha.index') }}" class="slide-item">Captcha
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.other.index') }}" class="slide-item">Other Settings</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <h3>Components</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.page.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.page.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path d="M15 14l-5-5-5 5v-3l10 -10z" />
                        </svg>
                        <span class="side-menu__label">Dynamic Page</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.social.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.social.index') }}">
                        <i class="fa-solid fa-link side-menu__icon"></i>
                        <span class="side-menu__label">Social Link</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.faq.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.faq.index') }}">
                        <i class="fa-solid fa-clipboard-question side-menu__icon"></i>
                        <span class="side-menu__label">FAQ</span>
                    </a>
                </li>
                <li>
                    <h3>Web CMS</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Home Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.home.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.about_cmc.index') }}" class="slide-item">About CMC
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.news.index') }}" class="slide-item">News Section</a>
                        </li>
                        <li><a href="{{ route('admin.cms.home.leaders.index') }}" class="slide-item">Leaders
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.events.index') }}" class="slide-item">Events
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.donate.index') }}" class="slide-item">Donate
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Who We Are Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.whoweare.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.whoweare.committed.index') }}" class="slide-item">Committed
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.whoweare.difference.index') }}"
                                class="slide-item">Difference Section</a></li>
                        <li><a href="{{ route('admin.cms.whoweare.mission.index') }}" class="slide-item">Mission
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.whoweare.vission.index') }}" class="slide-item">Vission
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.whoweare.discover_values.index') }}"
                                class="slide-item">Discover Values Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Meet Our Leaders Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.meeetourleaders.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.meeetourleaders.executive.index') }}"
                                class="slide-item">Executive Section</a></li>
                        <li><a href="{{ route('admin.cms.meeetourleaders.join_cmc.index') }}" class="slide-item">Join
                                CMC Section</a></li>
                        <li><a href="{{ route('admin.cms.meeetourleaders.representative.index') }}"
                                class="slide-item">Representative Section</a></li>
                        <li><a href="{{ route('admin.cms.meeetourleaders.senators.index') }}"
                                class="slide-item">Senator Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Leader Details Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.meeetourleadersdetails.banner.index') }}"
                                class="slide-item">Banner Section</a></li>
                        <li><a href="{{ route('admin.cms.meeetourleadersdetails.relatedpost.index') }}"
                                class="slide-item">Related Post Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Polices Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.polices.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Events Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.events.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.events.gallery.index') }}" class="slide-item">Gallery
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Events Details Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.events_details.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.events_details.relatedpost.index') }}"
                                class="slide-item">Related Post Section</a></li>
                        <li><a href="{{ route('admin.cms.events_details.register.index') }}"
                                class="slide-item">Register Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">News Page</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.news.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.news.list.index') }}" class="slide-item">List Section</a>
                        </li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">News Details Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.news_details.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.news_details.relatedpost.index') }}"
                                class="slide-item">Related Post Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Blogs Page</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.blogs.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.blogs.list.index') }}" class="slide-item">List Section</a>
                        </li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Blogs Details Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.blogs_details.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Shop Page</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.shop.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Shop Details Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.shop_details.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.shop_details.relatedpost.index') }}"
                                class="slide-item">Related Post Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Contact Us Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.contact_us.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.contact_us.form.index') }}" class="slide-item">Form
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.contact_us.office.index') }}" class="slide-item">Office
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Join CMC Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.join_cmc.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.join_cmc.form.index') }}" class="slide-item">Form
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Donate Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.donate.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.donate.form.index') }}" class="slide-item">Form Section</a>
                        </li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Repr..ive Apply Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.representative.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.representative.form.index') }}" class="slide-item">Form
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Senator Apply Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.senator.banner.index') }}" class="slide-item">Banner
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.senator.form.index') }}" class="slide-item">Form
                                Section</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">Common Part of Page</span><i
                            class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.common.footer.index') }}" class="slide-item">Footer</a>
                        </li>
                        <li><a href="{{ route('admin.cms.common.faq.index') }}" class="slide-item">FAQ</a></li>
                        <li><a href="{{ route('admin.cms.common.subscribe.index') }}"
                                class="slide-item">Subscribe</a></li>
                        <li><a href="{{ route('admin.cms.common.signin.index') }}" class="slide-item">Signin
                                Model</a></li>
                        <li><a href="{{ route('admin.cms.common.help.index') }}" class="slide-item">Help</a></li>
                    </ul>
                </li>
                <li>
                    <h3>App CMS</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.cms.app.welcome.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.cms.app.welcome.screen.index') }}">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Wellcome Screen</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.cms.app.home.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.cms.app.home.screen.index') }}">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Home Screen</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.cms.app.donation.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.cms.app.donation.screen.index') }}">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Donation Screen</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ Request::routeIs('admin.cms.app.party_policy.*') ? 'has-link active' : '' }}"
                        href="{{ route('admin.cms.app.party_policy.screen.index') }}">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Party Policy Screen</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Onboarding Screen</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.app.onboarding.first.index') }}" class="slide-item">First
                                Screen</a></li>
                        <li><a href="{{ route('admin.cms.app.onboarding.second.index') }}" class="slide-item">Second
                                Screen</a></li>
                        <li><a href="{{ route('admin.cms.app.onboarding.third.index') }}" class="slide-item">Third
                                Screen</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-brands fa-app-store-ios side-menu__icon"></i>
                        <span class="side-menu__label">Who We Are Screen</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.app.who_we_are.committed.index') }}"
                                class="slide-item">Committed Section</a></li>
                        <li><a href="{{ route('admin.cms.app.who_we_are.different.index') }}"
                                class="slide-item">Different Section</a></li>
                        <li><a href="{{ route('admin.cms.app.who_we_are.mession_and_vision.index') }}"
                                class="slide-item">Mission and Vission Section</a></li>
                        <li><a href="{{ route('admin.cms.app.who_we_are.discover.index') }}"
                                class="slide-item">Discover Section</a></li>
                    </ul>
                </li>
                <li>
                    <h3>END</h3>
                </li>
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->
