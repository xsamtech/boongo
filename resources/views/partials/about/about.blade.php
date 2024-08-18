
		<!-- SECTION -->
		<div class="section{{ request()->has('app_id') ? ' pt-0' : '' }}">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-8">
@foreach ($titles as $ttl)
    @if ($ttl['ref'] == 'mission')
                        <hr class="my-5">

                        <h3 class="h3 mb-4 fw-bold">{{ $ttl['title'] }}</h3>

        @foreach ($ttl['contents'] as $cnt)
                        <div class="mb-4">
                            <p class="mb-1 fs-6 text-secondary"><i class="bi bi-chevron-double-right me-2 align-middle fs-5 text-danger"></i> {!! $cnt['content'] !!}</p>
                        </div>
        @endforeach

                        <hr class="my-5">
    @else
                        <h3 class="h3 mb-4 fw-bold">{{ $ttl['title'] }}</h3>

        @foreach ($ttl['contents'] as $cnt)
                        <div class="mb-4">
                            <h5 class="h5 mb-1 fw-semibold dktv-text-green">{{ $cnt['subtitle'] }}</h5>

                            <p class="mb-1 fs-6 text-secondary">{!! $cnt['content'] !!}</p>
                        </div>
        @endforeach
    @endif
@endforeach
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
