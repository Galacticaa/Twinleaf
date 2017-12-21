$.fn.progressPopup = function(options) {
    var config = $.extend({}, $.fn.progressPopup.defaults, options);
    var modal;

    function build_modal() {
        modal = $('<div data-backdrop="static" data-keyboard="false"/>');

        modal.append($('<div/>').addClass('modal-dialog')
            .append($('<div/>').addClass('modal-content')
                .append($('<div/>').addClass('modal-header')
                    .append($('<h4/>').addClass('modal-title').text(config.title))
                ).append($('<div/>').addClass('modal-body')
                    .append($('<div/>').addClass('progress progress-sm')
                        .append($('<div/>').addClass('progress-bar progress-bar-striped bg-purple active'))
                    ).append($('<p/>').addClass('status-percent lead text-center')
                        .html(config.text)
                    )
                ).append($('<div/>').addClass('modal-footer'))
            )
        ).addClass('modal fade')

        return modal;
    }

    function set_status(txt, val, forceButton = false) {
        $('.status-percent', this.modal).html(txt + '&hellip;');

        if (val === -1) {
            $('.progress-bar').removeClass('bg-purple').addClass('progress-bar-danger');
        } else {
            $('.progress-bar').width(val+'%')

            if (val >= 100) {
                $('.progress-bar').removeClass('active');
            }
        }

        if (val >= 100 || forceButton) {
            $('.modal-footer', modal).append(
                $('<button/>').text('Close')
                    .addClass('btn btn-default pull-right')
                    .attr('data-dismiss', 'modal')
            );
        }
    }

    function perform_step(steps) {
        var step = steps.shift();
        set_status(step.text, step.status);

        $.post(step.url, function (data) {
            if (data.success) {
                perform_step(steps);
            } else if (data.error) {
                set_status(data.error, -1, true);
            }
        });
    }

    return this.each(function() {
        $(config.trigger).on('click', function() {
            modal = build_modal().appendTo('body');
            modal.modal();

            perform_step(config.steps.slice());
        });
    });
};

$.fn.progressPopup.defaults = {
    title: 'One Moment, Please!',
    text: 'Getting started&hellip;',
    steps: []
};
