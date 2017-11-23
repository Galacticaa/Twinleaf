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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

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

        $('#activateLures').bind('click', function() {
            $.post('{{ route('long-lures.enable') }}', function(data) {
                if (data.success === true) {
                    $('.lure-container').toggleClass('hidden');
                }
            });
        });

        $('#deactivateLures').bind('click', function() {
            $.post('{{ route('long-lures.disable') }}', function(data) {
                if (data.success === true) {
                    $('.lure-container').toggleClass('hidden');
                }
            });
        });
    });
</script>
@stop

@section ('content_header')
<h1>Dashboard</h1>
@stop

@section ('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Lure Duration</h3>
            </div>
            <div class="lure-container box-body {{ $settings->long_lures ? '' : 'hidden' }}">
                <button id="deactivateLures" class="btn btn-danger pull-right">Deactivate</button>
                <p class="lead">6-hour Lures are <span class="text-success">active</span>!</p>
            </div>
            <div class="lure-container box-body {{ $settings->long_lures ? 'hidden' : '' }}">
                <button id="activateLures" class="btn btn-success pull-right">Activate</button>
                <p class="lead">6-hour Lures are <span class="text-danger">inactive</span>!</p>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Current Locations</h3>
            </div>
            <div class="box-body no-padding">
                <div id="map"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h3 style="margin-top: 0;">Recent Activity</h3>
        @if ($logsByDate)
        <ul class="timeline">
            @foreach ($logsByDate as $date => $logs)
            <li class="time-label">
                <span class="bg-purple">{{ (new Carbon\Carbon($date))->toFormattedDateString() }}</span>
            </li>
            @foreach ($logs as $log)
            <li>
                @php $data = json_decode($log->data) @endphp
                <i class="fa fa-{{ $log->getIcon() }}"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> {{ $log->created_at }}</span>

                    <h3 class="timeline-header">{!! $log->description !!}</h3>
                </div>
            </li>
            @endforeach
            @endforeach
            <li><i class="fa fa-clock-o bg-gray"></i></li>
        </ul>
        @else
        <p class="lead">No history to display.</p>
        @endif
    </div>
</div>
@stop
