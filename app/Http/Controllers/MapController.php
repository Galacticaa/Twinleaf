<?php

namespace Twinleaf\Http\Controllers;

use Activity;
use Exception;
use Twinleaf\Map;
use Twinleaf\Http\Requests\StoreMap;

class MapController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('maps.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Twinleaf\Http\Requests\StoreMap  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMap $request)
    {
        $map = Map::create($request->all());

        return redirect()->route('maps.show', ['map' => $map]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function show(Map $map)
    {
        $logs = Activity::whereContentType('map')
                        ->whereContentId($map->id)
                        ->orderBy('updated_at', 'desc')
                        ->limit(50)
                        ->get();

        $logsByDate = [];

        foreach ($logs as $log) {
            $date = $log->updated_at->toDateString();

            if (!array_key_exists($date, $logsByDate)) {
                $logsByDate[$date] = [];
            }

            $logsByDate[$date][] = $log;
        }

        return view('maps.details')->with([
            'map' => $map,
            'logsByDate' => $logsByDate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function edit(Map $map)
    {
        return view('maps.edit')->with('map', $map);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function update(StoreMap $request, Map $map)
    {
        $map->fill($request->all());
        $map->save();

        return redirect()->route('maps.show', ['map' => $map]);
    }

    /**
     * Run a diff against stored vs current configs
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function checkConfig(Map $map)
    {
        usleep(0.7 * 1000000);

        try {
            if ($map->hasLatestConfig()) {
                $error = "Config for {$map->name} is already up to date!";
            } else {
                $success = true;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success ?? false,
            'error' => $error ?? null,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function destroy(Map $map)
    {
        $map->delete();

        return redirect()->route('dashboard');
    }
}
