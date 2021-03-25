(function ($) {
  $(window).on('load', function() {
    /** Comment **/
    $('#add-comment').click(function(e){
        e.preventDefault();
        $("#comment-form").fadeToggle();
    });
  });
})(jQuery);


