@extends('layouts.guest')

@section('guest-content')

                                <div class="card border rounded-0 text-center shadow-0">
                                    <div class="card-body py-5">
    @if (Route::is('transaction.waiting'))
                                        <p>@lang('miscellaneous.transaction_waiting')</p>
                                        <p>
                                            <a href="{{ route('transaction.message', ['orderNumber' => explode('-', request()->success_message)[0], 'userId' => explode('-', request()->success_message)[1]]) }}" class="btn dktv-btn-blue py-3 px-5 rounded-pill">OK</a>
                                        </p>
    @else
        @if (!empty($status_code))
            @if ($status_code == '0')
                                        <h1 class="text-success" style="font-size: 5rem;"><span class="bi bi-check-circle"></span></h1>
            @endif

            @if ($status_code == '1')
                                        <h1 class="text-warning" style="font-size: 5rem;"><span class="bi bi-exclamation-circle"></span></h1>
            @endif

            @if ($status_code == '2')
                                        <h1 class="text-danger" style="font-size: 5rem;"><span class="bi bi-x-circle"></span></h1>
            @endif
        @endif
                                        <h3 class="h3 mb-4">{{ \Session::has('error_message') ? \Session::get('error_message') : $message_content }}</h3>

        @if (!empty($payment))
                                        <div class="card border mb-4 shadow-0">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="px-2 py-1 border-start border-3 border-{{ $payment->status->color }} text-start">
                                                    <p class="m-0 text-black">{{ $payment->reference }}</p>
                                                    <h4 class="h4 mt-0 mb-1 fw-bold text-{{ $payment->status->color }} text-truncate" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">
                                                        {{ $payment->amount . ' ' . $payment->currency }}
                                                    </h4>
                                                    <p class="m-0 small">{{ $payment->created_at }}</p>
                                                </div>

                                                <div class="px-3 py-1 text-center">
                                                    <p class="m-0 text-black text-uppercase text-truncate">{{ $payment->channel }}</p>
                                                    <div class="badge badge-{{ $payment->status->color }} p-2 rounded-pill fw-normal">
                                                        {{ $payment->status->status_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
        @endif

                                        <a href="{{ route('home') }}" class="btn dktv-btn-yellow py-3 px-5 rounded-pill shadow-0 detect-webview">{{ __('miscellaneous.back_home') }}</a>
    @endif
                                    </div>
                                </div>

@endsection
