/* MinhTB VERSION 2 */
jQuery(function($) {

    $(window).scroll(function() {
        var $video = $('.assess-form-videos .video');
        var $video_top = $video.parent().offset().top;

        if ($(this).scrollTop() >= ($video_top - 62)) {
            var $padding = $(this).scrollTop() - $video_top + 62;
            $video.css({'padding-top' : $padding});
        } else {
            $video.css({'padding-top' : 0});
        }
    });

});
