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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">

 		<!-- ============ Google font ============ -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

        <!-- ============ Addons CSS Files ============ -->
 		<!-- Bootstrap -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/custom/bootstrap/css/bootstrap.min.css') }}"/>
 		<!-- Slick -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/cooladmin/slick/slick.css') }}"/>
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/cooladmin/slick/slick-theme.css') }}"/>
 		<!-- Other -->
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/perfect-scrollbar/css/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/dataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.css') }}">

 		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
 		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
 		<!--[if lt IE 9]>
 		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
 		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
 		<![endif]-->

        <!-- ============ Custom CSS ============ -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}">

        <title>@lang('miscellaneous.admin.work.add')</title>
    </head>
	<body>
        <span class="menu-sidebar2__content d-none"></span>
        <!-- MODALS-->
        <!-- ### Crop user image ### -->
        <div class="modal fade" id="cropModalUser" tabindex="-1" aria-labelledby="cropModalUserLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalUserLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn btn-light border rounded-pill" data-bs-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_avatar" class="btn btn-primary rounded-pill"data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ### Crop other user image ### -->
        <div class="modal fade" id="cropModalOtherUser" tabindex="-1" aria-labelledby="cropModalOtherUserLabel" aria-hidden="true" data-bs-backdrop="{{ Route::is('branch.home') ? 'static' : 'true' }}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalOtherUserLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn btn-light border rounded-pill" data-bs-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_other_user" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MODALS-->

		<!-- SECTION -->
		<div class="section py-5">
            <h1 class="text-center mb-4">@lang('miscellaneous.admin.work.add')</h1>
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-lg-4 col-sm-6 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="register_work_title">@lang('miscellaneous.admin.work.data.work_title')</label>
                                    <input type="text" name="register_work_title" id="register_work_title" class="form-control" placeholder="@lang('miscellaneous.admin.work.data.work_title')">
                                </div>

                                <div class="form-group">
                                    <label for="register_work_url">@lang('miscellaneous.admin.work.data.work_url')</label>
                                    <input type="text" name="register_work_url" id="register_work_url" class="form-control" placeholder="@lang('miscellaneous.admin.work.data.work_url')">
                                </div>

                                <button type="submit" class="btn btn-block btn-primary">@lang('miscellaneous.register')</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 mx-auto">
                        <div class="card rounded-4">
                            <div id="otherUserImageWrapper" class="card-body pb-4 text-center">
                                <p class="card-text m-0">@lang('miscellaneous.account.personal_infos.click_to_change_picture')</p>

                                <label for="image_other_user" href="#" class="thumbnail" style="cursor: pointer;">
                                    <img src="{{ asset('assets/img/cover.png') }}" alt="cover" class="other-user-image img-fluid rounded-4">
                                    <input type="file" name="image_other_user" id="image_other_user" style="position: absolute; left: -99999px;">
                                </label>
                                <input type="hidden" name="data_other_user" id="data_other_user">

                                {{-- <div class="bg-image hover-overlay mt-3">
                                    <div class="mask rounded-4" style="background-color: rgba(5, 5, 5, 0.5);">
                                        <label role="button" class="d-flex h-100 justify-content-center align-items-center">
                                            <i class="bi bi-pencil-fill text-white fs-2"></i>
                                        </label>
                                    </div>
                                </div> --}}

                                <p class="d-none mt-2 mb-0 small text-success fst-italic">@lang('miscellaneous.waiting_register')</p>
                            </div>
                        </div>
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->

        <span id="btnBackTop" class="btn btn-floating btn-primary d-none" style="position: fixed; bottom: 2rem; right: 2rem;"><i class="fa-solid fa-chevron-up"></i></span>
		<!-- jQuery Plugins -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
		<script src="{{ asset('assets/addons/cooladmin/slick/slick.min.js') }}"></script>
		<script src="{{ asset('assets/addons/custom/jquery/jquery.zoom/jquery.zoom.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/dataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/scroll4ever/js/jquery.scroll4ever.js') }}"></script>
		<script src="{{ asset('assets/js/script.custom2.js') }}"></script>
	</body>
</html>