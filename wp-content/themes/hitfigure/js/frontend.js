// $(document).ready(function() {
//     var debug = false;
// 
//     /* When page loads... */
//  jQuery(".seller_form_container").hide(); /* Hide all content */
//     jQuery(".subinfo").hide(); /* Hide all subsections that will open with proper radio buttons */
// 
//  jQuery(".seller_form_container:first").show(); /* Show first "page" content */
// 
//  /* Page Progression + Validation click functions */
//  jQuery(".seller_form_container a.button").click(function() {
// 
//         var id = jQuery(this).closest("div").attr("id"); /* gather for validation purposes.. */
//         var next_page = jQuery(this).attr("href");
//         var form = jQuery('#submit_vehicle');
//         if(debug) console.log("#" + id);
// 
//         var errors = false;
// 
//         /* validate select inputs */
//          jQuery("#" + id + " select").each(function() {
//             /* look through our class declaration from our micro-templates and find */
//             /* if the class="validate" is present (even in combination with other classes) */
//             var isValidate = jQuery(this).parent().children('.required').length;
//             console.log(jQuery(this).parent().children('.required'));
//             var selectID = jQuery(this).attr('id');
//             if(isValidate) { /* only check the ones with class="validate" */
//                 var optionValue = jQuery("#" + selectID + ' option:selected').val();
//                 if(optionValue.length < 1) {
//                     jQuery(this).parent().addClass('error');
//                     errors = true;
//                     if(debug) {
//                         var selectName = jQuery$(this).attr('name');
//                         console.log(selectName + ":" + optionValue );
//                     }
//                 } else {
//                     jQuery(this).parent().removeClass('error');
//                  jQuery(this).removeClass('error');
//                 }
// 
//             }
// 
//          });
//         /* $("select option:selected") */
// 
//         /* validate text inputs: */
//         jQuery("#" + id + " input[type=text]").each(function(n, element) {
//             /* look through our class declaration from our micro-templates and find */
//             /* if the class="validate" is present (even in combination with other classes) */
//             var isValidate = jQuery(this).parent().children('.required').length;
// 
//             if(isValidate) { /* only check the ones with class="validate" */
//                 var txtval = jQuery(this).val();
//                 if(txtval.length < 1) {
//                     jQuery(this).parent().addClass('error');
//                     errors = true;
//                     if(debug) {
//                         var inputName = jQuery(this).attr('name');
//                         console.log(inputName + ":" + txtval );
//                         console.log(inputName + ":" + jQuery(this).attr('class') );
//                     }
//                 } else {
//                     jQuery(this).parent().removeClass('error');
//                 }
// 
// 
//             }
//         });
// 
//         jQuery("#" + id + " .RadioGroup").each(function() {
// 
//             /* get the trimmed id... */
//             var radioID = $(this).attr('id');
//             var radioName = radioID.replace(/wrapper\-id_/i, '');
//             if(debug) console.log(radioName);
// 
//             /* do we have a required child div? */
//             if(jQuery(this).children('.required').length) {
// 
//                 if (jQuery('input[name='+ radioName +']:checked').length) {
//                     jQuery(this).parent().removeClass('error');
//                   } else {
//                     if(debug) console.log(radioName + ": none are checked");
//                     jQuery(this).parent().addClass('error');
//                     errors = true;
//                 }
//             }
//         });
// 
//         if(errors) {
//             /* alert('errors'); */
//             /* error classes added individually above... */
//             /* TO-DO: print error description somewhere... */
//             
//         } else {
//             if(jQuery(this).attr('id') == 'submit') {
//                 form.submit();
//             } else {
//             /* we're clear for lift-off */
//            jQuery('.seller_form_container').hide();
//             jQuery(next_page).fadeIn();
//             }
//         }
//         /* make sure we stop the normal anchor/link function: */
//      return false;
//  });
//  
//  /* Remove Error CSS if we have a positive change */
//  
//     jQuery(':input').live('change', function() {
//      if(jQuery(this).val()) 
//          jQuery(this).parent().removeClass('error');
//  });
//  
//     /* MTV.debugging(); */
// 
//     /* Main Select Elements + AJAX calls to get filtered options from the DB */
//     /* Year => Makes */
//     jQuery('#id_vehicle_year').live('change', function() {
// 
//      cyear = jQuery('#id_vehicle_year').val();
//         var a = {
//             text : 'make',
//             xdata : {
//                 action : "vehicle_makes",
//                 data : {
//                  'car_year' : cyear
//              }
//             },
//             result_id : '#id_vehicle_make'
// 
//         };
//         get_car_data(a);
//  });
// 
//     /* Makes => Models */
//     jQuery('#id_vehicle_make').live('change', function() {
//         /* take year + make and return models */
//      cyear = jQuery('#id_vehicle_year').val();
//      cmake = jQuery('#id_vehicle_make').val();
//      
//         var a = {
//             text : 'model',
//             xdata : {
//                 action : "vehicle_models",
//                 data : {
//                  'car_year'  :   cyear,
//                  'car_make' :    cmake
//              }
//             },
//             result_id : '#id_vehicle_model'
// 
//         };
//         get_car_data(a);
//  });
// 
//     /* Models => Trim (if any) */
//     jQuery('#id_vehicle_model').live('change', function() {
//      cyear = jQuery('#id_vehicle_year').val();
//      cmake = jQuery('#id_vehicle_make').val();
//      cmodel = jQuery('#id_vehicle_model').val();
//      
//         var a = {
//             text : 'trim',
//             xdata : {
//                 action : "vehicle_trims",
//                 data : {
//                  'car_year'  :   cyear,
//                  'car_model' :   cmake,
//                  'car_model' :   cmodel
//              }
//             },
//             result_id : '#id_vehicle_trim'
// 
//         };
//         get_car_data(a);
//  });
// 
// 
// 
//     /*
//     * get_car_data(obj)
//     *
//     * Main Workhorse Function for retrieving the info, via AJAX,
//     * to update Select elements with filtered data
//     * 
//      */
//  function get_car_data(a) {
//      /*  Set current to disabled, it'll get overwritten when we add the ajax result- nice touch, ColinPress! */
//      jQuery(a.result_id).attr('disabled','disabled');
//  
//         var resultType = 'car_' + a.text;
//         var topOptionText = ucwords(a.text);
//      
// 
//      MTV.do_ajax(
//          /*  global javascript variable */
//          '/ajax_form_data/',
//          a.xdata,
//          function( response ) {
//                 /* if(debug) alert(response.length); */
//              jQuery(a.result_id).parent().replaceWith(response.html);
//              if(debug) console.log(a.debug);
//          },
//             function(error) {
//                 alert('error')
//             }
//      );
//  }
// 
//     /*
//     * Radio Button/Open Sub Info
//      */
//     jQuery(".radio_group_wrapper").each(function() {
// 
//         var subdiv = $(this).children('.subinfo');
//         
//         /* does this group/wrapper have a '.subinfo' div? if so... */
//         if(subdiv.length) {
//             /* get the trimmed id... */
//             var radioID = $(this).attr('id');
//             var radioName = radioID.replace(/id_/i, '');
//             radioName = radioName.replace(/_wrapper/i, '');
//             if(debug) console.log(radioName);
// 
//             $('input[name=' + radioName + ']').change(function() {
//                   if($(this).val() == 'Yes') {
//                     /* open subdiv */
//                     $(subdiv).show();
//                   } else {
//                       /* close the subdiv */
//                       $(subdiv).hide();
//                   }
//             });
//         }
// 
//     });
// 
//     /* Utility to capitalize words, much like ucwords() in PHP */
//     function ucwords(str) {
//         return (str + '').replace(/^([a-z])|[\s_]+([a-z])/g, function ($1) {
//             return $1.toUpperCase();
//         })
//     };
//  
// 
// /* end frontend.js */
// } );

var frontEnd = (function(){
    var debug = false,
        self = {},
        errors = false;
        
    var validateSelect = function(select) {
        if(select.val().length < 1) {
            select.parent().addClass('error');
            errors = true;
            if(debug) {
                var selectName = select.attr('name');
                console.log(selectName + ":" + select.val());
            }
        } else {
            select.parent().removeClass('error');
            select.removeClass('error');
        }
    }
        
    var validateTextInput = function(textInput) {
        var txtval = textInput.val();
        if(txtval.length < 1) {
            textInput.parent().addClass('error');
            errors = true;
            if(debug) {
                var inputName = textInput.attr('name');
                console.log(inputName + ":" + txtval );
                console.log(inputName + ":" + textInput.attr('class') );
            }
        } else {
            textInput.parent().removeClass('error');
        }
    }
    
    var validateRadio = function(radioGroup) {
        if (radioGroup.find('input:checked').length) {
            jQuery(this).parent().removeClass('error');
          } else {
            if(debug) console.log(radioGroup.find('input').attr('name') + ": none are checked");
            radioGroup.parent().addClass('error');
            errors = true;
        }
    }
    
    /*
    * get_car_data(obj)
    *
    * Main Workhorse Function for retrieving the info, via AJAX,
    * to update Select elements with filtered data
    * 
    */
    var get_car_data = function(a) {
        /*  Set current to disabled, it'll get overwritten when we add the ajax result- nice touch, ColinPress! */
        jQuery(a.result_id).attr('disabled','disabled');

        var resultType = 'car_' + a.text;
        var topOptionText = ucwords(a.text);
 
        MTV.do_ajax(
             /*  global javascript variable */
             '/ajax_form_data/',
             a.xdata,
             function( response ) {
                    /* if(debug) alert(response.length); */
                 jQuery(a.result_id).parent().replaceWith(response.html);
                 if(debug) console.log(a.debug);
             },
                function(error) {
                    alert('error')
                }
         );
    }
    
    /* Utility to capitalize words, much like ucwords() in PHP */
    var ucwords = function(str) {
        return (str + '').replace(/^([a-z])|[\s_]+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        })
    }
    
    self.init = function(step_number) {
        /* When page loads... */
        jQuery(".subinfo").hide(); /* Hide all subsections that will open with proper radio buttons */
        
        self.steps = {
            1: jQuery('#form_page_1').hide(),
            2: jQuery('#form_page_2').hide(),
            3: jQuery('#form_page_3').hide()
        }
        self.active_step = self.steps[step_number].show();
        self.active_step_number = step_number;
        
        jQuery(".seller_form_container .frontFormBtn").click(function(e) {
            e.preventDefault();
            errors = false; //assume there are no errors pre-validation 
            
            // find all required selects and validate
            self.active_step.find('.required').siblings('select').each(function(){
                validateSelect(jQuery(this));
            });
            
            // find all required text inputs and validate
            self.active_step.find('.required').siblings('input[type=text]').each(function(){
                validateTextInput(jQuery(this));
            });
            
            // find all required radio inputs and validate
            self.active_step.find('.required').parent('.RadioGroup').each(function(){
                validateRadio(jQuery(this));
            });
            
            if(!errors) {
                if(self.active_step != self.steps[3]) {
                    self.active_step.hide();
                    self.active_step = self.steps[self.active_step_number + 1].fadeIn();
                    self.active_step_number = self.active_step_number + 1;
                }
                else {
                    jQuery('#submit_vehicle').submit();
                }
            }
        });
        
         /* Remove Error CSS if we have a positive change */
         jQuery('input[type=text], select').live('change', function() {
            if(jQuery(this).val()) 
                jQuery(this).parent().removeClass('error');
         });
         
         jQuery('input[type=radio]').live('change', function(){
             if(jQuery(this).val())
                jQuery(this).parents('.radio_group_wrapper').removeClass('error');
         });
         
         /* Main Select Elements + AJAX calls to get filtered options from the DB */
         /* Year => Makes */
         jQuery('#id_vehicle_year').live('change', function() {
              cyear = jQuery('#id_vehicle_year').val();
                 var a = {
                     text : 'make',
                     xdata : {
                         action : "vehicle_makes",
                         data : {
                          'car_year' : cyear
                      }
                     },
                     result_id : '#id_vehicle_make'
                 }
                 get_car_data(a);
            });
 
         /* Makes => Models */
         jQuery('#id_vehicle_make').live('change', function() {
             /* take year + make and return models */
              cyear = jQuery('#id_vehicle_year').val();
              cmake = jQuery('#id_vehicle_make').val();
      
              var a = {
                  text : 'model',
                  xdata : {
                      action : "vehicle_models",
                      data : {
                          'car_year'  :   cyear,
                          'car_make' :    cmake
                      }
                  },
                  result_id : '#id_vehicle_model'
              }
              get_car_data(a);
          });
 
         /* Models => Trim (if any) */
         jQuery('#id_vehicle_model').live('change', function() {
             cyear = jQuery('#id_vehicle_year').val();
             cmake = jQuery('#id_vehicle_make').val();
             cmodel = jQuery('#id_vehicle_model').val();
      
             var a = {
                 text : 'trim',
                 xdata : {
                     action : "vehicle_trims",
                     data : {
                      'car_year'  :   cyear,
                      'car_model' :   cmake,
                      'car_model' :   cmodel
                  }
                 },
                 result_id : '#id_vehicle_trim'
             }
             get_car_data(a);
         });
         
         
         /*
         * Radio Button/Open Sub Info
          */
         jQuery(".radio_group_wrapper").each(function() {
     
             var subdiv = $(this).children('.subinfo');
             
             /* does this group/wrapper have a '.subinfo' div? if so... */
             if(subdiv.length) {
                 /* get the trimmed id... */
                 var radioID = $(this).attr('id');
                 var radioName = radioID.replace(/id_/i, '');
                 radioName = radioName.replace(/_wrapper/i, '');
                 if(debug) console.log(radioName);
     
                 $('input[name=' + radioName + ']').change(function() {
                       if($(this).val() == 'Yes') {
                         /* open subdiv */
                         $(subdiv).show();
                       } else {
                           /* close the subdiv */
                           $(subdiv).hide();
                       }
                 });
             }
     
         });         
    }
    
    return self;    
}())



$(document).ready(function(){
    frontEnd.init(1);
});