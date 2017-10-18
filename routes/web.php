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

Route::prefix('services/rocketmap')->group(function () {
    Route::post('install', 'RocketMapController@download')->name('services.rm.download');
    Route::post('compile', 'RocketMapController@install')->name('services.rm.install');
    Route::post('clean/{map}', 'RocketMapController@clean')->name('services.rm.clean');
    Route::post('configure/{map}', 'RocketMapController@configure')->name('services.rm.configure');
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

