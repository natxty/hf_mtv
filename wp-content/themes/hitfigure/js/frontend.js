$(document).ready(function() {
    var debug = true;

    //When page loads...
	$(".seller_form_container").hide(); //Hide all content
	$(".seller_form_container:first").show(); //Show first tab content

	//On Click Event
	$(".seller_form_container a.button").click(function() {

        var id = $(this).closest("div").attr("id"); //gather for validation purposes..
        var next_page = $(this).attr("href");
        if(debug) console.log("#" + id);

        var errors = false;

        //validate select inputs
         $("#" + id + " select").each(function() {
            //look through our class declaration from our micro-templates and find
            //if the class="validate" is present (even in combination with other classes)
            var isValidate = $(this).parent().children('.required').length;
            var selectID = $(this).attr('id');
            if(isValidate) { //only check the ones with class="validate"
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
                }

            }

         });
        //$("select option:selected")

        //validate text inputs:
        $("#" + id + " input[type=text]").each(function(n, element) {
            //look through our class declaration from our micro-templates and find
            //if the class="validate" is present (even in combination with other classes)
            var isValidate = $(this).parent().children('.required').length;

            if(isValidate) { //only check the ones with class="validate"
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

        $("#" + id + " .RadioGroup").each(function() {

            //get the trimmed id...
            var radioID = $(this).attr('id');
            var radioName = radioID.replace(/wrapper\-id_/i, '');
            if(debug) console.log(radioName);

            //do we have a required child div?
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
            //alert('errors');
            
        } else {
           $('.seller_form_container').hide();
            $(next_page).fadeIn();
        }


		return false;
	});


    MTV.debugging();

    /* some nice formy functions */
    jQuery('#id_vehicle_year').change(function() {
		get_makes();
		car_year = jQuery('#id_vehicle_year').val();
		//alert(car_year);
	});



    //GET MAKES BASED ON YEAR
	function get_makes() {
		//get selected car values
		car_year = jQuery('#id_vehicle_year').val();

		MTV.do_ajax(
			// global javascript variable
			'/ajax_form_data/',
			{
				// here we declare the parameters to send along with the request
				// this means the following action hooks will be fired:
				// vehicle_models
				action : 'vehicle_makes',

				// other parameters can be added along with "action"
				car_year : car_year
			},
			function( response ) {
                alert(response);
			},
            function(error) {
                alert('error')
            }
		);
	}
	
	
} );