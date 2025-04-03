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
    Route::apiResource('country', 'App\Http\Controllers\API\CountryController')->except(['index', 'store', 'search']);
    Route::apiResource('group', 'App\Http\Controllers\API\GroupController')->except(['search']);
    Route::apiResource('status', 'App\Http\Controllers\API\StatusController')->except(['search', 'findByGroup']);
    Route::apiResource('type', 'App\Http\Controllers\API\TypeController')->except(['search', 'findByGroup']);
    Route::apiResource('category', 'App\Http\Controllers\API\CategoryController')->except(['search', 'findByGroup', 'allUsedInWorks', 'allUsedInWorksType']);
    Route::apiResource('work', 'App\Http\Controllers\API\WorkController')->except(['index', 'store', 'show', 'trends', 'search', 'findAllByUser', 'findAllByType', 'findAllByTypeStatus', 'findViews', 'filterByCategories']);
    Route::apiResource('file', 'App\Http\Controllers\API\FileController')->except(['index']);
    Route::apiResource('subscription', 'App\Http\Controllers\API\SubscriptionController')->except(['index']);
    Route::apiResource('cart', 'App\Http\Controllers\API\CartController')->except(['index']);
    Route::apiResource('partner', 'App\Http\Controllers\API\PartnerController');
    Route::apiResource('role', 'App\Http\Controllers\API\RoleController')->except(['search']);
    Route::apiResource('user', 'App\Http\Controllers\API\UserController')->except(['store', 'show', 'login']);
    Route::apiResource('organization', 'App\Http\Controllers\API\OrganizationController');
    Route::apiResource('circle', 'App\Http\Controllers\API\CircleController');
    Route::apiResource('event', 'App\Http\Controllers\API\EventController');
    Route::apiResource('password_reset', 'App\Http\Controllers\API\PasswordResetController')->except(['searchByEmailOrPhone', 'searchByEmail', 'searchByPhone', 'checkToken']);
    Route::apiResource('personal_access_token', 'App\Http\Controllers\API\PersonalAccessTokenController');
    Route::apiResource('message', 'App\Http\Controllers\API\MessageController');
    Route::apiResource('notification', 'App\Http\Controllers\API\NotificationController');
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
    Route::resource('subscription', 'App\Http\Controllers\API\SubscriptionController');
    Route::resource('role', 'App\Http\Controllers\API\RoleController');
    Route::resource('user', 'App\Http\Controllers\API\UserController');
    Route::resource('password_reset', 'App\Http\Controllers\API\PasswordResetController');
    Route::resource('payment', 'App\Http\Controllers\API\PaymentController');

    // Country
    Route::get('country', 'App\Http\Controllers\API\CountryController@index')->name('country.api.index');
    Route::post('country', 'App\Http\Controllers\API\CountryController@store')->name('country.api.store');
    Route::get('country/search/{data}', 'App\Http\Controllers\API\CountryController@search')->name('country.api.search');
    // Status
    Route::get('status/search/{locale}/{data}', 'App\Http\Controllers\API\StatusController@search')->name('status.api.search');
    Route::get('status/find_by_group/{group_name}', 'App\Http\Controllers\API\StatusController@findByGroup')->name('status.api.find_by_group');
    // Type
    Route::get('type/search/{locale}/{data}', 'App\Http\Controllers\API\TypeController@search')->name('type.api.search');
    Route::get('type/find_by_group/{group_name}', 'App\Http\Controllers\API\TypeController@findByGroup')->name('type.api.find_by_group');
    // Category
    Route::get('category/search/{locale}/{data}', 'App\Http\Controllers\API\CategoryController@search')->name('category.api.search');
    Route::get('category/find_by_group/{group_name}', 'App\Http\Controllers\API\CategoryController@findByGroup')->name('category.api.find_by_group');
    Route::get('category/all_used_in_works', 'App\Http\Controllers\API\CategoryController@allUsedInWorks')->name('category.api.all_used_in_works');
    Route::get('category/all_used_in_works_type/{type_id}', 'App\Http\Controllers\API\CategoryController@allUsedInWorksType')->name('category.api.all_used_in_works_type');
    // Work
    Route::get('work', 'App\Http\Controllers\API\WorkController@index')->name('work.api.index');
    Route::post('work', 'App\Http\Controllers\API\WorkController@store')->name('work.api.store');
    Route::get('work/{id}', 'App\Http\Controllers\API\WorkController@show')->name('work.api.show');
    Route::get('work/trends/{year}', 'App\Http\Controllers\API\WorkController@trends')->name('work.api.trends');
    Route::get('work/search/{data}', 'App\Http\Controllers\API\WorkController@search')->name('work.api.search');
    Route::get('work/find_views/{work_id}', 'App\Http\Controllers\API\WorkController@findViews')->name('work.api.find_views');
    Route::get('work/find_all_by_type/{locale}/{type_name}', 'App\Http\Controllers\API\WorkController@findAllByType')->name('work.api.find_all_by_type');
    Route::get('work/find_all_by_type_status/{locale}/{type_name}/{status_name}', 'App\Http\Controllers\API\WorkController@findAllByTypeStatus')->name('work.api.find_all_by_type_status');
    Route::post('work/filter_by_categories', 'App\Http\Controllers\API\WorkController@filterByCategories')->name('work.api.filter_by_categories');
    Route::post('work/filter_by_categories_type_status/{locale}/{type_name}/{status_name}', 'App\Http\Controllers\API\WorkController@filterByCategoriesTypeStatus')->name('work.api.filter_by_categories_type_status');
    // Subscription
    Route::get('subscription', 'App\Http\Controllers\API\SubscriptionController@index')->name('subscription.api.index');
    // Role
    Route::get('role/search/{data}', 'App\Http\Controllers\API\RoleController@search')->name('role.api.search');
    // User
    Route::post('user', 'App\Http\Controllers\API\UserController@store')->name('user.api.store');
    Route::get('user/{id}', 'App\Http\Controllers\API\UserController@show')->name('user.api.show');
    Route::post('user/login', 'App\Http\Controllers\API\UserController@login')->name('user.api.login');
    // PasswordReset
    Route::get('password_reset/search_by_email_or_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmailOrPhone')->name('password_reset.api.search_by_email_or_phone');
    Route::get('password_reset/search_by_email/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmail')->name('password_reset.api.search_by_email');
    Route::get('password_reset/search_by_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByPhone')->name('password_reset.api.search_by_phone');
    Route::post('password_reset/check_token', 'App\Http\Controllers\API\PasswordResetController@checkToken')->name('password_reset.api.check_token');
    // Payment
    Route::post('payment/store', 'App\Http\Controllers\API\PaymentController@store')->name('payment.api.store');
    Route::get('payment/find_by_phone/{phone_number}', 'App\Http\Controllers\API\PaymentController@findByPhone')->name('payment.api.find_by_phone');
    Route::get('payment/find_by_order_number/{order_number}', 'App\Http\Controllers\API\PaymentController@findByOrderNumber')->name('payment.api.find_by_order_number');
    Route::get('payment/find_by_order_number_user/{order_number}/{user_id}', 'App\Http\Controllers\API\PaymentController@findByOrderNumberUser')->name('payment.api.find_by_order_number_user');
    Route::put('payment/switch_status/{status_id}/{id}', 'App\Http\Controllers\API\PaymentController@switchStatus')->name('payment.api.switch_status');
});
Route::group(['middleware' => ['api', 'auth:sanctum', 'localization']], function () {
    Route::resource('work', 'App\Http\Controllers\API\WorkController')->except(['index', 'store', 'show', 'trends', 'search', 'findAllByUser', 'findAllByType', 'findAllByTypeStatus', 'findViews', 'filterByCategories']);
    Route::resource('partner', 'App\Http\Controllers\API\PartnerController');
    Route::resource('cart', 'App\Http\Controllers\API\CartController')->except(['index']);
    Route::resource('subscription', 'App\Http\Controllers\API\SubscriptionController')->except(['index']);
    Route::resource('user', 'App\Http\Controllers\API\UserController')->except(['store', 'show', 'login']);
    Route::resource('organization', 'App\Http\Controllers\API\OrganizationController');
    Route::resource('notification', 'App\Http\Controllers\API\NotificationController');

    // Work
    Route::get('work/find_all_by_user/{user_id}', 'App\Http\Controllers\API\WorkController@findAllByUser')->name('work.api.find_all_by_user');
    Route::put('work/switch_view/{work_id}', 'App\Http\Controllers\API\WorkController@switchView')->name('work.api.switch_view');
    Route::post('work/upload_files', 'App\Http\Controllers\API\WorkController@uploadFiles')->name('work.api.upload_files');
    Route::put('work/add_image/{id}', 'App\Http\Controllers\API\WorkController@addImage')->name('work.api.add_image');
    // Partner
    Route::get('partner/search/{data}', 'App\Http\Controllers\API\PartnerController@search')->name('partner.api.search');
    Route::get('partner/partnerships_by_status/{locale}/{status_name}', 'App\Http\Controllers\API\PartnerController@partnershipsByStatus')->name('partner.api.partnerships_by_status');
    Route::put('partner/terminate_partnership/{partner_id}', 'App\Http\Controllers\API\PartnerController@terminatePartnership')->name('partner.api.terminate_partnership');
    // Cart
    Route::get('cart/is_inside/{work_id}/{user_id}', 'App\Http\Controllers\API\CartController@isInside')->name('cart.api.is_inside');
    Route::put('cart/add_to_cart/{work_id}/{user_id}', 'App\Http\Controllers\API\CartController@addToCart')->name('cart.api.add_to_cart');
    Route::put('cart/remove_from_cart/{work_id}/{cart_id}', 'App\Http\Controllers\API\CartController@removeFromCart')->name('cart.api.remove_from_cart');
    Route::post('cart/purchase/{user_id}', 'App\Http\Controllers\API\CartController@purchase')->name('cart.api.purchase');
    // Subscription
    Route::get('subscription/is_subscribed/{user_id}', 'App\Http\Controllers\API\SubscriptionController@isSubscribed')->name('subscription.api.is_subscribed');
    Route::put('subscription/validate_subscription/{user_id}', 'App\Http\Controllers\API\SubscriptionController@validateSubscription')->name('subscription.api.validate_subscription');
    Route::put('subscription/invalidate_subscription/{user_id}', 'App\Http\Controllers\API\SubscriptionController@invalidateSubscription')->name('subscription.api.invalidate_subscription');
    // User
    Route::get('user/profile/{username}', 'App\Http\Controllers\API\UserController@profile')->name('user.api.profile');
    Route::get('user/find_by_role/{locale}/{role_name}', 'App\Http\Controllers\API\UserController@findByRole')->name('user.api.find_by_role');
    Route::get('user/find_by_not_role/{locale}/{role_name}', 'App\Http\Controllers\API\UserController@findByNotRole')->name('user.api.find_by_not_role');
    Route::get('user/organization_members/{organization_id}/{role_name}', 'App\Http\Controllers\API\UserController@organizationMembers')->name('user.api.organization_members');
    Route::get('user/group_members/{entity}/{entity_id}', 'App\Http\Controllers\API\UserController@groupMembers')->name('user.api.group_members');
    Route::get('user/member_groups/{entity}/{user_id}/{status_id}', 'App\Http\Controllers\API\UserController@memberGroups')->name('user.api.member_groups');
    Route::get('user/is_main_member/{entity}/{entity_id}/{user_id}', 'App\Http\Controllers\API\UserController@isMainMember')->name('user.api.is_main_member');
    Route::get('user/is_partner/{user_id}', 'App\Http\Controllers\API\UserController@isPartner')->name('user.api.is_partner');
    Route::get('user/find_by_status/{status_id}', 'App\Http\Controllers\API\UserController@findByStatus')->name('user.api.find_by_status');
    Route::put('user/subscribe_to_group/{user_id}/{addressee_id}', 'App\Http\Controllers\API\UserController@subscribeToGroup')->name('user.api.subscribe_to_group');
    Route::put('user/unsubscribe_to_group/{user_id}/{addressee_id}', 'App\Http\Controllers\API\UserController@unsubscribeToGroup')->name('user.api.unsubscribe_to_group');
    Route::put('user/switch_status/{id}/{status_id}', 'App\Http\Controllers\API\UserController@switchStatus')->name('user.api.switch_status');
    Route::put('user/update_role/{id}', 'App\Http\Controllers\API\UserController@updateRole')->name('user.api.update_role');
    Route::put('user/update_password/{id}', 'App\Http\Controllers\API\UserController@updatePassword')->name('user.api.update_password');
    Route::put('user/update_avatar_picture/{id}', 'App\Http\Controllers\API\UserController@updateAvatarPicture')->name('user.api.update_avatar_picture');
    // Organization
    Route::get('organization/search/{data}', 'App\Http\Controllers\API\OrganizationController@search')->name('organization.api.search');
    Route::get('organization/find_all_by_owner/{data}', 'App\Http\Controllers\API\OrganizationController@findAllByOwner')->name('organization.api.find_all_by_owner');
    // Notification
    Route::get('notification/select_by_user/{user_id}', 'App\Http\Controllers\API\NotificationController@selectByUser')->name('notification.api.select_by_user');
    Route::get('notification/select_by_status_user/{status_id}/{user_id}', 'App\Http\Controllers\API\NotificationController@selectByStatusUser')->name('notification.api.select_by_status_user');
    Route::put('notification/switch_status/{id}/{status_id}', 'App\Http\Controllers\API\NotificationController@switchStatus')->name('notification.api.switch_status');
    Route::put('notification/mark_all_read/{user_id}', 'App\Http\Controllers\API\NotificationController@markAllRead')->name('notification.api.mark_all_read');
});
