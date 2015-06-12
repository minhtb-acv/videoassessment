/* MinhTB VERSION 2 */

jQuery(function($) {

    /* Load sort manually list */
    $(document).on('change', '#sortby, #id_order', function() {
        var t = $(this);
        var $sort = $('#sortby').val();
        var $order = $('#id_order').val();
        var $load = $('#sortby').data('load');

        $('.id_order_students').remove();

        if ($sort == 3 && $load == 1) {
            $('#manually-list').removeClass('hidden');
            $('#id_order').addClass('hidden');
        } else {
            var $url = t.closest('form').attr('action');
            var $id = t.closest('form').find('input[type="hidden"][name="id"]').val();
            $('#id_order').after('<div class="loading-icon"><i class="fa fa-refresh fa-spin fa-3x fa-fw margin-bottom"></i></div>');

            $.ajax({
                url: $url,
                method: 'post',
                data: {
                    sort: $sort,
                    order: $order,
                    id: $id
                },
                success: function ($html) {
                    if ($sort == 3) {
                        $('#sortby').data('load', 1);
                    }
                    $('#id_order').after($html);
                    $('#id_order').parent().find('.loading-icon').remove();
                }
            });

            if ($sort == 3) {
                $('#id_order').addClass('hidden');
            } else {
                $('#id_order').removeClass('hidden');
                $('#manually-list').addClass('hidden');
            }
        }
    });

    $(window).load(function() {
        var $sort = $('#sortby').val();
        var $order = $('#id_order').val();

        var $id = $('.sort-form input[type="hidden"][name="id"]').val();
        var $url = $('.sort-form').attr('action');
        $('#id_order').after('<div class="loading-icon"><i class="fa fa-refresh fa-spin fa-3x fa-fw margin-bottom"></i></div>');

        $.ajax({
            url: $url,
            method: 'post',
            data: {
                sort: $sort,
                order: $order,
                id: $id
            },
            success: function ($html) {
                if ($sort == 3) {
                    $('#sortby').data('load', 1);
                }
                $('#id_order').after($html);
                $('#id_order').parent().find('.loading-icon').remove();
            }
        });
    });

    /* Sort up */
    $(document).on('click', '.sort-button .up', function(e) {
        e.preventDefault();

        var t1 = $(this).closest('li');
        var $ueid_1 = t1.data('ueid');
        var $index = t1.closest('ul').find('li').index(t1);
        var t2 = t1.closest('ul').find('li').eq($index - 1);
        var $ueid_2 = t2.data('ueid');
        var $url = t1.closest('form').attr('action');
        var $count = t1.closest('ul').find('li').size();

        $.ajax({
            url: $url,
            method: 'post',
            data: {
                ueid_1: $ueid_1,
                ueid_2: $ueid_2,
                resort: 1
            },
            success: function() {
                if ($index == 1) {
                    var $up_button = t1.find('a.up');
                    $up_button.remove();
                    t2.find('.sort-button').prepend($up_button);
                }

                if ($index == ($count - 1)) {
                    var $down_button = t2.find('a.down');
                    $down_button.remove();
                    t1.find('.sort-button').append($down_button);
                }
                t2.before(t1);
            }
        });
    });

    /* Sort down */
    $(document).on('click', '.sort-button .down', function(e) {
        e.preventDefault();

        var t1 = $(this).closest('li');
        var $ueid_1 = t1.data('ueid');
        var $index = t1.closest('ul').find('li').index(t1);
        var t2 = t1.closest('ul').find('li').eq($index + 1);
        var $ueid_2 = t2.data('ueid');
        var $url = t1.closest('form').attr('action');
        var $count = t1.closest('ul').find('li').size();

        $.ajax({
            url: $url,
            method: 'post',
            data: {
                ueid_1: $ueid_1,
                ueid_2: $ueid_2,
                resort: 1
            },
            success: function() {
                if ($index == 0) {
                    var $up_button = t2.find('a.up');
                    $up_button.remove();
                    t1.find('.sort-button').prepend($up_button);
                }

                if ($index == ($count - 2)) {
                    var $down_button = t1.find('a.down');
                    $down_button.remove();
                    t2.find('.sort-button').append($down_button);
                }
                t2.after(t1);
            }
        });
    });

});
