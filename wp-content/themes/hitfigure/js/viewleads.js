$(document).ready(function() {
	var oTable = $('#view-leads-all').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "/ajax_view_leads/all",
		"aoColumns": [
			{ "mDataProp": "bid_offers" },
			{ "mDataProp": "bid_status" },
			{ "mDataProp": "time_left" },
			{ "mDataProp": "vehicle_model" },
			{ "mDataProp": "vehicle_make" },
			{ "mDataProp": "vehicle_mileage" },
			{ "mDataProp": "view_vehicle" }
		]
	} );

	var oTable = $('#view-leads-won').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "/ajax_view_leads/won",
		"aoColumns": [
			{ "mDataProp": "vehicle_model" },
			{ "mDataProp": "vehicle_make" },
			{ "mDataProp": "vehicle_mileage" },
			{ "mDataProp": "view_vehicle" }
		]
	} );
} );