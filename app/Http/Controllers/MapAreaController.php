<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Accounts\Generator;
use Twinleaf\Http\Requests\StoreMapArea;

class MapAreaController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Map $map)
    {
        return view('maps.areas.create')->with('map', $map);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMapArea $request)
    {
        $area = MapArea::create($request->all());
        $map = Map::find($area->map_id);

        return redirect()->route('mapareas.show', [
            'map' => $map,
            'area' => $area,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function show(Map $map, MapArea $area)
    {
        return view('maps.areas.details')
                ->with('map', $map)
                ->with('area', $area);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function edit(Map $map, MapArea $area)
    {
        return view('maps.areas.edit')
                ->with('map', $map)
                ->with('area', $area);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function update(StoreMapArea $request, Map $map, MapArea $area)
    {
        $area->fill($request->all());
        $area->save();

        return redirect()->route('mapareas.show', [
            'map' => $area->map,
            'area' => $area,
        ]);
    }

    /**
     * Replace all accounts for the specified area.
     * @param \Twinleaf\Map  $map
     * @param \Twinleaf\MapArea  $area
     * @return \Illuminate\Http\Response
     */
    public function regenerate(Map $map, MapArea $area)
    {
        foreach ($area->accounts as $account) {
            $account->area()->dissociate();
            $account->save();
        }

        return (new Generator($area))->generate();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Map $map, MapArea $area)
    {
        $area->delete();

        return redirect()->route('maps.show', ['map' => $map]);
    }
}
