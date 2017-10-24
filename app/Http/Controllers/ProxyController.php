<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Proxy;

use Carbon\Carbon;
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
    public function import(Request $request)
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

    public function check(Request $request)
    {
        return view('proxies.check')->with('proxies', Proxy::dueBanCheck()->get());
    }

    public function checkPtc(Proxy $proxy)
    {
        return $this->checkProxy('ptc', $proxy);
    }

    public function checkPogo(Proxy $proxy)
    {
        return $this->checkProxy('pogo', $proxy);
    }

    protected function checkProxy(string $service, Proxy $proxy)
    {
        sleep(rand(1, 3));

        $result = exec(base_path('bin/bancheck')." {$service} '{$proxy->url}'");
        list($type, $code) = explode(':', $result);

        $banKey = $service.'_ban';

        $bannedBefore = $proxy->$banKey;
        $proxy->$banKey = !($type == 'HTTP' && (int) $code == 200);

        if ($service == 'pogo') {
            $proxy->checked_at = Carbon::now();
        }

        if ($service == 'pogo' || $bannedBefore != $proxy->$banKey) {
            $proxy->save();
        }

        return [
            'status' => $type == 'CURL' ? 'C'.$code : $code,
            'proxy' => $proxy
        ];
    }
}
