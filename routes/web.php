<?php

use Twinleaf\Services\KinanCore;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::post('accounts/{account}/replace', 'AccountController@replace')->name('accounts.replace');
Route::post('maps/{map}/areas/{area}/regenerate', 'MapAreaController@regenerate')->name('maps.areas.regenerate');

Route::prefix('proxies')->group(function () {
    Route::get('/', 'ProxyController@index')->name('proxies.index');
    Route::post('import', 'ProxyController@import')->name('proxies.import');
    Route::get('check', 'ProxyController@check')->name('proxies.check');
    Route::post('check/{proxy}/ptc', 'ProxyController@checkPtc')->name('proxies.check-ptc');
    Route::post('check/{proxy}/pogo', 'ProxyController@checkPogo')->name('proxies.check-pogo');
});

Route::get('tasks', function () {
    return view('tasks')->with('creator', new KinanCore);
})->name('tasks');

Route::resource('settings', 'SettingController');

Route::resource('maps', 'MapController');
Route::resource('maps.areas', 'MapAreaController');

Route::post('services/kinan/configure', function () {
    return [
        'success' => (new KinanCore)->configure(),
    ];
})->name('services.kinan.configure');

Route::prefix('services/rocketmap')->group(function () {
    Route::post('install', 'RocketMapController@download')->name('services.rm.download');
    Route::post('compile', 'RocketMapController@install')->name('services.rm.install');
    Route::post('clean/{map}', 'RocketMapController@clean')->name('services.rm.clean');
    Route::post('check/{area}', 'RocketMapController@check')->name('services.rm.check');
    Route::post('configure/{map}/{area?}', 'RocketMapController@configure')->name('services.rm.configure');
    Route::post('accounts/{area}/write', 'RocketMapController@writeAccounts')->name('services.rm.write_accounts');
    Route::post('proxies/{area}/write', 'RocketMapController@writeProxies')->name('services.rm.write-proxies');
    Route::post('start/{map}', 'RocketMapController@startMap')->name('services.rm.start');
    Route::post('start/{map}/{area}', 'RocketMapController@startArea')->name('services.rm.start-area');
    Route::post('stop/{map}', 'RocketMapController@stopMap')->name('services.rm.stop');
    Route::post('stop/{map}/{area}', 'RocketMapController@stopArea')->name('services.rm.stop-area');
    Route::post('restart/{map}', 'RocketMapController@restartMap')->name('services.rm.restart');
    Route::post('restart/{map}/{area}', 'RocketMapController@restartArea')->name('services.rm.restart-area');
});
