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
                            let type = null;
                            $(data).each(function(index, types) {
                                for (type in types) {
                                    const span = $("<span>")
                                    .text(type)
                                    .addClass('search-autocomplete-separator')
                                    ul.append($("<li>").append(span));
                                    for (item in types[type]) {
                                        const a = $("<a>")
                                        .attr('href', types[type][item].url)
                                        .text(types[type][item].title);
                                        ul.append($("<li>").append(a));
                                    }
                                }
                            });
                        }
                       $("#sa-results").append(ul);
                    }
                });
              
            });
        }}
})(jQuery);
