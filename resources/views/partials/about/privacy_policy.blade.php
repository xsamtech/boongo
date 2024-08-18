
		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-8">
                        <ul class="ps-5">
@foreach ($titles as $ttl)
                            <li class="h3 mb-4 fw-bold" style="list-style-type: decimal;">{{ $ttl['title'] }}</li>


    @if ($ttl['ref'] == 'data_use')
        @foreach ($ttl['contents'] as $cnt)
                            <div class="mb-4">
            @if ($cnt['subtitle'])
                                <p class="mb-3 fs-6 text-secondary">{{ $cnt['subtitle'] }}</p>
            @endif
                                <p class="mb-1 fs-6 text-secondary"><i class="bi bi-chevron-double-right me-2 align-middle fs-5 text-danger"></i> {!! $cnt['content'] !!}</p>
                            </div>
        @endforeach

    @else
        @foreach ($ttl['contents'] as $cnt)
                            <div class="mb-4">
                                <h5 class="h5 mb-1 fw-semibold dktv-text-green">{{ $cnt['subtitle'] }}</h5>

                                <p class="mb-1 fs-6 text-secondary">{!! $cnt['content'] !!}</p>
                            </div>
        @endforeach
    @endif
@endforeach
                        </ul>
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
