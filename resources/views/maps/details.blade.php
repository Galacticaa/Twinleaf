@extends ('adminlte::page')

@section ('title', $map->name)

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
                        <b>Scan Areas</b>
                        <a class="pull-right">{{ count($map->areas) }}</a>
                    </li>
                </ul>
                <a class="btn btn-block bg-purple" href="{{ route('mapareas.create', ['map' => $map->code]) }}">
                    <b>New scan area</b>
                </a>
                <a href="{{ route('maps.edit', ['map' => $map]) }}" class="btn btn-block btn-default">
                    <b>Edit map settings</b>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-9">
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
                            <td>
                                <a class="btn btn-xs btn-default pull-right"
                                    href="{{ route('mapareas.show', ['map' => $map->code, 'slug' => $area->slug]) }}">
                                    Details
                                </a>
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
