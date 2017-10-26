@extends ('adminlte::page')

@section ('title', 'Proxies')

@section ('css')
<link href="//cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">
@stop

@section ('js')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
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
        <a class="btn bg-purple" href="{{ route('proxies.check') }}" style="margin-bottom: 10px;">
            <i class="fa fa-search"></i> Run ban check
        </a>
        <div id="importModal" class="modal fade" tabindex="-1" role="dialog" aria-labelled-by="importModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="importModalLabel">Import Proxies</h4>
                    </div>
                    <form class="form" action="{{ route('proxies.import') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="formProxies">Proxy list, one per line</label>
                                <textarea class="form-control" rows="10" name="proxies" id="formProxies"></textarea>
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
                    <th>Map Area</th>
                    <th>PTC Status</th>
                    <th>PoGo Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proxies as $proxy)
                <tr>
                    <td>{{ $proxy->url }}</td>
                    @if ($proxy->area)
                    <td><a href="{{ route('mapareas.show', ['map' => $proxy->area->map, 'area' => $proxy->area]) }}">{{ $proxy->area->name }}</a></td>
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
