@extends('layouts.guest')

@section('guest-content')

                <!-- SECTION -->
                <div class="section">
                    <!-- container -->
                    <div class="container">
                        <!-- row -->
                        <div id="subscribe" class="center-block" style="max-width: 40rem; min-height: 40rem;">
                            <form method="POST" action="{{ route('subscribe') }}">
                                <input type="hidden" name="app_url" value="{{ getWebURL() }}">
                                <input type="hidden" name="user_id" value="{{ request()->get('user_id') }}">
                                <input type="hidden" name="subscription_id" value="{{ request()->get('subscription_id') }}">
    @csrf
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <h5 class="h5 m-0 text-uppercase fw-bolder">@lang('miscellaneous.public.about.donate.send_money.title')</h5>
                                        <p class="small m-0 text-muted">@lang('miscellaneous.public.about.donate.send_money.description')</p>
                                    </div>

                                    <div id="paymentMethod" class="text-center">
    @foreach ($transaction_types as $type)
        @if ($type['type_name'] == __('miscellaneous.public.about.donate.send_money.mobile_money'))
                                        <label class="radio-inline">
                                            <input type="radio" name="transaction_type_id" id="mobile_money" value="{{ $type['id'] }}">
                                            <img src="{{ asset('assets/img/payment-mobile-money.png') }}" alt="{{ __('miscellaneous.public.about.donate.send_money.mobile_money') }}" width="37">
                                            @lang('miscellaneous.public.about.donate.send_money.mobile_money')
                                        </label>
        @else
                                        <label class="radio-inline">
                                            <input type="radio" name="transaction_type_id" id="bank_card" value="{{ $type['id'] }}">
                                            <img src="{{ asset('assets/img/payment-credit-card.png') }}" alt="{{ __('miscellaneous.public.about.donate.send_money.bank_card') }}" width="37">
                                            @lang('miscellaneous.public.about.donate.send_money.bank_card')
                                        </label>
        @endif
    @endforeach
                                    </div>
                                </div>

                                <div id="phoneNumberForMoney" class="row g-sm-2 g-3">
                                    <div class="col-sm-5">
                                        <div class="form-floating pt-0">
                                            <select name="select_country" id="select_country1" class="form-control">
                                                <option style="font-size: 0.6rem;" selected disabled>@lang('miscellaneous.choose_country')</option>
    @forelse ($countries as $country)
                                                <option value="{{ $country['country_phone_code'] . '-' . $country['id'] }}">{{ $country['country_name'] }}</option>
    @empty
                                                <option>@lang('miscellaneous.empty_list')</option>
    @endforelse
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <label class="sr-only" for="phone_number">@lang('miscellaneous.phone_code')</label>
                                            <div id="phone_code_text1" class="input-group">
                                                <div class="input-group-addon text-value">xxxx</div>
                                                <input type="tel" name="other_phone_number" id="phone_number" class="form-control" placeholder="@lang('miscellaneous.phone')">
                                                <input type="hidden" id="phone_code1" name="other_phone_code" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-block dktv-btn-green mt-4 py-3 px-5 rounded-pill shadow-0" type="submit">@lang('miscellaneous.send')</button>
                            </form>
                        </div>
                        {{-- <div class="row">
                        </div> --}}
                        <!-- /row -->
                    </div>
                    <!-- /container -->
                </div>
                <!-- /SECTION -->

@endsection
