<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Proxy;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('proxies.index')->with('proxies', Proxy::with('area')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Load proxies, stripping any blank lines
        $proxies = $request->get('proxies');
        $proxies = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $proxies);
        $proxies = explode("\n", $proxies);

        foreach ($proxies as $proxy) {
            $proxy = Proxy::firstOrNew(['url' => $proxy]);
            $proxy->save();
        }

        return redirect()->route('proxies.index');
    }
}
