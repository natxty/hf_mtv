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
				
				// But first make sure the highest bidder isn't the current user...
				if ($client->id != $hitfigure->admin->user->id) {
					AlertCollection::new_alert('outbid', $client->id, $vehicle->id);
				}
				
				// Then email the currently logged in user
				AlertCollection::new_alert('newbid', null, $vehicle->id);
				
				// Send the submitter of that lead an email
				$vehicle->seller_new_bid_email($bid);
				break;
				
				
			case 'bid_won':
				// Geez, lots of stuff huh...
				// we're calling this from a cron script btw...
				// but lets assume we have this...
				$vehicle 	= $args['vehicle'];
				
				// We have to email the winner...
				$client = BidCollection::getHighestBidder($vehicle->id);
				AlertCollection::new_alert('won', $client->id, $vehicle->id);
				
				//  and the seller...
				$vehicle->seller_won_bid_email($client);
				
				// Then... well, the rest we can do in our cron script...				
				break;
			
			case 'new_lead':
				$vehicle 	= $args['vehicle']; // Vehicle model instance
				$dealers	= $args['dealers']; // '$dealers' is our DB wp_closestdealers results
				
				$ids = array();
				$dealer_vars = array();
				
				foreach ($dealers as $dealer) {
					//print_r($dealer);			
					$dealer_id 			= $dealer->dealer_id;
					$manufacturer_id 	= $dealer->manufacturer_id;
					
					if ($manufacturer_id && !in_array($manufacturer_id, $ids)) {
						$ids[] = $manufacturer_id;
					}
					if ($dealer_id && !in_array($dealer_id, $ids)) {
						$ids[] 				= $dealer_id;
						$client				= new Dealer(array('id'=>$dealer_id));
						$client->fetch();
						$dealer_vars[]		= $client->get_vars();
					}					
				}
				
				foreach ($ids as $id) {
					AlertCollection::new_alert('newlead', $id, $vehicle->id);
				}
				
				// then... send a confirmation to the seller
				$vehicle->seller_confirm_new_lead_email($dealer_vars);
				
				break;
		}
	
	}
	
}
