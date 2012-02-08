<?php

/*
Creates a tree hierarchy representing parent/child nesting of posts;
Includes a 'flatten_tree' function to create a one-dimensional ordered array of the tree;
*/

interface iMagPostTree {
	public function build_tree();
	public function flatten_tree();
}

class MagPostTree implements iMagPostTree {
	public $tree = array();

	public function __construct() {
		$this->post_type		= 'post';
		$this->order 			= 'ASC';
		$this->orderby 			= 'menu_order';
		$this->posts_per_page	= -1;
	}
	
	private function _build_query_args($post_parent=null) {
		$args = array(
			'post_type'			=>$this->post_type,
			'order'				=>$this->order,
			'orderby'			=>$this->orderby,
			'posts_per_page'	=>$this->posts_per_page
		);
		
		if ( isset($post_parent) )
			$args['post_parent'] = $post_parent;
			
		return $args;
	}

	public function build_tree() {
		$args = $this->_build_query_args(0);
		$parent_query = new WP_Query( $args );
		foreach ($parent_query->posts as $post) {
			array_push($this->tree,$post);
			$_li = array();
			$this->_child_posts($post,&$_li);
			if (!empty($_li))
				array_push($this->tree,$_li);
		}
	}
	
	public function _child_posts($post,$li) {
		$child_posts = get_children( array(
			'post_parent'	=>$post->ID, 
			'orderby'		=>$this->orderby, 
			'order'			=>$this->order, 
			'post_type'		=>$this->post_type
			) 
		);

		foreach( $child_posts as $child_post ) {
			$_li = array();
			array_push($li,$child_post);
			$this->_child_posts($child_post,&$_li);
			if ( !empty($_li) )
				array_push($li,$_li);
		}
	}
	
	public function flatten_tree() {
		$li = array();
		$this->_flatten_tree_loop(&$li,$this->tree);
		return $li;
	}
	
	private function _flatten_tree_loop($li,$_li) {
		foreach( $_li as $item ) {
			if ( is_array($item) ) {
				$this->_flatten_tree_loop(&$li,$item);
			} else {
				array_push($li,$item);
			}
		}
	}

}



?>