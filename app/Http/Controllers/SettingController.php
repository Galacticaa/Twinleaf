<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settings = [
        'automatic_captchas' => false,
        'captcha_key' => '',
        'manual_captchas' => false,
        'captcha_refresh' => null,
        'captcha_timeout' => null,

        'hash_key' => '',
        'gmaps_key' => '',

        'login_delay' => null,
        'login_retries' => null,
        'altitude_cache' => false,
        'disable_version_check' => false,
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->action('SettingController@show', ['id' => 1]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        return view('settings')->with('settings', Setting::first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {
        foreach ($this->settings as $s => $default) {
            $setting->$s = $request->get($s, $default);
        }

        $setting->email_domains = explode("\n", preg_replace(
            "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n",
            $request->get('email_domains')
        ));

        $setting->save();

        return redirect()->route('settings.index');
    }
}
