<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="bng-url" content="{{ getWebURL() }}">
        <meta name="bng-api-url" content="{{ getApiURL() }}">
        <meta name="bng-visitor" content="{{ !empty(Auth::user()) ? Auth::user()->id : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bng-ref" content="{{ !empty(Auth::user()) ? Auth::user()->api_token : null }}">

        <!-- ============ Favicon ============ -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- ============ Bootstrap icons ============ -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <!-- ============ Stylesheet ============ -->
        <link rel="stylesheet" type="text/css" href="assets/vendors/css/vendors.min.css">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link rel="stylesheet" type="text/css" href="">
        <link rel="stylesheet" href="{{ asset('templates/admin/assets/css/theme.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}" />

        <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
        <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
        <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

        <title>
@if (!empty($page_title))
			{{ $page_title }}
@else
			Boongo Administration
@endif
		</title>
    </head>

    <body>
        <!--! ================================================================ !-->
        <!--! [Start] Main Content !-->
        <!--! ================================================================ !-->
        <main class="auth-creative-wrapper">
            <div class="auth-creative-inner">
                <div class="creative-card-wrapper">
                    <div class="card my-4 overflow-hidden" style="z-index: 1">
                        <div class="row flex-1 g-0">
                            <div class="col-lg-6 h-100 my-auto order-1 order-lg-0">
                                <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-50 start-50 d-none d-lg-block">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="" class="img-fluid">
                                </div>

@yield('auth-content')

                            </div>
                            <div class="col-lg-6 bg-primary order-0 order-lg-1">
                                <div class="h-100 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('assets/img/auth-user.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!--! ================================================================ !-->
        <!--! [End] Main Content !-->
        <!--! ================================================================ !-->

        <!-- ============ JavaScript ============ -->
        <!-- Bootstrap core JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Vendors JS -->
        <script src="{{ asset('templates/admin/assets/vendors/js/vendors.min.js') }}"></script>
        <!--Apps Init -->
        <script src="{{ asset('templates/admin/assets/js/common-init.min.js') }}"></script>
        <!-- Theme Customizer -->
        <script src="{{ asset('templates/admin/assets/js/theme-customizer-init.min.js') }}"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('assets/js/script.custom.js') }}"></script>
    </body>
</html>
