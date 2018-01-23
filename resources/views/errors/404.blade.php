@extends ('layouts.twinleaf')

@section ('title', 'Page not Found')

@section ('content_header')
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">Page not found</li>
</ol>
@stop

@section ('content')
<div class="error-page">
    <h2 class="headline text-yellow">404</h2>
    <div class="error-content">
        <h3>
            <i class="fa fa-warning text-yellow"></i>
            Oops! This page doesn't exist.
        </h2>
    </div>
</div>
@stop
