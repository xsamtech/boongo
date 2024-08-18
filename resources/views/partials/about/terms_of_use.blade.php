
		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-8" style="min-height: 50rem;">
                        <p class="lead" style="margin-top: 1rem;">@lang('miscellaneous.public.about.terms_of_use.description')</p>
@foreach ($titles as $ttl)
    @foreach ($ttl['contents'] as $cnt)
                        <div class="mb-4">
                            <h5 class="h5 mb-1 fw-semibold dktv-text-green">{{ $cnt['subtitle'] }}</h5>

                            <p class="mb-1 fs-6 text-secondary">{!! $cnt['content'] !!}</p>
                        </div>
    @endforeach
@endforeach
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
