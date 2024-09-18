<?php
/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| ROUTES FOR EVERY ROLES
|--------------------------------------------------------------------------
*/
// Generate symbolic link
Route::get('/symlink', function () { return view('symlink'); })->name('generate_symlink');
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/language/{locale}', [HomeController::class, 'changeLanguage'])->name('change_language');
Route::get('/notifications', [HomeController::class, 'notification'])->name('notification.home');
Route::get('/about', [HomeController::class, 'about'])->name('about.home');
Route::get('/about/{entity}', [HomeController::class, 'aboutEntity'])->name('about.entity');
Route::get('/books', [HomeController::class, 'book'])->name('book.home');
Route::get('/books/{id}', [HomeController::class, 'bookDatas'])->whereNumber('id')->name('book.datas');
Route::get('/newspapers', [HomeController::class, 'newspaper'])->name('newspaper.home');
Route::get('/newspapers/{id}', [HomeController::class, 'newspaperDatas'])->whereNumber('id')->name('newspaper.datas');
Route::get('/maps', [HomeController::class, 'map'])->name('map.home');
Route::get('/maps/{id}', [HomeController::class, 'mapDatas'])->whereNumber('id')->name('map.datas');
Route::get('/medias', [HomeController::class, 'media'])->name('media.home');
Route::get('/medias/{id}', [HomeController::class, 'mediaDatas'])->whereNumber('id')->name('media.datas');
// Account
Route::get('/account', [AccountController::class, 'account'])->name('account');
Route::post('/account', [AccountController::class, 'updateAccount']);
Route::get('/account/{entity}', [AccountController::class, 'accountEntity'])->name('account.entity');
Route::post('/account/{entity}', [AccountController::class, 'updateAccountEntity']);
Route::get('/account/{entity}/{id}', [AccountController::class, 'accountEntityDatas'])->whereNumber('id')->name('account.entity.datas');
Route::post('/account/{entity}/{id}', [AccountController::class, 'updateAccountEntityDatas']);
// Subscription
Route::get('/subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
Route::post('/subscribe', [HomeController::class, 'runSubscribe']);
Route::get('/transaction_waiting', [HomeController::class, 'transactionWaiting'])->name('transaction.waiting');
Route::get('/transaction_message/{orderNumber}/{userId}', [HomeController::class, 'transactionMessage'])->name('transaction.message');
Route::get('/subscribed/{amount}/{currency}/{code}/{user_id}', [HomeController::class, 'subscribed'])->whereNumber(['amount', 'code'])->name('subscribed');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Admin"
|--------------------------------------------------------------------------
*/
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.home');
Route::get('/role', [AdminController::class, 'role'])->name('admin.role.home');
Route::get('/role/{id}', [AdminController::class, 'roleDatas'])->whereNumber('id')->name('admin.role.datas');
Route::get('/group', [AdminController::class, 'group'])->name('admin.group.home');
Route::get('/group/{id}', [AdminController::class, 'groupDatas'])->whereNumber('id')->name('admin.group.datas');
Route::get('/group/{entity}', [AdminController::class, 'groupEntity'])->name('admin.group.entity');
Route::get('/group/{entity}/{id}', [AdminController::class, 'groupEntityDatas'])->whereNumber('id')->name('admin.group.entity.datas');
Route::get('/subscription', [AdminController::class, 'subscription'])->name('admin.subscription.home');
Route::get('/subscription/{id}', [AdminController::class, 'subscriptionDatas'])->whereNumber('id')->name('admin.subscription.datas');
Route::get('/work', [AdminController::class, 'work'])->name('admin.work.home');
Route::post('/work', [AdminController::class, 'addWork']);
Route::get('/work/{id}', [AdminController::class, 'workDatas'])->whereNumber('id')->name('admin.work.datas');
Route::get('/partners', [AdminController::class, 'partners'])->name('admin.partners.home');
Route::get('/partners/{id}', [AdminController::class, 'partnerDatas'])->whereNumber('id')->name('admin.partners.datas');
Route::get('/users', [AdminController::class, 'users'])->name('admin.users.home');
Route::get('/users/{id}', [AdminController::class, 'usersDatas'])->whereNumber('id')->name('admin.users.datas');

require __DIR__.'/auth.php';
