/* MinhTB VERSION 2 */
jQuery(function($) {

    $(window).scroll(function() {
        var $video = $('.assess-form-videos > .video-wrap');
        var $video_top = $video.parent().offset().top;
        var $video_height = $video.height();
        var $form = $('.path-mod-videoassessment .gradingform');
        var $scroll_form = $form.offset().top + $form.height();

        if ($(this).scrollTop() >= ($video_top - 62) && $(this).scrollTop() < ($scroll_form - $video_height - 62)) {
            var $padding = $(this).scrollTop() - $video_top + 62;
            $video.css({'padding-top' : $padding});
        } else if ($(this).scrollTop() < ($video_top - 62)) {
            $video.css({'padding-top' : 0});
        }
    });

    $(window).load(function() {
        var rubrics_passed = $('input[name="rubrics_passed"]').val();

        if (typeof(rubrics_passed) != 'undefined') {
            rubrics_passed = $.parseJSON(rubrics_passed);

            for (var key in rubrics_passed) {
                var rid = rubrics_passed[key];
                var id = "advancedgradingbefore-criteria-" + rid;
                var rubric = $("table#advancedgradingbefore-criteria").find('#' + id);
                var rubric_result = $('#training-result-table-render').find('#' + id);
                rubric_result.addClass(rubric.attr('class'));

                rubric.after(rubric_result);
                rubric.hide();
            }
        }
    });

});
