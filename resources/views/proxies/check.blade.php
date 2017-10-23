@extends ('adminlte::page')

@section ('title', 'Proxy Ban Check')

@section ('js')
<script>
    $(function() {
        var ptcCheckUrl = '{{ route('proxies.check-ptc', ['id' => '--ID--']) }}',
            pogoCheckUrl = '{{ route('proxies.check-pogo', ['id' => '--ID--']) }}';

        function makeStatusBox (proxy) {
            var url = proxy.url.split('@');
            url = url.length === 1 ? url[0] : url[1];

            var $icon = $('<span class="info-box-icon" />').append($('<i class="fa fa-hourglass-half"/>'));
            var $spinner = '<i class="fa fa-spinner fa-spin"></i>';
            var $content = $('<div class="info-box-content" style="padding-top: 0;" />')
                            .append($('<span/>').addClass('info-box-text')
                                .html('<p class="lead" style="margin-bottom: 0;">'+url+'</p>'))
                            .append($('<span style="height: 25px;" />').addClass('info-box-number')
                                .append($('<div style="width: 50%;" class="pogocheck pull-right"/>').text('Pogo: ').append($spinner))
                                .append($('<div style="width: 50%;" class="ptccheck"/>').text('PTC: ').append($spinner)))
                            .append($('<div/>').addClass('progress').append(
                                $('<div/>').addClass('progress-bar').width('20%')
                            )).append($('<span/>').addClass('progress-description').text('Checking PTC...'));

            var $box = $('<div data-proxy-id="'+proxy.id+'">').addClass('info-box bg-aqua').append($icon).append($content);

            return $('#checkStatus').prepend($('<div class="col-md-6 col-lg-4"/>').append($box));
        }

        var checkProxy = function () {
            var ptcStatus, pogoStatus;
            var proxy = proxies.pop();

            if (!proxy) {
                return;
            }
            var $box = makeStatusBox(proxy);

            $.post(ptcCheckUrl.replace('--ID--', proxy.id), function (data) {
                ptcStatus = data.status;
                $box = $('div[data-proxy-id="'+data.proxy.id+'"]');
                $('.ptccheck i', $box).replaceWith(ptcStatus);
                $('.info-box-icon i', $box).removeClass('fa-hourglass-half').addClass('fa-hourglass-end');
                $('.progress-bar', $box).width('70%');

                var $status = $('#overallStatus')
                completeChecks = parseInt($status.attr('data-checks')) + 1;
                overallPercent = ((completeChecks / total) * 100) + '%';

                $('.progress-bar', $status).width(overallPercent);
                $('#remaining', $status).text(total - completeChecks);
                $status.attr('data-checks', completeChecks);

                $('.progress-description', $box).text('Checking PokemonGO...');

                $.post(pogoCheckUrl.replace('--ID--', data.proxy.id), function (data) {
                    pogoStatus = data.status;
                    $box = $('div[data-proxy-id="'+data.proxy.id+'"]');
                    $icon = $('.info-box-icon i', $box);
                    $('.pogocheck i', $box).replaceWith(pogoStatus);
                    $('.progress-bar', $box).width('100%');
                    $icon.removeClass('fa-hourglass-end');

                    if (pogoStatus == 200 && ptcStatus == 200) {
                        $icon.addClass('fa-check');
                        bgClass = 'bg-green';
                    } else {
                        $icon.addClass('fa-times');
                        bgClass = 'bg-red';
                    }

                    $('.progress-description', $box).text('Checks complete!');

                    $('div[data-proxy-id="'+proxy.id+'"]').removeClass('bg-aqua').addClass(bgClass);

                    var $status = $('#overallStatus')
                    completeChecks = parseInt($status.attr('data-checks')) + 1;
                    completeProxies = parseInt($status.attr('data-proxies')) + 1;
                    overallPercent = ((completeChecks / total) * 100) + '%';

                    $('.progress-bar', $status).width(overallPercent);
                    $('#complete', $status).text(completeProxies);
                    $('#remaining', $status).text(total - completeChecks);
                    $status.attr('data-checks', completeChecks);
                    $status.attr('data-proxies', completeProxies);

                    checkProxy();
                })
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        var proxies = JSON.parse('{!! (string) $proxies !!}');
        const total = proxies.length * 2;

        $('#overallStatus p').text('Loaded '+(total/2)+' proxies for scanning!');

        setTimeout(function () {
            $('#overallStatus')
                .append($('<div class="progress"/>')
                .append($('<div class="progress-bar"/>')));

            $('#overallStatus p').html('Completed <span id="complete">0</span>'+
                ' of '+total/2+' proxies &mdash; <span id="remaining">'+total+'</span> checks remaining.');
            for (var i = 0; i < 15; i++) {
                setTimeout(checkProxy, (Math.random() * 2000));
            }
        }, 750);
    });
</script>
@endsection

@section ('content_header')
<h1>Ban Checker</h1>
<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li>Proxies</li>
    <li class="active">Ban Checker</li>
</ol>
@stop

@section ('content')
<div class="box box-primary">
    <div class="box-body">
        <div id="overallStatus" data-checks="0" data-proxies="0">
            <p class="lead text-center"></p>
        </div>
        <div class="row" id="checkStatus"></div>
    </div>
</div>
@stop
