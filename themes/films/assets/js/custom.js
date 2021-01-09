const pager     = 15; // Number of films to show per page
const container = new Array();

(function ($) {
  $(window).on('load', function() {
    
    const films     = $('#block-views-block-duplicate-of-relative-content-for-films-block-1').find('.views-row');
    const nbrFilms  = films.length;
    
    films.each(function(index, item) {
      //console.log(item);
      //container[index][] = item;
    });
  });
})(jQuery);
