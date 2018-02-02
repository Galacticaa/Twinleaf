@extends ('layouts.twinleaf')

@section ('title', 'Discord Cleanup')

@section ('content')
<h2 class="page-header">Discord Server Cleaner</h2>
@foreach ($errors->all() as $error)
<p class="alert alert-danger">{!! $error !!}</p>
@endforeach
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#tab-roles" data-toggle="tab" aria-expanded="true">Roles</a>
        </li>
        <li><a href="#tab-channels" data-toggle="tab">Channels</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab-roles">
            <form method="POST" action="{{ route('discord.purge-roles') }}">
                {{ csrf_field() }}
                <div class="well" style="padding-bottom: 0;">
                    <div class="row">
                        @foreach ($roles as $role)
                        <div class="col-xs-6 col-sm-4 col-md-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                                    &nbsp;{{ $role->name }}
                                </li>
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 mb-10">
                        <button type="button" class="btn bg-purple btn-purge" data-purge="roles">
                            Purge Selected Roles
                        </button>
                        <button type="button" class="btn btn-link">
                            Reset selection
                        </button>
                    </div>
                </div>
                <div class="modal modal-danger fade" id="modal-roles">
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
        <div class="tab-pane" id="tab-channels">
            <form method="POST" action="{{ route('discord.purge-channels') }}">
                {{ csrf_field() }}
                <p class="alert alert-info">
                    <strong>Note:</strong> The bot will need <em>either</em> Administrator status
                    <em>or</em> the <code>Manage Channel</code> and <code>Read Messages</code>
                    permissions in order to delete a channel.
                </p>
                <div class="well">
                    <div class="row">
                        @foreach ($categories as $id => $category)
                        <div class="col-sm-6 col-md-4">
                            <h3>
                                <input type="checkbox" name="channels[]" data-type="4" value="{{ $id }}">
                                &nbsp;{{ $category->name }}
                            </h3>
                            <ul class="list-group">
                                @foreach ($category->channels as $id => $channel)
                                <li class="list-group-item">
                                    <input type="checkbox" name="channels[]"
                                        data-type="{{ $channel->type }}" value="{{ $id }}">&nbsp;
                                    @if ($channel->type === 2)
                                    <i class="fa fa-volume-up"></i>
                                    @elseif ($channel->type === 0)
                                    <i class="fa fa-hashtag"></i>
                                    @endif
                                    &nbsp;{{ $channel->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-xs-12 mb-10">
                            <button type="button" class="btn bg-purple btn-purge" data-purge="channels">
                                Purge Selected Channels
                            </button>
                            <button type="button" class="btn btn-link">
                                Reset selection
                            </button>
                        </div>
                    </div>
                    <div class="modal modal-danger fade" id="modal-channels">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" aria-label="Close"
                                        data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">
                                        The following channels will be <strong>deleted</strong> forever!
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <ul></ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline pull-left"
                                        data-dismiss="modal">Abort! Abort!</button>
                                    <button type="submit" class="btn btn-outline">
                                        Delete Channels
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
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

            $('.btn-purge').bind('click', function() {
                var checkedBoxes = '.list-group-item :checkbox:checked',
                    target = $(this).data('purge'),
                    modal = '#modal-' + target,
                    tab = '#tab-' + target,
                    list = $('ul', modal);

                list.html('');

                if (tab == '#tab-channels') {
                    checkedBoxes += ', h3 :checkbox:checked';
                }

                $(checkedBoxes, tab).each(function() {
                    var role = $(this).parent().parent().text().replace(/\u00a0/g, '');

                    if (tab == '#tab-channels' && $(this).data('type') != 4) {
                        role = $('h3', $(this).parent().parent().parent().parent()).text() + ' /' + role;
                    }

                    list.append('<li>' + role + '</li>');
                });

                $(modal).modal('show');
            });
        });
</script>
@stop
