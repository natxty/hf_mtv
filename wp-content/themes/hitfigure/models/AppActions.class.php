<?php

namespace hitfigure\models;

/*
 * Our actions handler class
 */


class AppActions {

	public function trigger_action($type) {
		// Call functions based on a triggered action
		
		switch($type) {
			case 'bid_placed':
				// Get all users who have bid on this lead
				
				// Loop through them and create the necessary user type (dealer class/ manufacturer class)
				
				// Call their bid_placed functions which should check their user email permissions and either send an alert + email or not
				// it should also double check if thier bid has been outbid and send them a new outbid alert w/ email
				
				// Get the lead that was bid on
				
				
				// Send the submitter of that lead an email
				
				break;
		}
	
	}
	
}
