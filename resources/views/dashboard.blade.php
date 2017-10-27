@extends ('adminlte::page')

@section ('title', 'Dashboard')

@section ('css')
<link rel="stylesheet" type="text/css" href="http://jvectormap.com/css/jquery-jvectormap-2.0.3.css"/>
<style type="text/css">
    #map {
        height: 560px;
    }
</style>
@stop

@section ('js')
<script src="http://jvectormap.com/js/jquery-jvectormap-2.0.3.min.js"></script>
<script src="http://jvectormap.com/js/jquery-jvectormap-uk_regions-merc.js"></script>
<script>
    $(function() {
        $('#map').vectorMap({
            map: 'uk_regions_merc',
            backgroundColor: 'transparent',
            regionStyle: {
                initial: {
                    fill: 'rgba(210, 214, 222, 1)',
                    'fill-opacity': 1,
                }
            },
            markerStyle: {
                initial: {
                    fill: '#1f88f5',
                    stroke: '#383f47'
                }
            },
            markers: [
                @foreach ($maps as $i => $map)
                @if ($i == 0) { @else , { @endif latLng: [{{ $map->location }}], name: '{{ $map->name }}', style: { r: '14px' }
                @foreach ($map->areas as $area)
                }, { latLng: [{{ $area->location }}], name: '{{ $area->name }}', style: { fill: 'orange', r: '{{ $area->radius * .3 }}px' }
                @endforeach
                }
                @endforeach
            ]
        });
    });
</script>
@stop

@section ('content_header')
<h1>Dashboard</h1>
@stop

@section ('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Current Locations</h3>
    </div>
    <div class="box-body no-padding">
        <div id="map"></div>
    </div>
</div>
@stop
