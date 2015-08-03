/* MinhTB VERSION 2 */
jQuery(function($) {

    $(window).scroll(function() {
        var $video = $('.assess-form-videos .video');
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

});
