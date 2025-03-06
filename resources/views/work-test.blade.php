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

        <!-- ============ Favicon ============ -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- ============ Font Icons Files ============ -->
        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> --}}
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">

 		<!-- ============ Google font ============ -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

        <!-- ============ Addons CSS Files ============ -->
 		<!-- Bootstrap -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/custom/mdb/css/mdb.min.css') }}"/>
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
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/wysiwyg-editor-master/css/froala_editor.min.css') }}">

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
        <span class="menu-sidebar2__content perfect-scrollbar d-none"></span>
        <!-- MODALS-->
        <!-- ### Crop user image ### -->
        <div class="modal fade" id="cropModalUser" tabindex="-1" aria-labelledby="cropModalUserLabel"> <!-- aria-hidden="true"-->
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
                        <button type="button" id="crop_avatar" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ### Crop other image ###  aria-hidden="true" -->
        <div class="modal fade" id="cropModalOther" tabindex="-1" aria-labelledby="cropModalOtherLabel" data-bs-backdrop="{{ Route::is('branch.home') ? 'static' : 'true' }}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalOtherLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-sm-0 mb-4">
                                    <div class="bg-image">
                                        <img src="" id="retrieved_image_other" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light border rounded-pill" data-bs-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_other" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MODALS-->

@if (\Session::has('success_message'))
        <!-- ALERT-->
        <div class="position-relative">
            <div class="row position-absolute w-100" style="top: 0; opacity: 0.9; z-index: 9999;">
                <div class="col-lg-5 col-sm-6 mx-auto mt-lg-0 mt-5">
                    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                        <i class="fa-solid fa-info-circle me-2 fs-4" style="vertical-align: -3px;"></i> {{ \Session::get('success_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ALERT END-->
@endif

@if (\Session::has('error_message'))
        <!-- ALERT-->
        <div class="position-relative">
            <div class="row position-absolute w-100" style="top: 0; opacity: 0.9; z-index: 9999;">
                <div class="col-lg-5 col-sm-6 mx-auto mt-lg-0 mt-5">
                    <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i> {{ explode('~', \Session::get('error_message'))[3]  }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ALERT END-->
@endif

        <div class="py-4">
            <div class="d-flex justify-content-center mb-1">
                <img src="{{ asset('assets/img/brand-reverse.png') }}" alt="Boongo" width="200">
            </div>

            {{-- <form action="{{ route('admin.work.home') }}" method="post" enctype="multipart/form-data"> --}}
            <form id="workData">
                <div class="row">
                    <div class="col-lg-7 d-flex justify-content-between align-items-center mx-auto mb-4">
                        <h1 class="m-0">@lang('miscellaneous.admin.work.add')</h1>
                        <p class="m-0"><a href="{{ route('admin.partners.home') }}">@lang('miscellaneous.admin.partner.link')</a></p>
                    </div>
                </div>

                <input type="hidden" name="image_type_id" id="image_type_id" value="6">
                <input type="hidden" name="file_type_id" id="file_type_id" value="7">
                <input type="hidden" name="status_id" id="status_id" value="5">
@csrf

                <!-- container -->
                <div class="container">
                    <!-- row -->
                    <div class="row">
                        <div class="col-lg-5 col-sm-6 ms-auto">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="work_title" class="visually-hidden">@lang('miscellaneous.admin.work.data.work_title')</label>
                                        <input type="text" name="work_title" id="work_title" class="form-control" placeholder="@lang('miscellaneous.admin.work.data.work_title')">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="work_content" class="visually-hidden">@lang('miscellaneous.admin.work.data.work_content')</label>
                                        <textarea name="work_content" id="work_content" class="form-control" placeholder="@lang('miscellaneous.admin.work.data.work_content')"></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="work_url" class="visually-hidden">@lang('miscellaneous.admin.work.data.work_url')</label>
                                        <input type="text" name="work_url" id="work_url" class="form-control" placeholder="@lang('miscellaneous.admin.work.data.work_url')">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="file_url">@lang('miscellaneous.upload.upload_document')</label>
                                        <input type="file" name="file_url" id="file_url" class="form-control">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="type_id">@lang('miscellaneous.menu.admin.group.type')</label>
                                        <select id="type_id" name="type_id" class="form-select" aria-label="@lang('miscellaneous.admin.work.data.choose_type')">
                                            <option class="small" selected disabled>@lang('miscellaneous.admin.work.data.choose_type')</option>
@forelse ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->type_name }}</option>
@empty
@endforelse
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="d-block text-center">@lang('miscellaneous.admin.work.data.choose_categories')</label>
@forelse ($categories as $category)
                                        <div class="form-check mx-3">
                                            <input type="checkbox" name="categories_ids" id="category_{{ $category->id }}" class="form-check-input" value="{{ $category->id }}">
                                            <label class="form-check-label bng-text-secondary" for="category_{{ $category->id }}">{{ $category->category_name }}</label>
                                        </div>
@empty
@endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-6 me-auto">
                            <div class="card rounded-4">
                                <div id="otherImageWrapper" class="card-body pb-4 text-center">
                                    <p class="card-text m-0">@lang('miscellaneous.account.personal_infos.click_to_change_picture')</p>

                                    <div class="bg-image hover-overlay mt-3">
                                        <img src="{{ asset('assets/img/cover.png') }}" alt="@lang('miscellaneous.admin.work.data.work_title')" class="other-user-image img-fluid rounded-4">
                                        <div class="mask rounded-4" style="background-color: rgba(5, 5, 5, 0.5);">
                                            <label role="button" for="image_other" class="d-flex h-100 justify-content-center align-items-center">
                                                <i class="fa-solid fa-pencil-alt text-white fs-2"></i>
                                                <input type="file" name="image_other" id="image_other" class="d-none">
                                            </label>
                                            <input type="hidden" name="image_64" id="image_64">
                                        </div>
                                    </div>

                                    <p class="d-none mt-2 mb-0 small bng-text-primary fst-italic">@lang('miscellaneous.waiting_register')</p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-5 text-center request-message"></div>
                        </div>
                    </div>
                    <!-- /row -->

                    <!-- row -->
                    <div class="row mt-3">
                        <div class="col-lg-4 col-sm-6 col-9 mx-auto">
                            <button class="btn btn-block btn-primary">@lang('miscellaneous.register')</button>
                        </div>
                    </div>
                    <!-- /row -->
                </div>
                <!-- /container -->
            </form>
        </div>

        <div class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 mx-auto">
                        <div class="card border">
                            <div class="card-header d-sm-flex justify-content-between align-items-center">
                                <h3 class="m-sm-0 mb-1">{{ request()->has('type') ? request()->get('type') : __('miscellaneous.admin.work.list') }}</h3>

                                <form class="input-group w-50 m-0" method="get">
                                    <select name="type" class="form-select m-0">
                                        <option class="small" disabled selected>@lang('miscellaneous.admin.work.data.choose_type')</option>
                                        <option value="empty">@lang('miscellaneous.all_types')</option>
@forelse ($types as $type)
                                        <option>{{ $type->type_name }}</option>
@empty
@endforelse
                                    </select>
                                    <button type="submit" class="btn bng-btn-success m-0"><i class="fa-solid fa-search"></i></button>
                                </form>
                            </div>
@if (count($works) > 0)
                            <ul class="list-group list-group-flush">
    @foreach ($works as $item)
                                <li class="list-group-item py-3">
                                    <div class="d-lg-flex justify-content-between">
                                        <div class="mb-lg-0 mb-4">
                                            <img src="{{ !empty($item->image_url) ? $item->image_url : asset('assets/img/cover.png') }}" alt="{{ $item->work_title }}" width="100" class="float-sm-start rounded-4 mb-3 me-3">
                                            <h4 class="my-2 dktv-text-green fw-bold">{{ $item->work_title }}</h4>
                                            <p class="mb-3 text-muted">{{ !empty($item->work_content) ? Str::limit($item->work_content, 50, '...') : '' }}</p>
        @if (!empty($item->document_url))
                                            <a href="{{ $item->document_url }}" target="_blank" class="px-4 py-3"><i class="fa-solid fa-file-pdf me-2 fs-4 bng-text-danger"></i></a>
        @endif
        @if (!empty($item->video_url))
                                            <a href="{{ $item->video_url }}" target="_blank" class="px-4 py-3"><i class="fa-solid fa-play-circle me-2 fs-4 bng-text-primary"></i></a>
        @endif
                                        </div>

                                        <h5 class="w-25">
        @forelse ($item->categories as $category)
                                            <div class="badge badge-warning d-inline-block mb-2 me-2 text-black fw-normal">{{ $category->category_name }}</div>
        @empty
        @endforelse
                                        </h5>
                                    </div>
                                </li>
    @endforeach
                            </ul>

    @if ($lastPage > 1)
                            <div class="card-body pb-0 d-flex justify-content-center">
        @include('partials.pagination')
                            </div>
    @endif
@else
                            <div class="card-body text-center">
                                <p class="m-0 lead text-secondary fst-italic">@lang('miscellaneous.empty_list')</p>
                            </div>
@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <span id="btnBackTop" class="btn btn-floating btn-primary pb-0 d-none" style="position: fixed; bottom: 2rem; right: 2rem;"><i class="fa-solid fa-chevron-up"></i></span>

        <!-- jQuery Plugins -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/mdb/js/mdb.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.min.js') }}"></script>
		<script src="{{ asset('assets/addons/cooladmin/slick/slick.min.js') }}"></script>
		<script src="{{ asset('assets/addons/custom/jquery/jquery.zoom/jquery.zoom.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/dataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/scroll4ever/js/jquery.scroll4ever.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/wysiwyg-editor-master/js/froala_editor.min.js') }}"></script>
		<script src="{{ asset('assets/js/script.custom.js') }}"></script>
		<script type="text/javascript">
            $(function () {
                /* Register form-data */
                $('form#workData').submit(function (e) {
					e.preventDefault();

                    var formData = new FormData(this);
                    var categories = [];

                    document.querySelectorAll('[name="categories_ids"]').forEach(item => {
                        if (item.checked === true) {
                            categories.push(parseInt(item.value));
                        }
                    });

                    for (let i = 0; i < categories.length; i++) {
                        formData.append('categories_ids[' + i + ']', categories[i]);
                    }

                    $.ajax({
						headers: { 'Authorization': 'Bearer 4|9dQHWw3Jzh4BAsm5sBjMtH8Fz4oWlcvVQYQqLyru8b38b0a9', 'Accept': 'multipart/form-data', 'X-localization': navigator.language },
						type: 'POST',
						contentType: 'multipart/form-data',
						url: apiHost + '/work',
						data: formData,
						beforeSend: function () {
							$('form#workData .request-message').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
						},
						success: function (res) {
                            if ($('form#workData .request-message').hasClass('text-danger')) {
                                $('form#workData .request-message').removeClass('text-danger');
                            }

							$('form#workData .request-message').addClass('text-success').html(res.message);

                            document.getElementById('workData').reset();
							location.reload();
                        },
						cache: false,
						contentType: false,
						processData: false,
						error: function (xhr, error, status_description) {
                            if ($('form#workData .request-message').hasClass('text-success')) {
                                $('form#workData .request-message').removeClass('text-success');
                            }

                            $('form#workData .request-message').addClass('text-danger').html((xhr.responseJSON ? xhr.responseJSON.message : 'Error'));
							console.log((xhr.responseJSON ? xhr.responseJSON.message : `Error: ${JSON.stringify(xhr)}`));
							console.log(xhr.status);
							console.log(error);
							console.log(status_description);
						}
					});
				});
            });
        </script>
	</body>
</html>
