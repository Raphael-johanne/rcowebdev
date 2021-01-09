(function ($) {
    Drupal.behaviors.bug_report = { // https://drupal.org/node/756722#behaviors
        attach: function(context, settings) {
    
            $('#bug-report-button').once().click(function() {
                if ($('#block-bugreportblock').css('right') == "0px") {
                    $('#block-bugreportblock').animate({
                        right:"-230px"
                      }, 500 );
                } else {
                    $('#block-bugreportblock').animate({
                        right:0
                        }, 500 );
                }
            })

            $('#bug-report-send').once().click(function(){

                const url       = $('#bug-report-send').attr('data');
                const email     = $('#bug-report-email').val();
                const message   = $('#bug-report-message').val();
                
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: { data : message, email : email },
                    success: function (data) {
                        if (data !== true) {
                            $.colorbox({html:$('#bug-report-error').html()});
                        } else {
                            $.colorbox({html:$('#bug-report-success').html()});
                        }

                        $('#block-bugreportblock').animate({
                            right:"-230px"
                            }, 1500 );
                        $('#bug-report-message').val("");
                        $('#bug-report-email').val("");
                    }
                });
            });
        }}
})(jQuery);
