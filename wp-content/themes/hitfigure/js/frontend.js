$(document).ready(function() {
    var debug = false;

    /* When page loads... */
	jQuery(".seller_form_container").hide(); /* Hide all content */
    jQuery(".subinfo").hide(); /* Hide all subsections that will open with proper radio buttons */

	jQuery(".seller_form_container:first").show(); /* Show first "page" content */

	/* Page Progression + Validation click functions */
	jQuery(".seller_form_container a.button").click(function() {

        var id = $(this).closest("div").attr("id"); /* gather for validation purposes.. */
        var next_page = $(this).attr("href");
        if(debug) console.log("#" + id);

        var errors = false;

        /* validate select inputs */
         $("#" + id + " select").each(function() {
            /* look through our class declaration from our micro-templates and find */
            /* if the class="validate" is present (even in combination with other classes) */
            var isValidate = $(this).parent().children('.required').length;
            var selectID = $(this).attr('id');
            if(isValidate) { /* only check the ones with class="validate" */
                var optionValue = $("#" + selectID + ' option:selected').val();
                if(optionValue.length < 1) {
                    $(this).parent().addClass('error');
                    errors = true;
                    if(debug) {
                        var selectName = $(this).attr('name');
                        console.log(selectName + ":" + optionValue );
                    }
                } else {
                    $(this).parent().removeClass('error');
					$(this).removeClass('error');
                }

            }

         });
        /* $("select option:selected") */

        /* validate text inputs: */
        jQuery("#" + id + " input[type=text]").each(function(n, element) {
            /* look through our class declaration from our micro-templates and find */
            /* if the class="validate" is present (even in combination with other classes) */
            var isValidate = $(this).parent().children('.required').length;

            if(isValidate) { /* only check the ones with class="validate" */
                var txtval = $(this).val();
                if(txtval.length < 1) {
                    $(this).parent().addClass('error');
                    errors = true;
                    if(debug) {
                        var inputName = $(this).attr('name');
                        console.log(inputName + ":" + txtval );
                        console.log(inputName + ":" + $(this).attr('class') );
                    }
                } else {
                    $(this).parent().removeClass('error');
                }


            }
        });

        jQuery("#" + id + " .RadioGroup").each(function() {

            /* get the trimmed id... */
            var radioID = $(this).attr('id');
            var radioName = radioID.replace(/wrapper\-id_/i, '');
            if(debug) console.log(radioName);

            /* do we have a required child div? */
            if($(this).children('.required').length) {

                if ($('input[name='+ radioName +']:checked').length) {
                    $(this).parent().removeClass('error');
                  } else {
                    if(debug) console.log(radioName + ": none are checked");
                    $(this).parent().addClass('error');
                    errors = true;
                }
            }
        });

        if(errors) {
            /* alert('errors'); */
            /* error classes added individually above... */
            /* TO-DO: print error description somewhere... */
            
        } else {
            /* we're clear for lift-off */
           $('.seller_form_container').hide();
            $(next_page).fadeIn();
        }
        /* make sure we stop the normal anchor/link function: */
		return false;
	});

    /* wtf? */
    MTV.debugging();

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

        };
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
					'car_year'	:	cyear,
					'car_make' :	cmake
				}
            },
            result_id : '#id_vehicle_model'

        };
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
					'car_year'	:	cyear,
					'car_model' :	cmake,
					'car_model'	:	cmodel
				}
            },
            result_id : '#id_vehicle_trim'

        };
        get_car_data(a);
	});



    /*
    * get_car_data(obj)
    *
    * Main Workhorse Function for retrieving the info, via AJAX,
    * to update Select elements with filtered data
    * 
     */
	function get_car_data(obj) {
		/*  Set current to disabled, it'll get overwritten when we add the ajax result */
		jQuery(obj.result_id).attr('disabled','disabled');
	
        var resultType = 'car_' + obj.text;
        var topOptionText = ucwords(obj.text);

		MTV.do_ajax(
			/*  global javascript variable */
			'/ajax_form_data/',
			obj.xdata,
			function( response ) {
                /* if(debug) alert(response.length); */
				jQuery(obj.result_id).parent().replaceWith(response.html);
				if(debug) console.log(obj.debug);
			},
            function(error) {
                alert('error')
            }
		);
	}

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

    /* Utility to capitalize words, much like ucwords() in PHP */
    function ucwords(str) {
        return (str + '').replace(/^([a-z])|[\s_]+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        })
    };
	

/* end frontend.js */
} );