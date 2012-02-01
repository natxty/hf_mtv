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
	
	public static $CHILDREN = 1;
	public static $PARENTS	= 0;

	public function __construct() {
		$this->post_type	= 'post';
		$this->order 		= 'ASC';
		$this->orderby 		= 'menu_order';
		$this->args			= array();
	}
	
	private function _build_query_args() {
		$args = array(
			'post_type'	=>$this->post_type,
			'order'		=>$this->order,
			'orderby'	=>$this->orderby		
		);
			
		return array_merge($args, $this->args);
	}

	public function build_tree($type=1) {
		$args = $this->_build_query_args();
		$parent_query = new WP_Query( $args );

		foreach ($parent_query->posts as $post) {
			array_push($this->tree,$post);
			$_li = array();
			if ($type === 1) 
			{
				$this->_child_posts($post,&$_li);
			} 
			elseif ($type === 0) 
			{
				$this->_parent_posts($post,&$_li);
			}
			if (!empty($_li))
				array_push($this->tree,$_li);
		}
	}
	
	public function _child_posts($post,$li) {
		$child_posts = get_children( array('post_parent'=>$post->ID, 'orderby'=>$this->orderby, 'order'=>$this->order, 'post_type'=>$this->post_type) );

		foreach( $child_posts as $child_post ) {
			$_li = array();
			array_push($li,$child_post);
			$this->_child_posts($child_post,&$_li);
			if ( !empty($_li) )
				array_push($li,$_li);
		}
	}
	
	public function _parent_posts($post,$li) {
		if ($post_parent = $post->post_parent) {
			$parent = get_post($post_parent);
			$_li = array();
			array_push($li,$parent);
			$this->_parent_posts($parent, &$_li);
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