(function ($) {
    Drupal.behaviors.search_autocomplete = { // https://drupal.org/node/756722#behaviors
        attach: function(context, settings) {

            $('#sa-results').mouseleave(function() {
                $(this).css('display', 'none');
            });

            $('#sa-title').once().click(function() {
                if ($('#sa-results ul').html() != undefined) {
                    $("#sa-results").css('display', 'block');
                }
            });

            $('#sa-title').keyup(function() {
                const url   = $(this).attr('data');
                const title = $(this).val();
             
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: { data : title },
                    success: function (data) {
                        $("#sa-results").empty();
                        const ul =  $("<ul>");

                        if (data.length === 0) {
                            $("#sa-results").css('display', 'none');
                        } else {
                            $("#sa-results").css('display', 'block');
                            $(data).each(function(index, item) {
                                const a = $("<a>")
                                    .attr('href', item.url)
                                    .text(item.title);
                                ul.append($("<li>").append(a));
                            });
                        }
                    
                       $("#sa-results").append(ul);
                    }
                });
              
            });
        }}
})(jQuery);
