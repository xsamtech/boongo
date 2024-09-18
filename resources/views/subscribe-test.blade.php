@extends('layouts.guest')

@section('guest-content')

                <!-- SECTION -->
                <div class="section">
                    <!-- container -->
                    <div class="container">
                        <!-- row -->
                        <div id="subscribe" class="row" style="max-width: 40rem; min-height: 40rem;">
                            <div class="col-12">
                                <div style="display: flex; justify-content: center; margin-bottom: 4rem;">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="Boongo" width="100">
                                </div>

                                <div class="center-block">
                                    <form method="POST" action="{{ route('subscribe') }}">
                                        <input type="hidden" name="app_url" value="{{ getWebURL() }}">
                                        <input type="hidden" name="user_id" value="{{ request()->get('user_id') }}">
                                        <input type="hidden" name="subscription_id" value="{{ request()->get('subscription_id') }}">
    @csrf
                                        <div class="text-center" style="margin: 2rem 0;">
                                            <h3 class="text-uppercase fw-bolder">@lang('miscellaneous.menu.admin.subscription')</h3>
                                            <h5 class="text-muted" style="font-weight: 600; margin-bottom: 5rem;">@lang('miscellaneous.public.about.donate.send_money.description')</h5>

                                            <div id="paymentMethod">
    @foreach ($transaction_types as $type)
        @if ($type['type_name'] == __('miscellaneous.public.about.donate.send_money.mobile_money'))
                                                <label class="radio-inline">
                                                    <input type="radio" name="transaction_type_id" id="mobile_money" value="{{ $type['id'] }}">
                                                    <img src="{{ asset('assets/img/payment-mobile-money.png') }}" alt="{{ __('miscellaneous.public.about.donate.send_money.mobile_money') }}" width="40" style="display: inline-block; vertical-align: middle;">
                                                    @lang('miscellaneous.public.about.donate.send_money.mobile_money')
                                                </label>
        @else
                                                <label class="radio-inline">
                                                    <input type="radio" name="transaction_type_id" id="bank_card" value="{{ $type['id'] }}">
                                                    <img src="{{ asset('assets/img/payment-credit-card.png') }}" alt="{{ __('miscellaneous.public.about.donate.send_money.bank_card') }}" width="40" style="display: inline-block; vertical-align: middle;">
                                                    @lang('miscellaneous.public.about.donate.send_money.bank_card')
                                                </label>
        @endif
    @endforeach
                                            </div>
                                        </div>

                                        <div id="phoneNumberForMoney">
                                            <select name="select_country" id="select_country1" class="form-control" style="margin: 2rem 0;">
                                                <option style="font-size: 0.6rem;" selected disabled>@lang('miscellaneous.choose_country')</option>
    @forelse ($countries as $country)
                                                <option value="{{ $country['country_phone_code'] . '-' . $country['id'] }}">{{ $country['country_name'] }}</option>
    @empty
                                                <option>@lang('miscellaneous.empty_list')</option>
    @endforelse
                                            </select>

                                            <div class="form-group">
                                                <label class="sr-only" for="phone_number">@lang('miscellaneous.phone_code')</label>
                                                <div id="phone_code_text1" class="input-group">
                                                    <div class="input-group-addon text-value">xxxx</div>
                                                    <input type="tel" name="other_phone_number" id="phone_number" class="form-control" placeholder="@lang('miscellaneous.phone')">
                                                    <input type="hidden" id="phone_code1" name="other_phone_code" value="">
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn btn-block bng-btn-success" type="submit">@lang('miscellaneous.send')</button>
                                    </form>

                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                        </div> --}}
                        <!-- /row -->
                    </div>
                    <!-- /container -->
                </div>
                <!-- /SECTION -->

@endsection
