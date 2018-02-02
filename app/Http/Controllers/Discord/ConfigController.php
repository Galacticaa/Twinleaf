<?php

namespace Twinleaf\Http\Controllers\Discord;

use Twinleaf\Map;
use Twinleaf\Discord\Config;
use Twinleaf\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->action('Discord\ConfigController@show', ['id' => 1]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\Discord\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function show(Config $config)
    {
        return view('discord.config')->with('config', Config::first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\Discord\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Config $config)
    {
        $data = $request->validate([
            'bot_token' => 'nullable|string',
            'guild_id' => 'nullable|integer',
        ]);

        $config->update($data);

        return redirect()->route('discord.config.index');
    }
}
