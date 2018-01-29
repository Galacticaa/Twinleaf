@extends ('layouts.twinleaf')

@section ('title', 'Discord Cleanup')

@section ('content')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Discord Role Cleanup</h3>
    </div>
    <div class="box-body">
        <form method="POST" action="{{ route('discord.purge') }}">
            {{ csrf_field() }}
            <div class="well" style="padding-bottom: 0;">
                <div class="row">
                    @foreach ($roles as $role)
                    <div class="col-xs-6 col-sm-4 col-md-3">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <input type="checkbox" name="roles[]" value="{{ $role['id'] }}">
                                &nbsp;{{ $role['name'] }}
                            </li>
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 mb-10">
                    <button type="button" class="btn bg-purple" id="purge">
                        Purge Selected Roles
                    </button>
                    <button type="button" class="btn btn-link">
                        Reset selection
                    </button>
                </div>
            </div>
            <div class="modal modal-danger fade" id="confirmModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" aria-label="Close"
                                data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">
                                The following roles will be <strong>deleted</strong> forever!
                            </h4>
                        </div>
                        <div class="modal-body">
                            <ul></ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline pull-left"
                                data-dismiss="modal">Abort! Abort!</button>
                            <button type="submit" class="btn btn-outline">
                                Delete Roles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section ('js')
@parent
    <script>
        $(function() {
            $('.list-group-item').bind('click', function() {
                $('input', this).iCheck('toggle');
            });

            $('#purge').bind('click', function() {
                var list = $('ul', '#confirmModal');

                list.html('');

                $(':checkbox:checked', '.list-group-item').each(function() {
                    var role = $(this).parent().parent().text();

                    list.append('<li>'+role+'</li');
                });

                $('#confirmModal').modal('show');
            });
        });
</script>
@stop
