$(function(){
  // assign the slider to a variable
  var slider = $('#dash-lead-slider').bxSlider({
    controls: false
  });

  // assign a click event to the external thumbnails
  $('.dash-lead-thumbs a').click(function(){
   var thumbIndex = $('.dash-lead-thumbs a').index(this);
    // call the "goToSlide" public function
    slider.goToSlide(thumbIndex);
 
    // remove all active classes
    $('.dash-lead-thumbs a').removeClass('pager-active');
    // assisgn "pager-active" to clicked thumb
    $(this).addClass('pager-active');
    // very important! you must kill the links default behavior
    return false;
  });

  // assign "pager-active" class to the first thumb
  $('.dash-lead-thumbs a:first').addClass('pager-active');
});