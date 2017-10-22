@extends ('adminlte::page')

@section ('title', $map->name)

@section ('js')
<script>
    $(function() {
        function set_status(txt, val, forceButton = false) {
            $('#installStatus').html(txt + '&hellip;');

            if (val === -1) {
                $('.progress-bar').addClass('progress-bar-danger');
            } else {
                $('.progress-bar').removeClass('progress-bar-danger').width(val+'%')

                if (val >= 100) {
                    $('.progress-bar').removeClass('active');
                }
            }

            if (val >= 100 || forceButton) {
                var closebtn = $('<button/>').addClass('btn btn-default pull-right')
                                             .attr('data-dismiss', 'modal')
                                             .text('Close');
                $('.modal-footer', '#installModal').append(closebtn);
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $('#installModal').on('show.bs.modal', function (e) {
            $('.modal-footer', '#installModal').empty();

            set_status('Loading', 0);
        });

        $('#installModal').on('shown.bs.modal', function (e) {
            function fail(txt) {
                set_status('Install failed' + (txt ? (' while ' + txt + '.') : '!'), false, true);
                $('.progress-bar').addClass('progress-bar-danger');
            }

            set_status('Downloading RocketMap from Github...', 25);

            $.post('{{ route('services.rm.download') }}', function (data) {
                if (data.downloaded) {
                    set_status('Installing RocketMap packages...', 45);

                    $.post('{{ route('services.rm.install') }}', function (data) {
                        if (data.installed) {
                            set_status('Cleaning old files...', 55);

                            $.post('{{ route('services.rm.clean', ['map' => $map]) }}', function (data) {
                                set_status('Installing the map', 75);

                                $.post('{{ route('services.rm.configure', ['map' => $map]) }}', function (data) {
                                    if (data.written) {
                                        $('#installWarning').remove();

                                        set_status('Installation complete!', 100);
                                    } else {
                                        fail('writing config files');
                                    }
                                });
                            });
                        } else {
                            fail('installing RocketMap');
                        }
                    });
                } else {
                    fail('downloading RocketMap');
                }
            });
        });

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
                <a class="btn btn-block bg-purple" href="{{ route('mapareas.create', ['map' => $map->code]) }}">
                    <b>New scan area</b>
                </a>
                <a href="{{ route('maps.edit', ['map' => $map]) }}" class="btn btn-block btn-default">
                    <b>Edit map settings</b>
                </a>
                @if ($map->isDown())
                <button id="startMap" class="btn btn-block btn-success" data-loading-text="<i class='fa fa-spinner'></i> Starting map&hellip;"><b>Start map</b></button>
                @else
                <button id="restartMap" class="btn btn-block btn-warning"><b>Restart map</b></button>
                <button id="stopMap" class="btn btn-block btn-danger"><b>Stop map</b></button>
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
                <button class="btn btn-lg" data-toggle="modal" data-target="#installModal">Install {{ $map->name }}</button>
            </div>
        </div>
        <div id="installModal" class="modal fade" tabindex="-1" role="dialog" aria-labelled-by="installModalLabel" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="installModalLabel">Installing {{ $map->name }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="progress progress-sm">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemax="100" style="min-width: 3em; width: 0%;"></div>
                        </div>
                        <p class="lead text-center" id="installStatus">Loading&hellip;</p>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
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
                    <a class="btn bg-purple" href="{{ route('mapareas.create', ['map' => $map->code]) }}">
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
                            <td><b>{{ $area->name }}</b></td>
                            <td>{{ $area->accounts->count() }} accounts</td>
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
                                        href="{{ route('mapareas.show', ['map' => $map->code, 'slug' => $area->slug]) }}">
                                        Details
                                    </a>
                                    <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="{{ route('mapareas.edit', [
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
    </div>
</div>
@stop
