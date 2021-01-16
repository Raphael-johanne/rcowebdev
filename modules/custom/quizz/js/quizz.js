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
            let selected = null;

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
                window.location = $(selected).attr('href');
            });
        }}
})(jQuery);
