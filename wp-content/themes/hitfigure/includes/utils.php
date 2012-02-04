<?php

function get_page_queue($page) {

	$page_queue = array();
	
	if ( $page->post_parent ) {
		/* Use our Post Tree builder to get all the parents for this post */
		$post_tree = new \MagPostTree();
		$post_tree->args = array('post_type'=>'page', 'page_id'=>$page->ID);
		$post_tree->build_tree(\MagPostTree::$PARENTS);
		/* Flatten it, reverse it, and assign it to our $page_queue var */
		$page_queue = array_reverse($post_tree->flatten_tree());		
	} else {
		/* Only one page, so only one item */
		$page_queue = array($page);
	}
	
	/* Sub-Menu */
	$url = 'http://'.$_SERVER['HTTP_HOST'];
	
	foreach ($page_queue as $_page) {
			$ck_mag_subnav_slug = 'page-subnav-' . $_page->post_name;
			$sub_nav= wp_nav_menu( array(
						'theme_location'	=> $ck_mag_subnav_slug, 
						'menu'				=> $ck_mag_subnav_slug,
						'fallback_cb' 		=> function() {},
						'menu_class'		=> 'page-header-menu',
						'echo'				=> false
						));
			$_page->sub_nav = str_replace($url, '', $sub_nav);
	}
	// there should be an apply_filter here so extra stuff can be added to each page (like meta data)
	/* Better yet... take a look at MTV's PostCollection model first */
	return $page_queue;
}

function state_select_form($id="state", $args = array()) {
	
	$s = new \Select($id);
	$s->setProperties($args);

	$s->add_option('opt',array('text'=>'-- Select One --'));
	$s->add_option('opt',array('text'=>'Alabama', 'value' => 'AL'));
	$s->add_option('opt',array('text'=>'Alaska', 'value' => 'AK'));
	$s->add_option('opt',array('text'=>'Arizona', 'value' => 'AZ'));
	$s->add_option('opt',array('text'=>'Arkansas', 'value' => 'AR'));
	$s->add_option('opt',array('text'=>'California', 'value' => 'CA'));
	$s->add_option('opt',array('text'=>'Colorado', 'value' => 'CO'));
	$s->add_option('opt',array('text'=>'Connecticut', 'value' => 'CT'));
	$s->add_option('opt',array('text'=>'Delaware', 'value' => 'DE'));
	$s->add_option('opt',array('text'=>'District Of Columbia', 'value' => 'DC'));
	$s->add_option('opt',array('text'=>'Florida', 'value' => 'FL'));
	$s->add_option('opt',array('text'=>'Georgia', 'value' => 'GA'));
	$s->add_option('opt',array('text'=>'Hawaii', 'value' => 'HI'));
	$s->add_option('opt',array('text'=>'Idaho', 'value' => 'ID'));
	$s->add_option('opt',array('text'=>'Illinois', 'value' => 'IL'));
	$s->add_option('opt',array('text'=>'Indiana', 'value' => 'IN'));
	$s->add_option('opt',array('text'=>'Iowa', 'value' => 'IA'));
	$s->add_option('opt',array('text'=>'Kansas', 'value' => 'KS'));
	$s->add_option('opt',array('text'=>'Kentucky', 'value' => 'KY'));
	$s->add_option('opt',array('text'=>'Louisiana', 'value' => 'LA'));
	$s->add_option('opt',array('text'=>'Maine', 'value' => 'ME'));
	$s->add_option('opt',array('text'=>'Maryland', 'value' => 'MD'));
	$s->add_option('opt',array('text'=>'Massachusetts', 'value' => 'MA'));
	$s->add_option('opt',array('text'=>'Michigan', 'value' => 'MI'));
	$s->add_option('opt',array('text'=>'Minnesota', 'value' => 'MN'));
	$s->add_option('opt',array('text'=>'Mississippi', 'value' => 'MS'));
	$s->add_option('opt',array('text'=>'Missouri', 'value' => 'MO'));
	$s->add_option('opt',array('text'=>'Montana', 'value' => 'MT'));
	$s->add_option('opt',array('text'=>'Nebraska', 'value' => 'NE'));
	$s->add_option('opt',array('text'=>'Nevada', 'value' => 'NV'));
	$s->add_option('opt',array('text'=>'New Hampshire', 'value' => 'NH'));
	$s->add_option('opt',array('text'=>'New Jersey', 'value' => 'NJ'));
	$s->add_option('opt',array('text'=>'New Mexico', 'value' => 'NM'));
	$s->add_option('opt',array('text'=>'New York', 'value' => 'NY'));
	$s->add_option('opt',array('text'=>'North Carolina', 'value' => 'NC'));
	$s->add_option('opt',array('text'=>'North Dakota', 'value' => 'ND'));
	$s->add_option('opt',array('text'=>'Ohio', 'value' => 'OH'));
	$s->add_option('opt',array('text'=>'Oklahoma', 'value' => 'OK'));
	$s->add_option('opt',array('text'=>'Oregon', 'value' => 'OR'));
	$s->add_option('opt',array('text'=>'Pennsylvania', 'value' => 'PA'));
	$s->add_option('opt',array('text'=>'Rhode Island', 'value' => 'RI'));
	$s->add_option('opt',array('text'=>'South Carolina', 'value' => 'SC'));
	$s->add_option('opt',array('text'=>'South Dakota', 'value' => 'SD'));
	$s->add_option('opt',array('text'=>'Tennessee', 'value' => 'TN'));
	$s->add_option('opt',array('text'=>'Texas', 'value' => 'TX'));
	$s->add_option('opt',array('text'=>'Utah', 'value' => 'UT'));
	$s->add_option('opt',array('text'=>'Vermont', 'value' => 'VT'));
	$s->add_option('opt',array('text'=>'Virginia', 'value' => 'VA'));
	$s->add_option('opt',array('text'=>'Washington', 'value' => 'WA'));
	$s->add_option('opt',array('text'=>'West Virginia', 'value' => 'WV'));
	$s->add_option('opt',array('text'=>'Wisconsin', 'value' => 'WI'));
	$s->add_option('opt',array('text'=>'Wyoming', 'value' => 'WY'));

	return $s;
}
