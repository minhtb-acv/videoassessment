/* MinhTB VERSION 2 03-03-2016 */
jQuery(function($) {

    $('#publish-category').change(function() {
        var catid = $(this).val();
        var url = $(this).closest('form').attr('action');

        $.ajax({
            method: 'post',
            url: url,
            data: {
                ajax: 1,
                catid: catid,
                action: 'getcoursesbycategory',
            },
            success: function(data) {
                data = $.parseJSON(data);

                if (data.html) {
                    $('#publish-course').html(data.html);
                }
            }
        });
    }).change();

    $(document).on('change', '#publish-course', function() {
        var courseid = $(this).val();
        var url = $(this).closest('form').attr('action');

        if (courseid != 0) {
            $('#publish-fullname').attr('disabled', 'disabled');
            $('#publish-shortname').attr('disabled', 'disabled');

            $.ajax({
                method: 'post',
                url: url,
                data: {
                    ajax: 1,
                    courseid: courseid,
                    action: 'getsectionsbycourse',
                },
                success: function (data) {
                    data = $.parseJSON(data);

                    if (data.html) {
                        $('#publish-section').html(data.html);
                        $('#publish-section').removeAttr('disabled');
                    } else {
                        $('#publish-section').attr('disabled', 'disabled');
                    }
                }
            });
        } else {
            $('#publish-section').attr('disabled', 'disabled');
            $('#publish-fullname').removeAttr('disabled');
            $('#publish-shortname').removeAttr('disabled');
        }
    });

});
