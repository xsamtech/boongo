@extends('layouts.guest')

@section('guest-content')

		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-12">
                        <div class="card border rounded-0 text-center">
                            <div class="card-body py-5">
                                <h1 class="display-1 fw-bold bng-text-danger">403</h1>
                                <h2 class="mb-4 bng-text-primary">{{ __('notifications.403_title') }}</h2>
                                <p class="lead mb-4">{{ __('notifications.403_description') }}</p>
                                <a href="{{ route('home') }}" class="btn bng-btn-warning rounded-pill py-3 px-5">{{ __('miscellaneous.back_home') }}</a>
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
