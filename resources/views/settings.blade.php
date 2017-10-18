@extends ('adminlte::page')

@section ('title', 'Settings')

@section ('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/purple.css" rel="stylesheet">
<style type="text/css">
    .nav-tabs-custom {
        background: transparent;
    }
</style>
@stop

@section ('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script>
    $(function() {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-purple'
        })
    })
</script>
@stop

@section ('content_header')
<h1>Settings</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">Settings</li>
</ol>
@stop

@section ('content')
<form role="form" method="POST" action="{{ route('settings.update', ['id' => 1]) }}">
    {{ csrf_field() }}
    {{ method_field('PUT') }}
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#tab_captchas" data-toggle="tab">Captchas</a>
            </li>
            <li><a href="#tab_keys" data-toggle="tab">Keys</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_captchas">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settings_captchas_captcha-solving">Captcha Completion</label>
                            <select class="form-control" id="settings_captchas_captcha-solving" name="captcha_solving">
                                <option value=""@if (is_null($settings->captcha_solving)) selected @endif>None</option>
                                <option value="0"@if ($settings->captcha_solving == '0') selected @endif>Manual only</option>
                                <option value="1"@if ($settings->captcha_solving == '1') selected @endif>Manual + autocomplete</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settings_captchas_key">2Captcha Key</label>
                            <input type="text" class="form-control" id="settings_captchas_key" name="captcha_key" value="{{ $settings->captcha_key }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_keys">
                <div class="form-group">
                    <label for="settings_keys_gmaps">Google Maps Key</label>
                    <input type="text" class="form-control" id="settings_keys_gmaps" name="gmaps_key" value="{{ $settings->gmaps_key }}">
                </div>
                <div class="form-group">
                    <label for="settings_keys_hash">Bossland Hashing Key</label>
                    <input type="text" class="form-control" id="settings_keys_hash" name="hash_key" value="{{ $settings->hash_key }}">
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Save settings</button>
    <a href="/settings" class="text-danger btn-lg">cancel</a>
</form>
@stop
