<?php

use Twinleaf\Services\KinanCore;
use Twinleaf\Services\KinanMail;
use Twinleaf\Map;
use Twinleaf\Setting;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', function () {
    $logs = Activity::orderBy('updated_at', 'desc')->limit(10)->get();
    $logsByDate = [];

    foreach ($logs as $log) {
        $date = $log->updated_at->toDateString();

        if (!array_key_exists($date, $logsByDate)) {
            $logsByDate[$date] = [];
        }

        $logsByDate[$date][] = $log;
    }

    return view('dashboard')
            ->with('maps', Map::with('areas')->get())
            ->with('settings', Setting::first())
            ->with('logsByDate', $logsByDate);
})->name('dashboard');

Route::prefix('proxies')->group(function () {
    Route::get('/', 'ProxyController@index')->name('proxies.index');
    Route::post('import', 'ProxyController@import')->name('proxies.import');
    Route::get('check', 'ProxyController@check')->name('proxies.check');
    Route::post('check/{proxy}/ptc', 'ProxyController@checkPtc')->name('proxies.check-ptc');
    Route::post('check/{proxy}/pogo', 'ProxyController@checkPogo')->name('proxies.check-pogo');
});

Route::prefix('discord')->name('discord.')->group(function () {
    Route::get('clean', 'DiscordController@cleanup')->name('clean');
    Route::post('clean/channels', 'DiscordController@cleanChannels')->name('purge-channels');
    Route::post('clean/roles', 'DiscordController@cleanRoles')->name('purge-roles');
    Route::resource('config', 'Discord\ConfigController');
});

Route::get('tasks', function () {
    $maps = Map::where('is_enabled', '=', true)->get();

    // Create a failsafe just in case we fail to get a PID
    $processes = [0 => ['cpu' => '--', 'mem' => '--', 'cmd' => '']];

    // Load resources for all maps at once
    exec("ps -U twinleaf -u twinleaf -o pid,%cpu,%mem,cmd", $lines);

    // Activator is run via sudo
    exec("ps -U root -u root -o pid,%cpu,%mem,cmd", $lines);

    foreach ($lines as $line) {
        list($pid, $cpu, $mem, $cmd) = preg_split('/\s+/', trim($line), 4);

        $processes[$pid] = compact('cpu', 'mem', 'cmd');
    }

    return view('tasks', [
        'creator' => new KinanCore,
        'activator' => new KinanMail,
        'processes' => $processes,
        'maps' => $maps,
    ]);
})->name('tasks');

Route::prefix('settings/lures')->group(function () {
    Route::post('enable', 'SettingController@enableLongLures')->name('long-lures.enable');
    Route::post('disable', 'SettingController@disableLongLures')->name('long-lures.disable');
});
Route::resource('settings', 'SettingController');


Route::post('accounts/{account}/replace', 'AccountController@replace')->name('accounts.replace');
Route::post('maps/{map}/areas/{area}/regenerate', 'MapAreaController@regenerate')->name('maps.areas.regenerate');
Route::post('maps/{map}/check-config', 'MapController@checkConfig')->name('maps.check-config');
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
