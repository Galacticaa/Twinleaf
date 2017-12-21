@extends ('adminlte::page')

@section ('css')
@parent
<link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/purple.css" rel="stylesheet">
<link href="//cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/twinleaf.css') }}" rel="stylesheet">
@stop

@section ('js')
@parent
<script>CSRF_TOKEN='{{ csrf_token() }}';</script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ asset('js/twinleaf.js') }}"></script>
@stop
