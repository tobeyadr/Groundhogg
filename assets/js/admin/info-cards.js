(function ($){

  $(function(){
    $(".header-info").on("click",function(){
      $(this).next(".content-info").slideToggle(500);
      $(this).children("i").toggleClass("dashicons-arrow-up-alt2");
    });
  });

})(jQuery)