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

    function set_status(txt, val) {
        if (val < 0 || val >= 100) {
            $('.progress-bar').removeClass('active').removeClass('bg-purple');
            $('.progress-bar').addClass('progress-bar-' + (val < 0 ? 'danger' : 'success'));
            $('.modal-footer', modal).append(
                $('<button/>').text('Close')
                    .addClass('btn btn-default pull-right')
                    .attr('data-dismiss', 'modal')
            );
        } else {
            txt += '&hellip;';
        }
        $('.status-percent', this.modal).html(txt);

        if (val > 0) {
            $('.progress-bar').width(val+'%');
        }
    }

    function perform_step(steps) {
        var step = steps.shift();
        set_status(step.text, step.status);

        if (step.status == 100) {
            return;
        }

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
