@extends ('adminlte::page')

@section ('title', 'Task Manager')

@section ('js')
<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

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
        <table id="accounts-table" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Process</th>
                    <th>Status</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Account Creator</td>
                    @if (!$creator->isInstalled())
                    <td class="text-danger"><i class="fa fa-circle"></i> Not installed!</td>
                    @elseif (!$creator->isRunning())
                    <td class="text-muted"><i class="fa fa-circle"></i> Inactive</td>
                    @else
                    <td class="text-success"><i class="fa fa-circle"></i> Working</td>
                    @endif
                    <td>
                        <button class="btn btn-xs bg-purple reconfigure-creator">
                            <i class="fa fa-cog"></i>
                            @if ($creator->isInstalled()) Reinstall @else Install @endif configuration
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@stop
