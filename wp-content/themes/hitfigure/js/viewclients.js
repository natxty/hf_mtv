$(document).ready(function() {
	// get the type of client we're looking for...
	var type = $('#view-clients').attr('data-type');
	
	var oTable = $('#view-clients').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "/ajax_view_clients/"+type,
		"aoColumns": [
			{ "mDataProp": "business_name" },
			{ "mDataProp": "city" },
			{ "mDataProp": "state" },
			{ "mDataProp": "reports" },
			{ "mDataProp": "edit_client" }
		]
	} );
} );