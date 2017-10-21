<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
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
        $settings = [
            'captcha_solving' => null, 'captcha_key' => '',
            'hash_key' => '', 'gmaps_key' => '',
        ];

        foreach ($settings as $s => $default) {
            $setting->$s = $request->get($s, $default);
        }

        $setting->email_domains = explode("\n", $request->get('email_domains'));

        $setting->save();

        return redirect()->route('settings.index');
    }
}
