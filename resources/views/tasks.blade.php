@extends ('layouts.twinleaf')

@section ('title', 'Task Manager')

@section ('js')
@parent
<script>
    $(function() {
        $('#tasks-table').DataTable();

        $('.reconfigure-creator').on('click', function (e) {
            $(this).button('loading');

            $.post('{{ route('services.kinan.configure') }}', function (data) {
                window.location.reload();
            });
        });
    });
</script>
@stop

@section ('content_header')
<h1>Task Manager</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">Task Manager</li>
</ol>
@stop

@section ('content')
<div class="box box-primary">
    <div class="box-body">
        <table id="tasks-table" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>PID</th>
                    <th>Process</th>
                    <th>Status</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($creator->getPids() ?: [0] as $creatorPid)
                <tr>
                    <td>{{ $creatorPid }}</td>
                    <td>Account Creator</td>
                    @if (!$creator->isInstalled())
                    <td class="text-danger"><i class="fa fa-circle"></i> Not installed!</td>
                    <td></td>
                    <td></td>
                    @elseif (!$creator->isRunning())
                    <td class="text-muted"><i class="fa fa-circle"></i> Inactive</td>
                    <td></td>
                    <td></td>
                    @else
                    <td class="text-success"><i class="fa fa-circle"></i> Working</td>
                    <td>{{ $processes[$creatorPid]['cpu'] }}%</td>
                    <td>{{ $processes[$creatorPid]['mem'] }}%</td>
                    @endif
                    <td>
                        <button class="btn btn-xs bg-purple reconfigure-creator">
                            <i class="fa fa-cog"></i>
                            @if ($creator->isInstalled()) Reinstall @else Install @endif configuration
                        </button>
                    </td>
                </tr>
                @endforeach

                @foreach ($activator->getPids() ?: [0] as $pid)
                <tr>
                    <td>{{ $pid }}</td>
                    <td>Account Activator</td>
                    @if (!$activator->isInstalled())
                    <td class="text-danger"><i class="fa fa-circle"></i> Not installed!</td>
                    <td></td>
                    <td></td>
                    @elseif ($activator->isRunning())
                    <td class="text-muted"><i class="fa fa-circle"></i> Inactive</td>
                    <td></td>
                    <td></td>
                    @else
                    <td class="text-success"><i class="fa fa-circle"></i> Working</td>
                    <td>{{ $processes[$pid]['cpu'] }}%</td>
                    <td>{{ $processes[$pid]['mem'] }}%</td>
                    @endif
                    <td></td>
                </tr>
                @endforeach

                @foreach ($maps as $map)
                @foreach ($map->getPids() ?: [0] as $mapPid)
                <tr>
                    <td>{{ $mapPid }}</td>
                    <td>Map: <a href="/maps/{{ $map->code }}">{{ $map->name }}</a></td>
                    @if ($map->isUp())
                    <td class="text-success"><i class="fa fa-circle"></i> Running</td>
                    <td>{{ $processes[$mapPid]['cpu'] }}%</td>
                    <td>{{ $processes[$mapPid]['mem'] }}%</td>
                    @else
                    <td class="text-danger"><i class="fa fa-circle"></i> Not running!</td>
                    <td></td>
                    <td></td>
                    @endif
                    <td></td>
                </tr>
                @foreach ($map->areas()->whereIsEnabled(true)->get() as $area)
                @foreach ($area->getPids() ?: [0] as $areaPid)
                <tr>
                    <td>{{ $areaPid }}</td>
                    <td>Scan: <a href="/maps/{{ $map->code }}/areas/{{ $area->slug }}">
                            {{ $map->name }} / {{ $area->name }}
                    </a></td>
                    @if ($area->isUp())
                    <td class="text-success"><i class="fa fa-circle"></i> Running!</td>
                    <td>{{ $processes[$areaPid]['cpu'] }}%</td>
                    <td>{{ $processes[$areaPid]['mem'] }}%</td>
                    @else
                    <td class="text-danger"><i class="fa fa-circle"></i> Not running!</td>
                    <td></td>
                    <td></td>
                    @endif
                    <td></td>
                </tr>
                @endforeach
                @endforeach
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
