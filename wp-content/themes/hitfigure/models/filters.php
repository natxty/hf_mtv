<?php

namespace hitfigure\models;

function filter_where_48hours( $where = '' ) {
	$where .= " AND post_date > '" . date('Y-m-d h:i:s', strtotime('-2 days')) . "'";
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