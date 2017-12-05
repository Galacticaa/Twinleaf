@extends ('adminlte::page')

@section ('title', 'New Map')

@section ('content_header')
<h1>New Map</h1>
<ol class="breadcrumb">
    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Map Manager</a></li>
    <li class="active">Add new Map</li>
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

<form role="form" method="POST" action="{{ route('maps.store') }}">
    {{ csrf_field() }}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Basic Details</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formName">Name</label>
                        <input type="text" class="form-control" id="formName" placeholder="My Hometown" name="name">
                        <p class="help-block">
                            Pick a name, any name&hellip; Used in the map's menu bar, choose wisely!
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formCode">Code</label>
                        <input type="text" class="form-control" id="formCode" placeholder="hometown" name="code">
                        <p class="help-block">
                            This should be a unique "codename" that can identify the map.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formLocation">Web Address</label>
                        <input type="text" class="form-control" id="formUrl" placeholder="https://map.example.com/" name="url">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formAnalyticsKey">Google Analytics Key</label>
                        <input type="text" class="form-control" id="formAnalyticsKey" name="analytics_key">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="formLocation">Location</label>
                <input type="text" class="form-control" id="formLocation" placeholder="35.31233, 138.5892" name="location">
            </div>
        </div>
    </div>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Database Settings</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                <label for="formDbName">Database Name</label>
                <input type="text" class="form-control" id="formDbName" placeholder="rocketmap" name="db_name">
                <p class="help-block">
                    Enter the name of the database you've created for this map. Ensure the database is empty before continuing!
                </p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formDbUser">MySQL Username</label>
                        <input type="text" class="form-control" id="formDbUser" placeholder="root" name="db_user">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="formDbPass">MySQL Password</label>
                        <input type="password" class="form-control" id="formDbPass" placeholder="root" name="db_pass">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Save map</button>
    <a href="{{ route('dashboard') }}" class="text-danger btn-lg">cancel</a>
</form>
@stop
