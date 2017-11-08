@extends ('adminlte::page')

@section ('title', $area->name)

@section ('css')
<link href="//cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">
@stop

@section ('js')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function() {
        $('#accounts-table').DataTable()

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
            function fail(txt, useOwn = false) {
                if (useOwn) {
                    set_status('Install failed! ' + txt, false, true);
                } else {
                    set_status('Install failed' + (txt ? (' while ' + txt + '.') : '!'), false, true);
                }
                $('#installStatus').addClass('text-danger');
                $('.progress-bar').addClass('progress-bar-danger');
            }

            set_status('Checking installation status', 20);

            $.post('{{ route('services.rm.check', ['area' => $area]) }}', function (data) {
                if (data.success) {
                    set_status('Writing configuration for {{ $area->name }}', 35);

                    $.post('{{ route('services.rm.configure', ['map' => $area->map, 'area' => $area]) }}', function (data) {
                        if (data.success) {

                            set_status('Writing accounts file', 60);

                            $.post('{{ route('services.rm.write_accounts', ['area' => $area]) }}', function (data) {
                                set_status('Writing proxy file', 85);

                                $.post('{{ route('services.rm.write-proxies', ['area' => $area]) }}', function (data) {
                                    if (data.success) {
                                        $('#installWarning').remove();

                                        set_status('Installation complete!', 100);
                                    } else {
                                        fail(data.error, true);
                                    }
                                });
                            });
                        } else {
                            fail(data.error, true);
                        }
                    });
                } else {
                    fail(data.error, true);
                }
            });
        });

        $('#startScan').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.start-area', [
                'map' => $area->map,
                'area' => $area,
            ]) }}', function (data) {
                window.location.reload();
            });
        });

        $('#stopScan').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.stop-area', [
                'map' => $area->map,
                'area' => $area,
            ]) }}', function (data) {
                window.location.reload();
            });
        });

        $('#restartScan').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.rm.restart-area', [
                'map' => $area->map,
                'area' => $area,
            ]) }}', function (data) {
                window.location.reload();
            });
        });

        $('#regenerateArea').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('maps.areas.regenerate', [
                'map' => $map,
                'area' => $area,
            ]) }}', function (data) {
                window.location.reload();
            });
        });
    });

    function replaceAccount(account) {
        $(account).button('loading');
        var route = '{{ route('accounts.replace', ['account' => '--USERNAME--']) }}';

        $.post(route.replace('--USERNAME--', $(account).data('username')), function (data) {
            window.location.reload();
        });
    }
</script>
@stop

@section ('content_header')
<h1>{{ $map->name }}</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li><a href="#">Map Manager</a></li>
    <li><a href="/maps/{{ $map->slug }}">{{ $map->name }}</a></li>
    <li>Scan Areas</li>
    <li class="active">{{ $area->name }}</li>
</ol>
@stop

@section ('content')
<div class="row">
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <h3 class="profile-username text-center">{{ $area->name }}</h3>
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Accounts</b>
                        <a class="pull-right">
                            {{ $total = $area->accounts->count() }} / {{ $area->accounts_target }}
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Proxies</b>
                        <a class="pull-right">
                            {{ $area->proxies->count() }} / {{ $area->proxy_target }}
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Current Uptime</b>
                        <a class="pull-right">
                            {{ $area->human_uptime }}
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Record Uptime</b>
                        <a class="pull-right">
                            {{ $area->human_uptime_max }}
                        </a>
                    </li>
                </ul>
                <button id="regenerateArea" class="btn btn-block bg-purple"><b>Regenerate accounts</b></button>
                <a href="{{ route('maps.areas.edit', ['map' => $area->map, 'area' => $area]) }}" class="btn btn-block btn-default">
                    <b>Edit area settings</b>
                </a>
                @if ($area->isDown())
                <button id="startScan" class="btn btn-block btn-success"><b>Start scan</b></button>
                @else
                <button id="restartScan" class="btn btn-block btn-warning"><b>Restart scan</b></button>
                <button id="stopScan" class="btn btn-block btn-danger"><b>Stop scan</b></button>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-9">

        @if (!$area->map->isInstalled())
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <i class="fa fa-close"></i>
            </button>
            <h4><i class="fa fa-warning"></i> Something's Missing!</h4>
            <p>
                It would seem {{ $area->map->name }} isn't installed.
                It's probably worth fixing that before you try installing this scan area.
            </p>
        </div>
        @elseif (!$area->isInstalled())
        <div class="box box-danger" id="installWarning">
            <div class="box-header">
                <h3 class="box-title">Installation Required</h3>
            </div>
            <div class="box-body">
                <p class="lead">
                    Your map won't do much while it's not installed!<br>
                    It only takes a moment. Why not get it done?
                </p>
                <button class="btn btn-lg" data-toggle="modal" data-target="#installModal">Install {{ $area->name }}</button>
            </div>
        </div>
        <div id="installModal" class="modal fade" tabindex="-1" role="dialog" aria-labelled-by="installModalLabel" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="installModalLabel">Installing {{ $area->name }}</h4>
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
        @elseif ($area->isDown())
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <i class="fa fa-close"></i>
            </button>
            <h4><i class="fa fa-warning"></i> Scan Area Down!</h4>
            <p>It appears this area isn't running. Let's fix that! Go ahead and click Start Area on the left.</p>
        </div>
        @endif

        <div class="box box-default">
            <div class="box-header">
                <h3 class="box-title">Scan Accounts</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{ $total }}</h3>
                                <p>In Rotation</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ $working = $area->accounts->where('is_blind', '!=', true)->where('is_banned', '!=', true)->count() }}</h3>
                                <p>{{ $working ? round(($working / $total) * 100) : 0 }}% Working</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-orange">
                            <div class="inner">
                                <h3>{{ $blind = $area->accounts->where('is_blind', true)->where('is_banned', '!=', true)->count() }}</h3>
                                <p>{{ $blind ? round(($blind / $total) * 100) : 0 }}% Blind</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-eye-slash"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{ $banned = $area->accounts()->whereIsBanned(true)->count() }}</h3>
                                <p>{{ $banned ? round(($banned / $total) * 100) : 0 }}% Banned</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="accounts-table" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email Activation</th>
                            <th>Condition</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($area->accounts as $account)
                        <tr>
                            @php
                            if ($account->activated_at) {
                                $status = 'success';
                                $icon = 'check';
                            } elseif ($account->activated_at === null) {
                                $status = 'warning';
                                $icon = 'hourglass-half';
                            } else {
                                $status = 'danger';
                                $icon = 'times';
                            }
                            @endphp

                            <td>{{ $account->username }}</td>
                            <td class="text-{{ $status }}" data-order="{{ $account->activated_at }}">
                                <i class="fa fa-{{ $icon }}" title="{{ $account->activated_at }}"></i>
                                {{ $account->email }}
                            </td>
                            @if ($account->is_banned === 1)
                            <td class="text-danger">
                                <i class="fa fa-ban"></i> Banned
                            </td>
                            @elseif ($account->is_blind === 1)
                            <td class="text-warning">
                                <i class="fa fa-eye-slash"></i> Blind
                            </td>
                            @elseif ($account->is_banned === 0 || $account->is_blind === 0)
                            <td class="text-success">
                                <i class="fa fa-check"></i> Working
                            </td>
                            @else
                            <td class="text-muted">
                                <i class="fa fa-question"></i> Unused / Unknown
                            </td>
                            @endif
                            <td>
                                <button class="btn btn-xs btn-warning replace-account"
                                    data-username="{{ $account->username }}"
                                    onclick="replaceAccount(this)">
                                    <i class="fa fa-refresh"></i>
                                    Replace
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
