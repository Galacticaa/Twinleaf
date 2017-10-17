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
    })
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
                        <a class="pull-right">{{ $total = $area->accounts->count() }}</a>
                    </li>
                </ul>
                <a href="{{ route('mapareas.edit', ['map' => $area->map, 'area' => $area]) }}" class="btn btn-block btn-default">
                    <b>Edit Area</b>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-9">
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
                                <p>{{ round(($working / $total) * 100) }}% Working</p>
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
                                <p>{{ round(($blind / $total) * 100) }}% Blind</p>
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
                                <p>{{ round(($banned / $total) * 100) }}% Banned</p>
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
                            <td class="text-{{ $status }}">
                                <i class="fa fa-{{ $icon }}"></i>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
