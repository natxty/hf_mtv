<?php

namespace hitfigure\models;

function filter_where_48hours( $where = '' ) {
	$where .= " AND post_date > '" . date('Y-m-d h:i:s', strtotime('-2 days')) . "'";
	return $where;
}

function filter_where_48hours_past( $where = '' ) {
	$where .= " AND post_date < '" . date('Y-m-d h:i:s', strtotime('-2 days')) . "'";
	return $where;
}

function filter_where_registered_for_lead( $where = '' ) {
	global $wpdb;
		
	$hitfigure = HitFigure::getInstance();
	$user_id = $hitfigure->admin->user->id;
	$roles = $hitfigure->admin->user->roles;
	
	// Choose how we filter based on who is logged in
	$filter_on = "";
	$group_by = "";
	
	if ( in_array('manufacturer', $roles) ) {
		$filter_on = "cd.manufacturer_id = $user_id AND";
		$group_by =  "cd.manufacturer_id,"; 
	} elseif ( in_array('dealer', $roles) ) {
		$filter_on = "cd.dealer_id = $user_id AND";
		$group_by =  "cd.dealer_id,";
	}
	
	$where .= "AND	
(
SELECT
	TRUE
FROM
	wp_closestdealers as cd
WHERE
	$filter_on
	cd.vehicle_id = wp_posts.ID
GROUP BY
	$group_by cd.vehicle_id
) IS NOT NULL";

	return $where;
}