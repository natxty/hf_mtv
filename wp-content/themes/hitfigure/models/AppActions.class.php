<?php

namespace hitfigure\models;

/*
 * Our actions handler class
 */


class AppActions {

	public function trigger_action($type, $args) {
		// Call functions based on a triggered action
		$hitfigure = HitFigure::getInstance();
		
		switch($type) {
			case 'bid_placed':
				$vehicle 	= $args['vehicle'];
				$bid 		= $args['bid'];
				
				// We just get the highest bidder, then email them they were outbid
				$client = BidCollection::getHighestBidder($vehicle->id);
				AlertCollection::new_alert('outbid', $client->id);
				
				// Then email the currently logged in user
				AlertCollection::new_alert('newbid');
				
				// Send the submitter of that lead an email
				$vehicle->seller_new_bid_email();
				break;
		}
	
	}
	
}
