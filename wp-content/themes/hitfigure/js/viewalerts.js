$(document).ready(function() {
	var oTable = $('#view-alerts').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "/ajax_view_alerts",
		"aoColumns": [
			{ "mDataProp": "alert_type" },
			{ "mDataProp": "alert_title" },
			{ "mDataProp": "view_alert" }
		]
	} );
} );