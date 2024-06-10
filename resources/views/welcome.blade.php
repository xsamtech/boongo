@extends('layouts.guest')

@section('guest-content')

		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-lg-4 col-sm-6 col-lg-offset-2">
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

                    <div class="col-lg-3 col-sm-5">
                        <div class="card rounded-4">
                            <div id="otherUserImageWrapper" class="card-body pb-4 text-center">
                                <p class="card-text m-0">@lang('miscellaneous.account.personal_infos.click_to_change_picture')</p>

                                <label for="image_other_user" href="#" class="thumbnail" style="cursor: pointer;">
                                    <img src="{{ asset('assets/img/cover.png') }}" alt="cover" class="other-user-image img-fluid rounded-4">
                                    <input type="file" name="image_other_user" id="image_other_user" style="margin-left: -99999px;">
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

@endsection
