(function($){
    $(document).ready(function() {
        $('.owl-carousel-wpv').owlCarousel({
          loop:true,
          margin:10,
          nav:true,
          responsive:{
              0:{
                  items:1
              },
              600:{
                  items:3
              },
              1000:{
                  items:5
              }
          }
      })
    });
      
  })(jQuery) ;
  
jQuery(document).ready(function() {
    jQuery('.wpv-popup-link').magnificPopup({
        type: 'image',
        gallery:{
        enabled:true
        }
    });  
}); 