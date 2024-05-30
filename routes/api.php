<?php
/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Default API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'localization'])->group(function () {
    Route::apiResource('country', 'App\Http\Controllers\API\CountryController')->except(['index']);
    Route::apiResource('group', 'App\Http\Controllers\API\GroupController');
    Route::apiResource('status', 'App\Http\Controllers\API\StatusController')->except(['search', 'findByGroup']);
    Route::apiResource('type', 'App\Http\Controllers\API\TypeController')->except(['search', 'findByGroup']);
    Route::apiResource('category', 'App\Http\Controllers\API\CategoryController')->except(['search', 'findByGroup', 'allUsedInWorks', 'allUsedInWorksType']);
    Route::apiResource('work', 'App\Http\Controllers\API\WorkController')->except(['index', 'show', 'trends', 'search', 'findAllByUser', 'findAllByType', 'findAllByTypeStatus', 'findViews', 'filterByCategories']);
    Route::apiResource('file', 'App\Http\Controllers\API\FileController')->except(['index']);
    Route::apiResource('subscription', 'App\Http\Controllers\API\SubscriptionController')->except(['index']);
    Route::apiResource('cart', 'App\Http\Controllers\API\CartController')->except(['index']);
    Route::apiResource('role', 'App\Http\Controllers\API\RoleController')->except(['search']);
    Route::apiResource('user', 'App\Http\Controllers\API\UserController')->except(['show', 'store', 'login']);
    Route::apiResource('password_reset', 'App\Http\Controllers\API\PasswordResetController')->except(['searchByEmailOrPhone', 'searchByEmail', 'searchByPhone', 'checkToken']);
    Route::apiResource('personal_access_token', 'App\Http\Controllers\API\PersonalAccessTokenController')->except(['search']);
    Route::apiResource('notification', 'App\Http\Controllers\API\NotificationController')->except(['store']);
    Route::apiResource('payment', 'App\Http\Controllers\API\PaymentController')->except(['store', 'find_by_order_number', 'find_by_order_number_user', 'switch_status']);
    Route::apiResource('session', 'App\Http\Controllers\API\SessionController');
});
/*
|--------------------------------------------------------------------------
| Custom API resource
|--------------------------------------------------------------------------
 */
Route::group(['middleware' => ['api', 'localization']], function () {
    Route::resource('country', 'App\Http\Controllers\API\CountryController');
    Route::resource('status', 'App\Http\Controllers\API\StatusController');
    Route::resource('type', 'App\Http\Controllers\API\TypeController');
    Route::resource('category', 'App\Http\Controllers\API\CategoryController');
    Route::resource('work', 'App\Http\Controllers\API\WorkController');
    Route::resource('role', 'App\Http\Controllers\API\RoleController');
    Route::resource('user', 'App\Http\Controllers\API\UserController');
    Route::resource('password_reset', 'App\Http\Controllers\API\PasswordResetController');
    Route::resource('payment', 'App\Http\Controllers\API\PaymentController');

    // Country
    Route::get('country', 'App\Http\Controllers\API\CountryController@index')->name('country.api.index');
    // Status
    Route::get('status/search/{locale}/{data}', 'App\Http\Controllers\API\StatusController@search')->name('status.api.search');
    Route::get('status/find_by_group/{locale}/{group_name}', 'App\Http\Controllers\API\StatusController@findByGroup')->name('status.api.find_by_group');
    // Type
    Route::get('type/search/{locale}/{data}', 'App\Http\Controllers\API\TypeController@search')->name('type.api.search');
    Route::get('type/find_by_group/{locale}/{group_name}', 'App\Http\Controllers\API\TypeController@findByGroup')->name('type.api.find_by_group');
    // Category
    Route::get('category/search/{locale}/{data}', 'App\Http\Controllers\API\CategoryController@search')->name('category.api.search');
    Route::get('category/find_by_group/{group_name}', 'App\Http\Controllers\API\CategoryController@findByGroup')->name('category.api.find_by_group');
    Route::get('category/all_used_in_works', 'App\Http\Controllers\API\CategoryController@allUsedInWorks')->name('category.api.all_used_in_works');
    Route::get('category/all_used_in_works_type/{type_id}', 'App\Http\Controllers\API\CategoryController@allUsedInWorksType')->name('category.api.all_used_in_works_type');
    // Work
    Route::get('work', 'App\Http\Controllers\API\WorkController@index')->name('work.api.index');
    Route::get('work/{id}', 'App\Http\Controllers\API\WorkController@show')->name('work.api.show');
    Route::get('work/trends/{year}', 'App\Http\Controllers\API\WorkController@trends')->name('work.api.trends');
    Route::get('work/search/{data}', 'App\Http\Controllers\API\WorkController@search')->name('work.api.search');
    Route::get('work/find_views/{work_id}', 'App\Http\Controllers\API\WorkController@findViews')->name('work.api.find_views');
    Route::post('work/find_all_by_type/{locale}/{type_name}', 'App\Http\Controllers\API\WorkController@findAllByType')->name('work.api.find_all_by_type');
    Route::post('work/find_all_by_type_status/{locale}/{type_name}/{status_name}', 'App\Http\Controllers\API\WorkController@findAllByTypeStatus')->name('work.api.find_all_by_type_status');
    Route::post('work/filter_by_categories', 'App\Http\Controllers\API\WorkController@filterByCategories')->name('work.api.filter_by_categories');
    // Role
    Route::get('role/search/{data}', 'App\Http\Controllers\API\RoleController@search')->name('role.api.search');
    // User
    Route::get('user/{id}', 'App\Http\Controllers\API\UserController@show')->name('user.api.show');
    Route::get('user/find_by_parental_code/{parental_code}', 'App\Http\Controllers\API\UserController@findByParentalCode')->name('user.api.find_by_parental_code');
    Route::post('user', 'App\Http\Controllers\API\UserController@store')->name('user.api.store');
    Route::post('user/login', 'App\Http\Controllers\API\UserController@login')->name('user.api.login');
    // PasswordReset
    Route::get('password_reset/search_by_email_or_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmailOrPhone')->name('password_reset.api.search_by_email_or_phone');
    Route::get('password_reset/search_by_email/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmail')->name('password_reset.api.search_by_email');
    Route::get('password_reset/search_by_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByPhone')->name('password_reset.api.search_by_phone');
    Route::post('password_reset/check_token', 'App\Http\Controllers\API\PasswordResetController@checkToken')->name('password_reset.api.check_token');
    // Payment
    Route::post('payment/store', 'App\Http\Controllers\API\PaymentController@store')->name('payment.api.store');
    Route::get('payment/find_by_order_number/{order_number}', 'App\Http\Controllers\API\PaymentController@findByOrderNumber')->name('payment.api.find_by_order_number');
    Route::get('payment/find_by_order_number_user/{order_number}/{user_id}', 'App\Http\Controllers\API\PaymentController@findByOrderNumberUser')->name('payment.api.find_by_order_number_user');
    Route::put('payment/switch_status/{status_id}/{id}', 'App\Http\Controllers\API\PaymentController@switchStatus')->name('payment.api.switch_status');
});
Route::group(['middleware' => ['api', 'auth:sanctum', 'localization']], function () {
    Route::resource('work', 'App\Http\Controllers\API\WorkController');

    // Work
    Route::post('work/find_all_by_user/{user_id}', 'App\Http\Controllers\API\WorkController@findAllByUser')->name('work.api.find_all_by_user');
    Route::put('work/switch_view/{work_id}', 'App\Http\Controllers\API\WorkController@switchView')->name('work.api.switch_view');
    Route::put('work/upload_files', 'App\Http\Controllers\API\WorkController@uploadFiles')->name('work.api.upload_files');
});
