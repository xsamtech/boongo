<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="bng-url" content="{{ getWebURL() }}">
        <meta name="bng-api-url" content="{{ getApiURL() }}">
        <meta name="bng-visitor" content="{{ !empty(Auth::user()) ? Auth::user()->id : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bng-ref" content="{{ !empty(Auth::user()) ? Auth::user()->api_token : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- ============ Favicon ============ -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- ============ Font Icons Files ============ -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">

 		<!-- ============ Google font ============ -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

        <!-- ============ Addons CSS Files ============ -->
 		<!-- Bootstrap -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/bootstrap/css/bootstrap.min.css') }}"/>
 		<!-- Slick -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/slick/css/slick.css') }}"/>
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/slick/css/slick-theme.css') }}"/>
 		<!-- nouislider -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/nouislider/css/nouislider.min.css') }}"/>
 		<!-- Other -->
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/perfect-scrollbar/css/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/dataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.css') }}">

        <!-- ============ Electro CSS File ============ -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/style.electro.css') }}"/>

 		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
 		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
 		<!--[if lt IE 9]>
 		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
 		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
 		<![endif]-->

        <!-- ============ Custom CSS ============ -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}">
@if (request()->has('app_id') || !empty($exception))
        <style>
            .detect-webview { display: none;!important }
        </style>
@endif
        <style>
            .d-none { display: none;!important }
        </style>

        <title>
@if (!empty($exception))
            {{ $exception->getStatusCode() . ' - ' . __('notifications.' . $exception->getStatusCode() . '_title') }}
@else
    @if (!empty($error_title) || \Session::has('error_message') || \Session::has('error_message_login'))
            {{ !empty($error_title) ? $error_title : (\Session::has('error_message_login') ? preg_match('/~/', \Session::get('error_message_login')) ? explode(', ', explode('~', \Session::get('error_message_login'))[0])[2] : \Session::get('error_message_login') : (\Session::has('error_message') ? (preg_match('/~/', \Session::get('error_message')) ? explode('-', explode('~', \Session::get('error_message'))[0])[2] : \Session::get('error_message')) : '')) }}
    @endif

    @if (empty($error_title) && Session::get('error_message') == null)
        @if (!empty($children))
            @lang('auth.select-your-profile')
        @endif
        @if (Route::is('login'))
            @if (request()->has('check_param'))
                @if (request()->get('check_param') == 'email')
            @lang('auth.verified-email')
                @endif
                @if (request()->get('check_param') == 'phone')
            @lang('auth.verified-phone')
                @endif
            @else
            @lang('auth.login')
            @endif
		@endif

		@if (Route::is('register') || !empty($request->temporary_user_id))
            @if (!empty($token_sent))
            @lang('auth.otp-code')
            @else
                @if (!empty($temporary_user))
            @lang('miscellaneous.account.personal_infos.title')
                @else
                    @if (!empty($request->redirect))
                        @if (request()->has('check'))
                            @if (request()->get('check') == 'email')
            @lang('auth.verify-email')
                            @endif
                            @if (request()->get('check') == 'phone')
            @lang('auth.verify-phone')
                            @endif
                        @else
            @lang('auth.reset-password')
                        @endif
                    @else
            @lang('auth.register')
                    @endif
                @endif
            @endif
		@endif

		@if (Route::is('password.request') || !empty($former_password))
            @if (request()->has('check'))
                @if (request()->get('check') == 'email')
                    @lang('auth.verify-email')
                @endif
                @if (request()->get('check') == 'phone')
                    @lang('auth.verify-phone')
                @endif
            @else
                @lang('auth.reset-password')
            @endif
		@endif

        @if (!empty($token_sent))
            @lang('auth.otp-code')
        @endif

        @if (Route::is('home'))
            @lang('miscellaneous.welcome_title')
        @endif
        @if (Route::is('about.home'))
            @lang('miscellaneous.menu.about')
        @endif
        @if (Route::is('book.home'))
            @lang('miscellaneous.menu.public.books')
        @endif
        @if (Route::is('newspaper.home'))
            @lang('miscellaneous.menu.public.mag_newspapers')
        @endif
        @if (Route::is('map.home'))
            @lang('miscellaneous.menu.public.mapping')
        @endif
        @if (Route::is('media.home'))
            @lang('miscellaneous.menu.public.medias')
        @endif
        @if (Route::is('about.entity') || Route::is('account.entity') || Route::is('book.datas') || Route::is('newspaper.datas') || Route::is('map.datas') || Route::is('media.datas'))
            {{ $entity_title }}
        @endif
    @endif
@endif
        </title>
    </head>
	<body>
        <!-- ### Crop user image ### -->
        <div class="modal fade" id="cropModalUser" tabindex="-1" role="dialog" aria-labelledby="cropModalUserLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalUserLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-sm-0 mb-4">
                                    <div class="bg-image">
                                        <img src="" id="retrieved_image" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light border rounded-pill" data-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_avatar" class="btn btn-primary rounded-pill"data-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ### Crop other user image ### -->
        <div class="modal fade" id="cropModalOtherUser" tabindex="-1" role="dialog" aria-labelledby="cropModalOtherUserLabel" aria-hidden="true" data-bs-backdrop="{{ Route::is('branch.home') ? 'static' : 'true' }}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalOtherUserLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-sm-0 mb-4">
                                    <div class="bg-image">
                                        <img src="" id="retrieved_image_other_user" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light border rounded-pill" data-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_other_user" class="btn btn-primary rounded-pill" data-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MODALS-->

		<!-- HEADER -->
		<header class="detect-webview">
            <span class="menu-sidebar2__content d-none"></span>
			<!-- TOP HEADER -->
			<div id="top-header" class="detect-webview">
				<div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <ul class="header-links pull-left top-contact">
                            <li><a href="tel:+243815737600"><i class="fa-solid fa-phone"></i> +243 815 737 600</a></li>
                            <li><a href="mailto:contact@boongo7.com"><i class="fa-solid fa-envelope"></i> contact@boongo7.com</a></li>
                        </ul>
                        <ul class="header-links pull-right">
                            <li><a href="{{ Auth::check() ? route('account') : route('login') }}"><i class="fa-solid {{ Auth::check() ? 'fa-user' :'fa-power-off' }}"></i> {{ Auth::check() ? __('miscellaneous.menu.account.title') : __('miscellaneous.login_title1') }}</a></li>
                        </ul>
                    </div>
				</div>
			</div>
			<!-- /TOP HEADER -->

			<!-- MAIN HEADER -->
			<div id="header">
				<!-- container -->
				<div class="container">
					<!-- row -->
					<div class="row">
						<!-- LOGO -->
						<div class="col-md-3 mb-sm-0 mb-3">
							<div class="header-logo" style="margin-top: 10px;">
								<a href="{{ route('home') }}" class="logo">
									<img src="{{ asset('assets/img/brand.png') }}" alt="logo" width="160" class="align-bottom">
								</a>
							</div>
						</div>
						<!-- /LOGO -->

						<!-- SEARCH BAR -->
						<div class="col-md-6">
							<div class="header-search">
								<form>
									<select class="input-select">
										<option value="0">@lang('miscellaneous.all_categories')</option>
										{{-- <option value="1">Category 01</option>
										<option value="1">Category 02</option> --}}
									</select>
									<input class="input" placeholder="@lang('miscellaneous.search_input')">
									<button class="search-btn"><i class="fa-solid fa-search"></i></button>
								</form>
							</div>
						</div>
						<!-- /SEARCH BAR -->

						<!-- ACCOUNT -->
						<div class="col-md-3 clearfix">
							<div class="header-ctn">
@if (Auth::check())
								<!-- Wishlist -->
								<div>
									<a href="#">
										<i class="fa fa-heart-o"></i>
										<span>@lang('miscellaneous.public.your_subscriptions')</span>
										<div class="qty">2</div>
									</a>
								</div>
								<!-- /Wishlist -->

								<!-- Cart -->
								<div class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
										<i class="fa fa-shopping-cart"></i>
										<span>@lang('miscellaneous.public.your_cart')</span>
										<div class="qty">3</div>
									</a>
									<div class="cart-dropdown">
										<div class="cart-list">
											<div class="product-widget">
												<div class="product-img">
													<img src="" alt="">
												</div>
												<div class="product-body">
													<h3 class="product-name"><a href="#">product name goes here</a></h3>
													<h4 class="product-price"><span class="qty">1x</span>$980.00</h4>
												</div>
												<button class="delete"><i class="fa fa-close"></i></button>
											</div>

											<div class="product-widget">
												<div class="product-img">
													<img src="" alt="">
												</div>
												<div class="product-body">
													<h3 class="product-name"><a href="#">product name goes here</a></h3>
													<h4 class="product-price"><span class="qty">3x</span>$980.00</h4>
												</div>
												<button class="delete"><i class="fa fa-close"></i></button>
											</div>
										</div>
										<div class="cart-summary">
											<small>3 Item(s) selected</small>
											<h5>@lang('miscellaneous.public.subtotal') $2940.00</h5>
										</div>
										<div class="cart-btns">
											<a href="#">@lang('miscellaneous.public.view_cart')</a>
											<a href="#">@lang('miscellaneous.public.checkout')<i class="fa fa-arrow-circle-right ms-2"></i></a>
										</div>
									</div>
								</div>
								<!-- /Cart -->
@endif

								<!-- Menu Toogle -->
								<div class="menu-toggle">
									<a href="#">
										<i class="fa fa-bars"></i>
										<span>@lang('miscellaneous.menu_toggle')</span>
									</a>
								</div>
								<!-- /Menu Toogle -->
							</div>
						</div>
						<!-- /ACCOUNT -->
					</div>
					<!-- row -->
				</div>
				<!-- container -->
			</div>
			<!-- /MAIN HEADER -->
		</header>
		<!-- /HEADER -->

		<!-- NAVIGATION -->
		<nav id="navigation" class="detect-webview">
			<!-- container -->
			<div class="container">
				<!-- responsive-nav -->
				<div id="responsive-nav">
					<!-- NAV -->
					<ul class="main-nav nav navbar-nav">
						<li class="{{ Route::is('home') ? 'active' : '' }}"><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
						<li class="{{ Route::is('about.home') ? 'active' : '' }}"><a href="{{ route('about.home') }}">@lang('miscellaneous.menu.about')</a></li>
						<li class="{{ Route::is('book.home') || Route::is('book.datas') ? 'active' : '' }}"><a href="{{ route('book.home') }}">@lang('miscellaneous.menu.public.books')</a></li>
						<li class="{{ Route::is('newspaper.home') || Route::is('newspaper.datas') ? 'active' : '' }}"><a href="{{ route('newspaper.home') }}">@lang('miscellaneous.menu.public.mag_newspapers')</a></li>
						<li class="{{ Route::is('map.home') || Route::is('map.datas') ? 'active' : '' }}"><a href="{{ route('map.home') }}">@lang('miscellaneous.menu.public.mapping')</a></li>
						<li class="{{ Route::is('media.home') || Route::is('media.datas') ? 'active' : '' }}"><a href="{{ route('media.home') }}">@lang('miscellaneous.menu.public.medias')</a></li>
					</ul>
					<!-- /NAV -->
				</div>
				<!-- /responsive-nav -->
			</div>
			<!-- /container -->
		</nav>
		<!-- /NAVIGATION -->

@include('partials.breadcrumb')

@yield('guest-content')

		<!-- FOOTER -->
		<footer id="footer" class="mt-5{{ Route::is('transaction.waiting') || Route::is('transaction.message') || !empty($exception) ? ' detect-webview' : '' }}">
			<!-- top footer -->
			<div class="section detect-webview">
				<!-- container -->
				<div class="container">
					<!-- row -->
					<div class="row">
						<div class="col-lg-6 col-sm-7">
							<div class="footer">
								<h3 class="footer-title">@lang('miscellaneous.public.about.enterprise_title')</h3>
								<p class="mb-4 pl-lg-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut.</p>
								<ul class="footer-links">
									<li><a href="tel:+243815737600"><i class="fa-solid fa-phone"></i>+243 815 737 600</a></li>
									<li><a href="mailto:contact@boongo7.com"><i class="fa-solid fa-envelope"></i>contact@boongo7.com</a></li>
								</ul>
							</div>
						</div>

						<div class="col-lg-3 col-sm-5">
							<div class="footer">
								<h3 class="footer-title">@lang('miscellaneous.public.footer.useful_links')</h3>
								<ul class="footer-links">
									<li><a href="{{ route('about.home') }}">@lang('miscellaneous.menu.about')</a></li>
									<li><a href="{{ route('about.entity', ['entity' => 'contact']) }}">@lang('miscellaneous.menu.contact')</a></li>
									<li><a href="{{ route('about.entity', ['entity' => 'privacy_policy']) }}">@lang('miscellaneous.menu.privacy_policy')</a></li>
									<li><a href="{{ route('about.entity', ['entity' => 'terms_of_use']) }}">@lang('miscellaneous.menu.terms_of_use')</a></li>
								</ul>
							</div>
						</div>

						<div class="col-lg-3 col-sm-5">
							<div class="footer">
								<h3 class="footer-title">@lang('miscellaneous.public.footer.services')</h3>
								<ul class="footer-links">
@if (Auth::check())
									<li><a href="{{ route('account') }}">@lang('miscellaneous.menu.account.title')</a></li>
									<li><a href="{{ route('account.entity', ['entity' => 'orders']) }}">@lang('miscellaneous.public.view_cart')</a></li>
									<li><a href="{{ route('account.entity', ['entity' => 'works']) }}">@lang('miscellaneous.menu.account.works')</a></li>
@else
									<li><a href="{{ route('login') }}">@lang('miscellaneous.login_title1')</a></li>
									<li><a href="{{ route('register') }}">@lang('miscellaneous.register_title1')</a></li>
@endif
								</ul>
							</div>
						</div>
					</div>
					<!-- /row -->
				</div>
				<!-- /container -->
			</div>
			<!-- /top footer -->

			<!-- bottom footer -->
			<div id="bottom-footer" class="section py-0">
				<div class="container">
					<!-- row -->
					<div class="row py-0">
						<div class="col-md-12 text-center">
							<span class="copyright">
								Copyright &copy;{{ date('Y') }}</script> @lang('miscellaneous.all_right_reserved') | Designed by <a href="https://xsamtech.com" target="_blank">Xsam Technologies</a>
							</span>
						</div>
					</div>
					<!-- /row -->
				</div>
				<!-- /container -->
			</div>
			<!-- /bottom footer -->
		</footer>
		<!-- /FOOTER -->

        <span id="btnBackTop" class="btn btn-floating btn-primary d-none" style="position: fixed; bottom: 2rem; right: 2rem;"><i class="fa-solid fa-chevron-up"></i></span>
		<!-- jQuery Plugins -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/addons/electro/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.min.js') }}"></script>
		<script src="{{ asset('assets/addons/electro/slick/js/slick.min.js') }}"></script>
		<script src="{{ asset('assets/addons/electro/nouislider/js/nouislider.min.js') }}"></script>
		<script src="{{ asset('assets/addons/custom/jquery/jquery.zoom/jquery.zoom.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/dataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/scroll4ever/js/jquery.scroll4ever.js') }}"></script>
		<script src="{{ asset('assets/js/script.electro.js') }}"></script>
		<script src="{{ asset('assets/js/script.custom2.js') }}"></script>
		<script type="text/javascript">
			$(function () {
				/* On select change, update de country phone code */
				$('#select_country1').on('change', function () {
					var countryData = $(this).val();
					var countryDataArray = countryData.split('-');
					// Get ID and Phone code from splitted data
					var countryId = countryDataArray[1];
					var countryPhoneCode = countryDataArray[0];

					$('#phone_code_text1 .text-value').text(countryPhoneCode);
					$('#country_id1').val(countryId);
					$('#phone_code1').val(countryPhoneCode);
				});
				/* On check, show/hide some blocs */
				// TRANSACTION TYPE
				$('#paymentMethod .radio').each(function () {
					$(this).on('click', function () {
						if ($('#bank_card').is(':checked')) {
							$('#phoneNumberForMoney').addClass('d-none');

						} else {
							$('#phoneNumberForMoney').removeClass('d-none');
						}
					});
				});
			});
		</script>
</body>
</html>
