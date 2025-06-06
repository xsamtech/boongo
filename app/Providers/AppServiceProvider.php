<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\Group;
use App\Models\Type;
use Illuminate\Support\ServiceProvider;
use App\Http\Resources\Country as ResourcesCountry;
use App\Http\Resources\Type as ResourcesType;
use Illuminate\Support\Facades\URL;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (app()->environment('production')) {
        //     URL::forceScheme('https');
        // }

        // Select all countries
        $countries = Country::all();
        // Select all types by group (Type de transaction)
        $group = Group::where('group_name', 'Type de paiement')->first();
        $transaction_types = !empty($group) ? Type::where('group_id', $group->id)->get() : Type::all();

        view()->composer('*', function ($view) use ($countries, $transaction_types) {
            $view->with('current_locale', app()->getLocale());
            $view->with('available_locales', config('app.available_locales'));
            $view->with('countries', ResourcesCountry::collection($countries)->toArray(request()));
            $view->with('transaction_types', ResourcesType::collection($transaction_types)->toArray(request()));
        });
    }
}
