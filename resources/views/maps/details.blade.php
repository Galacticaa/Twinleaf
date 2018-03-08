@extends ('layouts.twinleaf')

@section ('title', $map->name)

@section ('js')
@parent
<script>
    $(function() {
        $('#install-map').progressPopup({
            title: 'Installing {{ $map->name }}',
            steps: [{
                text: 'Downloading RocketMap from Github',
                url: '{{ route('services.rm.download') }}',
                status: 25
            }, {
                text: 'Installing RocketMap packages',
                url: '{{ route('services.rm.install') }}',
                status: 45
            }, {
                text: 'Cleaning old files',
                url: '{{ route('services.rm.clean', ['map' => $map]) }}',
                status: 55
            }, {
                text: 'Installing the map',
                url: '{{ route('services.rm.configure', ['map' => $map]) }}',
                status: 75
            }, {
                text: 'Installation complete!',
                done: function () {
                    $('#installWarning').remove();
                }, status: 100
            }]
        });

        @if ($map->isInstalled() && !$map->hasLatestConfig())
        $('#applyConfig').progressPopup({
            title: 'Updating {{ $map->name }}',
            steps: [{
                text: 'Checking configuration',
                url: '{{ route('maps.check-config', ['map' => $map]) }}',
                status: 20
            }, {
                text: 'Writing new configuration',
                url: '{{ route('services.rm.configure', ['map' => $map]) }}',
                status: 60
            }, {
                text: 'Map successfully updated!',
                done: function () {
                    $('#applyConfig').remove();
                }, status: 100
            }]
        });
        @endunless

        @foreach ($map->areas as $area)
        @if ($map->isInstalled() && !$map->hasLatestConfig())
        $('.area-apply[data-slug="{{ $area->slug }}"]').progressPopup({
            title: 'Updating {{ $area->name }}',
            steps: [{
                text: 'Checking installation status',
                url: '{{ route('services.rm.check', ['area' => $area]) }}',
                status: 20
            }, {
                text: 'Writing configuration for {{ $area->name }}',
                url: '{{ route('services.rm.configure', ['map' => $area->map, 'area' => $area]) }}',
                status: 35
            }, {
                text: 'Writing accounts file',
                url: '{{ route('services.rm.write_accounts', ['area' => $area]) }}',
                status: 60
            }, {
                text: 'Writing proxy file',
                url: '{{ route('services.rm.write-proxies', ['area' => $area]) }}',
                status: 85
            }, {
                text: 'Successfully updated area {{ $area->name }}',
                done: function () {
                    $('.area-apply[data-slug="{{ $area->slug }}"]').parent()
                        .removeClass('text-warning').addClass('text-success').text('latest config');
                }, status: 100
            }]
        });
        @endunless
        @endforeach

        $('#startMap').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.start', ['map' => $map]) }}', function (data) {
                window.location.reload();
            });
        });

        $('#stopMap').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.stop', ['map' => $map]) }}', function (data) {
                window.location.reload();
            });
        });

        $('#restartMap').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.restart', ['map' => $map]) }}', function (data) {
                window.location.reload();
            });
        });
    });
</script>
@stop

@section ('content_header')
<h1>Map Manager</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li>Map Manager</li>
    <li class="active">{{ $map->name }}</li>
</ol>
@stop

@section ('content')
<div class="row">
    <div class="col-md-3">

        <div class="box box-primary">
            <div class="box-body box-profile">
                <h3 class="profile-username text-center">{{ $map->name }}</h3>
                <p class="text-muted text-center">
                    <a class="btn btn-block" href="{{ $map->url }}" target="_blank">
                        Open website
                        <sup><i class="fa fa-external-link"></i></sup>
                    </a>
                </p>
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Accounts</b>
                        <a class="pull-right">{{ $map->accounts->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Proxies</b>
                        <a class="pull-right">{{ $map->proxies->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Scan Areas</b>
                        <a class="pull-right">{{ count($map->areas) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Current Uptime</b>
                        <a class="pull-right">
                            {{ $map->human_uptime }}
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Record Uptime</b>
                        <a class="pull-right">
                            {{ $map->human_uptime_max }}
                        </a>
                    </li>
                </ul>
                <a class="btn btn-block bg-purple" href="{{ route('maps.areas.create', ['map' => $map->code]) }}">
                    <b>New scan area</b>
                </a>
                <a href="{{ route('maps.edit', ['map' => $map]) }}" class="btn btn-block btn-default">
                    <b>Edit map settings</b>
                </a>
                @if ($map->isInstalled())
                @if (!$map->hasLatestConfig())
                <button id="applyConfig" class="btn btn-block btn-success">
                    <b>Apply config</b>
                </button>
                @endif
                @if ($map->isDown())
                <button id="startMap" class="btn btn-block btn-success" data-loading-text="<i class='fa fa-spinner'></i> Starting map&hellip;"><b>Start map</b></button>
                @else
                <button id="restartMap" class="btn btn-block btn-warning"><b>Restart map</b></button>
                <button id="stopMap" class="btn btn-block btn-danger"><b>Stop map</b></button>
                @endif
                @endif
            </div>
        </div>

    </div>
    <div class="col-md-9">

        @if (!$map->isInstalled())
        <div class="box box-danger" id="installWarning">
            <div class="box-header">
                <h3 class="box-title">Installation Required</h3>
            </div>
            <div class="box-body">
                <p class="lead">
                    Your map won't do much while it's not installed!<br>
                    It only takes a moment. Why not get it done?
                </p>
                <button class="btn btn-lg" id="install-map">Install {{ $map->name }}</button>
            </div>
        </div>
        @elseif ($map->isDown())
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <i class="fa fa-close"></i>
            </button>
            <h4><i class="fa fa-warning"></i> Map is Down!</h4>
            <p>It appears this map isn't running. Let's fix that! Go ahead and click Start Area on the left.</p>
        </div>
        @endif

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Scan Areas</h3>
                <div class="box-tools pull-right">
                    <a class="btn bg-purple" href="{{ route('maps.areas.create', ['map' => $map->code]) }}">
                        Add new Area
                    </a>
                </div>
            </div>
            <div class="box-body no-padding">
                @if (!$map->areas->count())
                <p class="lead text-center">
                    {{ $map->name }} has no areas &#x1F61E;
                </p>
                @else
                <table class="table">
                    <tbody>
                        @foreach ($map->areas as $area)
                        <tr>
                            <td class="{{ $area->is_enabled ? '' : 'text-muted' }}"><b>{{ $area->name }}</b></td>
                            <td>{{ $area->accounts->count() }} accounts</td>
                            @if ($area->isInstalled() && $area->hasLatestConfig())
                            <td class="text-success text-center">latest config</td>
                            @elseif (!$area->accounts()->count())
                            <td class="text-warning text-center">needs accounts</td>
                            @else
                            <td class="text-center">
                                <button class="area-apply btn btn-xs btn-primary" data-slug="{{ $area->slug }}">Apply config</button>
                            </td>
                            @endif
                            @if (!$area->isInstalled())
                            <td class="text-muted"><i class="fa fa-circle"></i> Not installed</td>
                            @elseif ($area->isUp())
                            <td class="text-success"><i class="fa fa-circle"></i> Running</td>
                            @else
                            <td class="text-danger"><i class="fa fa-circle"></i> Not running!</td>
                            @endif
                            <td>
                                <div class="btn-group pull-right">
                                    <a class="btn btn-xs btn-default"
                                        href="{{ route('maps.areas.show', ['map' => $map->code, 'slug' => $area->slug]) }}">
                                        Details
                                    </a>
                                    <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="{{ route('maps.areas.edit', [
                                                'map' => $map, 'area' => $area ]) }}">
                                                Edit {{ $area->name }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        <h3 class="box-title">Activity Log</h3>
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
                    @if ($log->details)
                    <div class="timeline-body">{{ $log->details }}</div>
                    @endif
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
