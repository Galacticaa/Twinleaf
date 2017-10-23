@extends ('adminlte::page')

@section ('title', 'Editing '.$area->name)

@section ('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/purple.css" rel="stylesheet">
@stop

@section ('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script>
    $(function() {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-purple'
        });
    });
</script>
@stop

@section ('content_header')
<h1>Editing {{ $area->name }}</h1>
<ol class="breadcrumb">
    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Map Manager</a></li>
    <li><a href="{{ route('maps.show', ['map' => $map->code]) }}">{{ $map->name }}</a></li>
    <li class="active">Edit Scan Area</li>
</ol>
@stop

@section ('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form role="form" method="POST" action="{{ route('mapareas.update', ['map' => $map, 'area' => $area]) }}">
    {{ csrf_field() }}
    {{ method_field('PUT') }}
    <input type="hidden" name="map_id" value="{{ $map->id }}">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Basic Details</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formName">Name</label>
                        <input type="text" class="form-control" id="formName" placeholder="Santa Monica" name="name" value="{{ $area->name }}">
                        <p class="help-block">
                            Pick a name, any name&hellip; Used in the map's menu bar, choose wisely!
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formSlug">Slug</label>
                        <input type="text" class="form-control" id="formSlug" placeholder="santa-monica" name="slug" value="{{ $area->slug }}">
                        <p class="help-block">
                            This should be a unique "codename" that can identify the map.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h2 class="page-header">Map Settings</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Scan Area</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="formLocation">Location</label>
                                <input type="text" class="form-control" id="formLocation" placeholder="35.31233, 138.5892" name="location" value="{{ $area->location }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="formRadius">Radius (steps)</label>
                                <input type="number" min="1" required class="form-control" id="formRadius" name="radius" value="{{ $area->radius }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resources</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="formAccountsTarget">Accounts</label>
                                <input type="number" min="0" required class="form-control" id="formAccountsTarget" name="accounts_target" value="{{ $area->accounts_target }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="formProxyTarget">Proxies</label>
                                <input type="number" min="0" required class="form-control" id="formProxyTarget" name="proxy_target" value="{{ $area->proxy_target }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="formDbThreads">Threads</label>
                                <input type="number" min="0" class="form-control" id="formDbThreads" name="db_threads" value="{{ $area->db_threads }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Scan Options</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formSpeedScan">
                                    <input type="checkbox" name="speed_scan" id="formSpeedScan" value="1" @if ($area->speed_scan) checked @endif>
                                    Speed Scan
                                </label>
                                <div class="input-group">
                                    <label for="formWorkers" class="sr-only">Workers</label>
                                    <input type="number" min="0" class="form-control" id="formWorkers" name="workers" value="{{ $area->workers }}">
                                    <div class="input-group-addon">
                                        workers
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formBeehive"><input type="checkbox" id="formBeehive" name="beehive" value="1" @if ($area->beehive) checked @endif> Beehive Mode</label>
                                <div class="input-group">
                                    <label for="formWorkersPerHive" class="sr-only">Workers per Hive</label>
                                    <input type="number" min="0" class="form-control" id="formWorkersPerHive" name="workers_per_hive" value="{{ $area->workers_per_hive }}">
                                    <div class="input-group-addon">
                                        per hive
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formScanDuration">Acccount Scan Duration</label>
                                <div class="input-group">
                                    <input type="number" min="0" class="form-control" id="formScanDuration" name="scan_duration" value="{{ $area->scan_duration }}">
                                    <div class="input-group-addon">minutes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formRestInterval">Acccount Rest Interval</label>
                                <div class="input-group">
                                    <input type="number" min="0" class="form-control" id="formRestInterval" name="rest_interval" value="{{ $area->rest_interval }}">
                                    <div class="input-group-addon">minutes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formMaxEmpty">Max Empty</label>
                                <input type="number" min="0" class="form-control" id="formMaxEmpty" name="max_empty" value="{{ $area->max_empty }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formMaxFailures">Max Failures</label>
                                <input type="number" min="0" class="form-control" id="formMaxFailures" name="max_failures" value="{{ $area->max_failures }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-lg pull-right" data-toggle="modal" data-target="#deleteModal">Delete scan area</button>
    <button type="submit" class="btn btn-primary btn-lg">Save scan area</button>
    <a href="{{ route('mapareas.show', ['map' => $map, 'area' => $area]) }}" class="text-danger btn-lg">cancel</a>
</form>

<div id="deleteModal" class="modal modal-danger" tabindex="-1" role="dialog" aria-labelled-by="deleteModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-close"></i></span>
                </button>
                <h4 class="modal-title" id="deleteModalLabel">Are you sure?</h4>
            </div>
            <div class="modal-body">
                <p>This will permanently destroy {{ $map->name }}'s <b>{{ $area->name }}</b> area! Do you really want to continue?</p>
            </div>
            <div class="modal-footer">
                <form role="form" method="POST" action="{{ route('mapareas.destroy', ['map' => $map, 'area' => $area]) }}">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button type="submit" class="btn btn-outline">Yeah, I get it, delete this map</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
