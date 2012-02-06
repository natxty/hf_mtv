<?php

namespace hitfigure\models;

function filter_where_48hours( $where = '' ) {
	$where .= " AND post_date > '" . date('Y-m-d h:i:s', strtotime('-2 days')) . "'";
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

function filter_where_lt_50miles( $where = '' ) {
	global $wpdb;
	
	$hitfigure = HitFigure::getInstance();
	$_lat_filter = $hitfigure->admin->user->lat;
	$_lng_filter = $hitfigure->admin->user->lng;
		
	$where .= "AND
(
SELECT
	TRUE
FROM
	(
		SELECT 
			pm.meta_value AS latitude,
			p.ID AS ID
		FROM 
			wp_posts AS p
		INNER JOIN
			wp_postmeta AS pm
		ON
			p.ID = pm.post_id
		WHERE
			pm.meta_key = 'lat'
	) lat
INNER JOIN
	(
		SELECT 
			pm.meta_value AS longitude,
			p.ID AS ID
		FROM 
			wp_posts AS p
		INNER JOIN
			wp_postmeta AS pm
		ON
			p.ID = pm.post_id
		WHERE
			pm.meta_key = 'lng'
	) lng
ON
	lng.ID = lat.ID
WHERE
	((ACOS(SIN( $_lat_filter * PI() / 180) * SIN(lat.latitude * PI() / 180) + COS( $_lat_filter * PI() / 180) * COS(lat.latitude * PI() / 180) * COS(($_lng_filter - lng.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 0.621371192) < 50
	AND
	lat.ID = wp_posts.ID
) IS NOT NULL";

	return $where;
}