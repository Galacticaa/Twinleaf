@extends ('layouts.twinleaf')

@section ('title', 'Proxies')

@section ('js')
@parent
<script>
    $(function() {
        $('.datatable').DataTable();
    });
</script>
@endsection

@section ('content_header')
<h1>Proxies</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">Proxies</li>
</ol>
@stop

@section ('content')
<div class="box box-primary">
    <div class="box-body">
        <button class="btn btn-default pull-right" data-toggle="modal" data-target="#importModal">
            <i class="fa fa-upload"></i> Import
        </button>
        <a class="btn bg-purple mb-10" href="{{ route('proxies.check') }}">
            <i class="fa fa-search"></i> Run ban check
        </a>
        <div id="importModal" class="modal fade" tabindex="-1" role="dialog" aria-labelled-by="importModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="importModalLabel">Import Proxies</h4>
                    </div>
                    <form class="form form-horizontal" action="{{ route('proxies.import') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                            @endif
                            <div class="form-group">
                                <label class="col-sm-3" for="formProvider">Provider</label>
                                <div class="col-sm-9">
                                    <select name="provider" id="formProvider" class="form-control">
                                        <option value="" selected disabled></option>
                                        @foreach (config('proxy.providers') as $slug => $provider)
                                        <option value="{{ $slug }}">{{ $provider['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3">Import mode</label>
                                <div class="col-sm-9 mb-10">
                                    <label class="radio-inline pt-0">
                                        <input type="radio" name="mode" id="formOpAppend" value="a" checked>
                                        Append
                                    </label>
                                    <label class="radio-inline pt-0">
                                        <input type="radio" name="mode" id="formOpReplace" value="r">
                                        Replace
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3" for="formProxies">
                                    Proxy list
                                    <small class="help-block">one per line</small>
                                </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" rows="10" name="proxies" id="formProxies"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <table id="accounts-table" class="table table-bordered table-hover datatable">
            <thead>
                <tr>
                    <th>Proxy</th>
                    <th>Provider</th>
                    <th>Map Area</th>
                    <th>PTC Status</th>
                    <th>PoGo Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proxies as $proxy)
                <tr>
                    <td>{{ $proxy->url }}</td>
                    <td>
                        @if ($proxy->provider)
                        {{ $providers[$proxy->provider]['name'] }}
                        @else
                        <span class="text-muted">None</span>
                        @endif
                    </td>
                    @if ($proxy->area)
                    <td><a href="{{ route('maps.areas.show', ['map' => $proxy->area->map, 'area' => $proxy->area]) }}">{{ $proxy->area->name }}</a></td>
                    @else
                    <td><span class="text-muted">Unassigned</span></td>
                    @endif
                    @foreach (['ptc_ban', 'pogo_ban'] as $ban)
                    @if ($proxy->$ban === null)
                    <td class="text-muted"><i class="fa fa-question"></i> Unknown
                    @elseif ($proxy->$ban)
                    <td class="text-danger"><i class="fa fa-exclamation-triangle"></i> FAIL
                    @else
                    <td class="text-success"><i class="fa fa-check"></i> Pass
                    @endif
                    @if ($ban == 'ptc_ban' && $proxy->ptc_status)
                    ({{ $proxy->ptc_status }})
                    @elseif ($ban == 'pogo_ban' && $proxy->pogo_status)
                    ({{ $proxy->pogo_status }})
                    @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="4"><p class="lead text-center">There aren't any proxies!</p></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
