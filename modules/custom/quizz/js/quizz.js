(function ($) {
    Drupal.behaviors.quizz = { // https://drupal.org/node/756722#behaviors
        attach: function(context, settings) {
            /**
             * step pseudo
             */
            $('#quizz-valid-pseudo').once().click(function(event) {
                event.preventDefault();
                if ($('#pseudo').val() == "") {
                    $.colorbox({html:"A pseudo is mandatory"});
                    return false;
                }
                $('#quizz-pseudo').submit();
            });

            /**
             * steps quizz
             */
            let selected    = null;
            let validated   = true;

            $('.quizz-answers').each(function(index, item) {
                $(this).click(function(event){
                    event.preventDefault();

                    if (selected != null) {
                        $(selected).removeClass('selected');
                        selected = null;
                    }

                    selected = item;
                    $(selected).addClass('selected');
                })
            });

            $('#quizz-valid-step').once().click(function(event) {
                event.preventDefault();
                if (selected == null) {
                    $.colorbox({html:"An answer is mandatory !"});
                    return false;
                }
                if (validated === true) {
                    $('i.fa.fa-spinner.fa-spin').css('display', 'inline-block');
                    window.location = $(selected).attr('href');
                    validated = false;
                }
            });

            /**
             * generate timer behaviour
             */
            if ($('#quizz-timer').length > 0) {
                let timer               = parseInt($('#quizz-timer').val());
                const emptyAnswerRoute  = $('#quizz-timer-link').val();
                setInterval(function() {
                    timer--;
                    $('#quizz-timer-progress').val(timer);
                    if (timer === 0) {
                        window.location = emptyAnswerRoute;
                        validated = false;
                    }
                }, 1000);
            }
        }}
})(jQuery);
