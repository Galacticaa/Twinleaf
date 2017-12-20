$(function() {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-purple'
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
});
