$(document).ready(function() {
	var oTable = $('#view-leads').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "/ajax_view_leads",
		"aoColumns": [
			{ "mDataProp": "bid_offers" },
			{ "mDataProp": "bid_status" },
			{ "mDataProp": "time_left" },
			{ "mDataProp": "vehicle_model" },
			{ "mDataProp": "vehicle_make" },
			{ "mDataProp": "vehicle_mileage" }
		]
	} );
} );