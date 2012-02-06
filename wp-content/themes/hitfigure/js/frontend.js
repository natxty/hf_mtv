$(document).ready(function() {
    var debug = true;

    //When page loads...
	$(".seller_form_container").hide(); //Hide all content
    $(".subinfo").hide(); //Hide all subsections that will open with proper radio buttons

	$(".seller_form_container:first").show(); //Show first "page" content

	//Page Progression + Validation click functions
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
            //error classes added individually above...
            //TO-DO: print error description somewhere...
            
        } else {
            //we're clear for lift-off
           $('.seller_form_container').hide();
            $(next_page).fadeIn();
        }
        //make sure we stop the normal anchor/link function:
		return false;
	});

    //wtf?
    MTV.debugging();

    /* Main Select Elements + AJAX calls to get filtered options from the DB */
    /* Year => Makes */
    jQuery('#id_vehicle_year').change(function() {

		cyear = jQuery('#id_vehicle_year').val();
        var a = {
            text : 'make',
            xdata : {
                action : "vehicle_makes",
                data : cyear
            },
            result_id : '#id_vehicle_make'

        };
        get_car_data(a);
	});

    /* Makes => Models */
    jQuery('#id_vehicle_make').change(function() {
        //take make and return models
		cmake = jQuery('#id_vehicle_make').val();
        var a = {
            text : 'model',
            xdata : {
                action : "vehicle_models",
                data : cmake
            },
            result_id : '#id_vehicle_model'

        };
        get_car_data(a);
	});

    /* Models => Trim (if any) */
    jQuery('#id_vehicle_model').change(function() {

		cmodel = jQuery('#id_vehicle_model').val();
        var a = {
            text : 'trim',
            xdata : {
                action : "vehicle_trims",
                data : cmodel
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
        var resultType = 'car_' + obj.text;
        var topOptionText = ucwords(obj.text);

		MTV.do_ajax(
			// global javascript variable
			'/ajax_form_data/',
			obj.xdata,
			function( response ) {
                //if(debug) alert(response.length);
                var listItems= "<option value=''>-- Select a " + topOptionText + " --</option>";
				for (var i = 1; i < response.length; i++){
					listItems+= "<option value='" + response[i][resultType] + "'>" + response[i][resultType] + "</option>";
				}
				jQuery(obj.result_id).html(listItems);

			},
            function(error) {
                alert('error')
            }
		);
	}

    /*
    * Radio Button // Open Sub Info
     */
    $(".radio_group_wrapper").each(function() {

        var subdiv = $(this).children('.subinfo');
        
        //does this group/wrapper have a '.subinfo' div? if so...
        if(subdiv.length) {
            //get the trimmed id...
            var radioID = $(this).attr('id');
            var radioName = radioID.replace(/id_/i, '');
            radioName = radioName.replace(/_wrapper/i, '');
            if(debug) console.log(radioName);

            $('input[name=' + radioName + ']').change(function() {
                  if($(this).val() == 'Yes') {
                    //open subdiv
                    $(subdiv).show();
                  } else {
                      //close the subdiv
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
	
	
} );