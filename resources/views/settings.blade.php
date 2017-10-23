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

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Scan Options</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="formAltitudeCache">
                            <input type="checkbox" name="altitude_cache" id="formAltitudeCache" value="1" @if ($settings->altitude_cache) checked @endif>
                            Use altitude cache
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="formDisableVersionCheck">
                            <input type="checkbox" id="formDisableVersionCheck" name="disable_version_check" value="1" @if ($settings->disable_version_check) checked @endif>
                            Disable version check
                        </label>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="formGmapsKey">Google Maps Key</label>
                                <input type="text" class="form-control" id="formGmapsKey" name="gmaps_key" value="{{ $settings->gmaps_key }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="formHashKey">Bossland Hashing Key</label>
                                <input type="text" class="form-control" id="formHashKey" name="hash_key" value="{{ $settings->hash_key }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formLoginDelay">Login Delay</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="formLoginDelay" name="login_delay" value="{{ $settings->login_delay }}">
                                    <div class="input-group-addon">seconds</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formLoginRetries">Login Retries</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="formLoginRetries" name="login_retries" value="{{ $settings->login_retries }}">
                                    <div class="input-group-addon">seconds</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Account Creation</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="formEmailDomains">Email Domains</label>
                        <textarea name="email_domains" id="formEmailDomains" rows="5"
                            class="form-control">{{ implode("\n", $settings->email_domains) }}</textarea>
                        <span class="help-block">Domain names to use when registering new game accounts. One per line.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Captchas</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formManualCaptchas">
                                    <input type="checkbox" name="manual_captchas" id="formManualCaptchas" value="1" @if ($settings->manual_captchas) checked @endif>
                                    Manual solving
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formAutomaticCaptchas">
                                    <input type="checkbox" id="formAutomaticCaptchas" name="automatic_captchas" value="1" @if ($settings->automatic_captchas) checked @endif>
                                    Automatic solving
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="formCaptchaKey">2Captcha Key</label>
                        <input type="text" class="form-control" required id="formCaptchaKey" name="captcha_key" value="{{ $settings->captcha_key }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formCaptchaRefresh">Captcha Refresh</label>
                                <input type="text" class="form-control" id="formCaptchaRefresh" name="captcha_refresh" value="{{ $settings->captcha_refresh }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formCaptchaTimeout">Captcha Timeout</label>
                                <input type="text" class="form-control" id="formCaptchaTimeout" name="captcha_timeout" value="{{ $settings->captcha_timeout }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Save settings</button>
    <a href="/settings" class="text-danger btn-lg">cancel</a>
</form>
@stop
