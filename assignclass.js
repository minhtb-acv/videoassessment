/* MinhTB VERSION 2 */

jQuery(function($) {

    /* Load sort manually list */
    $(document).on('change', '#sortby', function() {
        var t = $(this);
        var $load = t.data('load');
        var $sort = t.val();

        if ($sort == 3) {
            $('#id_order').addClass('hidden');
            $('#id_manually').removeClass('hidden');
            if ($load == 0) {
                var $url = t.closest('form').attr('action');
                var $id = t.closest('form').find('input[type="hidden"][name="id"]').val();
                $('#id_order').after('<i class="fa fa-refresh fa-spin fa-3x fa-fw margin-bottom"></i>');

                $.ajax({
                    url: $url,
                    method: 'post',
                    data: {
                        sort: $sort,
                        id: $id
                    },
                    success: function ($html) {
                        t.data('load', 1);
                        $('#id_order').after($html);
                        $('#id_order').parent().find('i.fa-refresh').remove();
                    }
                });
            }
        } else {
            $('#id_order').removeClass('hidden');
            $('#id_manually').addClass('hidden');
        }
    });

    $(window).load(function() {
        var $sort = $('#sortby').data('sort');

        if ($sort == 3) {
            var $id = $('.sort-form input[type="hidden"][name="id"]').val();
            var $url = $('.sort-form').attr('action');
            $('#id_order').after('<i class="fa fa-refresh fa-spin fa-3x fa-fw margin-bottom"></i>');

            $.ajax({
                url: $url,
                method: 'post',
                data: {
                    sort: $sort,
                    id: $id
                },
                success: function ($html) {
                    $('#sortby').data('load', 1);
                    $('#id_order').after($html);
                    $('#id_order').parent().find('i.fa-refresh').remove();
                }
            });
        }
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
