<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::resource('settings', 'SettingController');

Route::prefix('services/rocketmap')->group(function () {
    Route::post('install', 'RocketMapController@download')->name('services.rm.download');
    Route::post('compile', 'RocketMapController@install')->name('services.rm.install');
    Route::post('clean/{map}', 'RocketMapController@clean')->name('services.rm.clean');
    Route::post('check/{area}', 'RocketMapController@check')->name('services.rm.check');
    Route::post('configure/{map}/{area?}', 'RocketMapController@configure')->name('services.rm.configure');
    Route::post('accounts/{area}/write', 'RocketMapController@writeAccounts')->name('services.rm.write_accounts');
    Route::post('start/{map}', 'RocketMapController@startMap')->name('services.rm.start');
    Route::post('start/area/{area}', 'RocketMapController@startArea')->name('services.rm.start-area');
    Route::post('stop/{map}', 'RocketMapController@stopMap')->name('services.rm.stop');
    Route::post('stop-area/{area}', 'RocketMapController@stopArea')->name('services.rm.stop-area');
    Route::post('restart/{map}', 'RocketMapController@restartMap')->name('services.rm.restart');
    Route::post('restart/area/{area}', 'RocketMapController@restartArea')->name('services.rm.restart-area');
});

Route::resource('maps', 'MapController');
Route::resource('maps/{map}/areas', 'MapAreaController', ['names' => [
    'create' => 'mapareas.create',
    'store' => 'mapareas.store',
    'show' => 'mapareas.show',
    'edit' => 'mapareas.edit',
    'update' => 'mapareas.update',
    'destroy' => 'mapareas.destroy',
]]);
