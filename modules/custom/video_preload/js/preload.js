(function ($) {
    Drupal.behaviors.video_preload = { 
        attach: function(context, settings) {
            $(window).once().on('load', function () {
                const videoEl   = $('#film-video-tag').get(0);
                const videoKey  = $.md5($(videoEl).children().first('source').attr('src'));
  
                if (parseInt($.cookie(videoKey)) > 0) {
                    $("#video-preload-container").css('display', 'block');
                    $("#video-preload-container").appendTo("#video-custom-toolbar");
                    const currentTime = $.cookie(videoKey);
                    $('#video-continue').once().click(function(event) {
                        event.preventDefault();
                        videoEl.currentTime = currentTime;
                        videoEl.play();
                        $("#video-preload-container").css('display', 'none');
                    });
                    $('#video-reset').once().click(function(event) {
                        event.preventDefault();
                        $.cookie(videoKey, 0);
                        videoEl.pause();
                        videoEl.currentTime = 0;
                        videoEl.play();
                        $("#video-preload-container").css('display', 'none');
                    });
                } else {
                    $.cookie(videoKey, 0);
                }

                setInterval(function(){
                    $.cookie(videoKey, videoEl.currentTime);
                }, 
                1000);
              });
        }}
})(jQuery);
