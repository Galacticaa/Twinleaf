@extends ('layouts.twinleaf')

@section ('content_header')
<h1>Discord Configuration</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li>Discord</li>
    <li class="active">Configuration</li>
</ol>
@stop

@section ('content')
<form role="form" method="POST" action="{{
    route('discord.config.update', ['id' => 1])
}}">
    {{ csrf_field() }}
    {{ method_field('PUT') }}
    <div class="box box-primary form-horizontal">
        <div class="box-header with-border">
            <h3 class="box-title">API Credentials</h3>
        </div>
        <div class="box-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="form-group">
                <label for="discordBotToken" class="col-sm-2 control-label">
                    Bot Token
                </label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="discordBotToken"
                        name="bot_token" value="{{ $config->bot_token }}">
                </div>
            </div>
            <div class="form-group">
                <label for="discordGuildId" class="col-sm-2 control-label">
                    Guild (Server) ID
                </label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="discordGuildId"
                        name="guild_id" value="{{ $config->guild_id }}">
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header">
            <h3 class="box-title">Team Colours</h3>
        </div>
        <div class="box-body">
            <div class="row">
                @foreach ($config->colours as $team => $colour)
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="discordTeam{{ ucfirst($team) }}">{{ ucfirst($team) }}</label>
                        <div class="input-group" id="cp-{{ $team }}">
                            <input id="discordTeam{{ ucfirst($team) }}" name="colours[{{ $team }}]"
                                value="{{ $colour }}" type="text" class="form-control">
                            <div class="input-group-addon">&nbsp;</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Save Discord configuration</button>
    &nbsp;<a href="/settings" class="text-danger btn-lg">cancel</a>
</form>
@stop

@section ('css')
@parent
<link rel="stylesheet" type="text/css" href="https://farbelous.github.io/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css">
@stop

@section ('js')
@parent
<script src="https://farbelous.github.io/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js"></script>
<script>
    $(function() {
        $('#cp-instinct, #cp-mystic, #cp-valor').colorpicker({
            useAlpha: false,
            format: 'hex'
        });
    });
</script>
@stop
