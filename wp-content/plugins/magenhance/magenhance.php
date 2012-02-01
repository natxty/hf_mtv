<?php
/*
Plugin Name: Magnetic Enhance
*/

require_once(dirname(__FILE__).'/includes/class_MagAsset.php');
//require_once(dirname(__FILE__).'/includes/class_MagPostTree.php');

class MagEnhance {
	public function init() {
		if ( is_admin() ) {
			add_action( 'save_post', array(&$this,'metabox_opts_save') );
			$this->register_cpt();
			$this->register_metaboxes();
		} else {		
			$this->deregister_all();
			$this->register_enhancejs();
		}
	}
	
	public function register_cpt() {
	  // register 'cpt-script'
	  $labels = array(
	    'name' => 'Script',
	    'singular_name' => 'Script',
	    'add_new' => 'Add New',
	    'add_new_item' => 'Add New Script',
	    'edit_item' => 'Edit Script',
	    'new_item' => 'New Script',
	    'all_items' => 'All Scripts',
	    'view_item' => 'View Script',
	    'search_items' => 'Search Scripts',
	    'not_found' =>  'No scripts found',
	    'not_found_in_trash' => 'No scripts found in Trash', 
	    'parent_item_colon' => '',
	    'menu_name' => 'Scripts'
	  );
	  $args = array(
	    'labels' => $labels,
	    'public' => false,
	    'publicly_queryable' => false,
	    'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
	    'rewrite' => true,
	    'capability_type' => 'post',
	    'has_archive' => false, 
	    'hierarchical' => true,
	    'menu_position' => 57,
	    'supports' => array('title','page-attributes','custom-fields')
	  ); 
	  register_post_type('cpt-script',$args);

	  // register 'cpt-style'
	  $labels = array(
	    'name' => 'Style',
	    'singular_name' => 'Style',
	    'add_new' => 'Add New',
	    'add_new_item' => 'Add New Style',
	    'edit_item' => 'Edit Style',
	    'new_item' => 'New Style',
	    'all_items' => 'All Styles',
	    'view_item' => 'View Style',
	    'search_items' => 'Search Styles',
	    'not_found' =>  'No styles found',
	    'not_found_in_trash' => 'No styles found in Trash', 
	    'parent_item_colon' => '',
	    'menu_name' => 'Styles'
	  );
	  $args = array(
	    'labels' => $labels,
	    'public' => false,
	    'publicly_queryable' => false,
	    'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
	    'rewrite' => true,
	    'capability_type' => 'post',
	    'has_archive' => false, 
	    'hierarchical' => true,
	    'menu_position' => 	58,
	    'supports' => array('title','page-attributes','custom-fields')
	  ); 
	  register_post_type('cpt-style',$args);
	 	  		
	}

	public function deregister_all() {
		// Cue our deregistering after all scripts and styles have been registered (priority=1000)...
		add_action('wp_enqueue_scripts', function() {
			global $wp_scripts, $wp_styles;
		
			if(!is_admin()){
				if (isset($wp_scripts->registered) && is_array($wp_scripts->registered) && !empty($wp_scripts->registered)) {
					foreach ( array_keys($wp_scripts->registered) as $scriptname) {
						wp_deregister_script($scriptname);
					}
				}
				if (isset($wp_styles->registered) && is_array($wp_styles->registered) && !empty($wp_styles->registered)) {			
					foreach ( array_keys($wp_styles->registered) as $stylename) {
						wp_deregister_style($stylename);
					}
				}				
			}	
		}, 1000);
	}
	
	public function register_js_vars() {
		/* Uses wp_localize_script to print out our wp blog urls */
	
	    add_action ('wp_enqueue_scripts', function() {
	    	// i.js is just a dummy script...dummy
		    wp_register_script('magenhance-js-vars',plugins_url('/js/i.js', __FILE__));	
		
			$js_runtime_settings = array(
			    'ajaxurl' 				=>admin_url( 'admin-ajax.php' ),
				'url'					=>get_bloginfo('url'),
				'wpurl'					=>get_bloginfo('wpurl'),
				'stylesheet_directory'	=>get_bloginfo('stylesheet_directory'),
				'stylesheet_url'		=>get_bloginfo('stylesheet_url'),
				'template_directory'	=>get_bloginfo('template_directory'),
				'template_url'			=>get_bloginfo('template_url'),
				'atom_url'				=>get_bloginfo('atom_url'),
				'rss2_url'				=>get_bloginfo('rss2_url'),
				'rss_url'				=>get_bloginfo('rss_url'),
				'pingback_url'			=>get_bloginfo('pingback_url'),	
				'rdf_url'				=>get_bloginfo('rdf_url'),	
				'comments_atom_url'		=>get_bloginfo('comments_atom_url'),				
				'comments_rss2_url'		=>get_bloginfo('comments_rss2_url'),
			    'current_blog_id' 		=>get_current_blog_id()
			);
		
			wp_localize_script('magenhance-js-vars', 'WordPress', $js_runtime_settings);
			wp_enqueue_script('magenhance-js-vars');
		}, 1002);	
	}
	
	static function passed() {
		return false;
		return isset($_COOKIE['enhanced']) && $_COOKIE['enhanced']=='pass';
	}

	static function failed() {
		return isset($_COOKIE['enhanced']) && $_COOKIE['enhanced']=='fail';
	}
	
	public function register_enhancejs() {	
		// If enhance.js set its cookie to 'fail' then don't do anything...
		if ( self::failed() )
			return;	
		
		// Print our Site Urls
		$this->register_js_vars();

		// Create our script and styles trees
		// We'll use these to either enqueue 
		// or pass to enhance.js as objects
		
		$script_tree = new MagPostTree();
		$script_tree->post_type = 'cpt-script';
		$script_tree->args = array('post_parent'=>0);
		$script_tree->build_tree();
	
		$style_tree = new MagPostTree();
		$style_tree->post_type = 'cpt-style';
		$style_tree->args = array('post_parent'=>0);
		$style_tree->build_tree();
		
		if ( self::passed() ) {
			// Enhance.js set its cookie to 'pass'
			// We can add our scripts and styles right into the document...
			
			// Add a hook to add the 'enhanced' class to the html element...
			add_filter('mag_html_classes',function($classes) {
				$classes[] = 'enhanced'; 
				return $classes;
			});
		
			//Enqueue our scripts and styles...
			$c=0;
			foreach ( $style_tree->flatten_tree() as $data  ) {
				$asset = new MagAsset($data->ID,$data);
				$asset->enqueue(1001 + ++$c);
								
			}	

			foreach ( $script_tree->flatten_tree() as $data  ) {
				$asset = new MagAsset($data->ID,$data);
				$asset->enqueue(1001 + ++$c);				
			}					
		
		} else {
			// Probably the first visit to the website
			// Run our enhance.js scripts...
			
			// Queue our 'enhance.js' and 'enhance.css' after our deregister scripts runs...
			add_action ('wp_enqueue_scripts', function() {
				wp_enqueue_script('enhance.js', plugins_url('/js/enhance.min.js', __FILE__));
				wp_enqueue_style('enhance.css', plugins_url('/css/enhance.css', __FILE__));			
			},1001);			
		
			add_action('print_head_scripts',function() use ($script_tree, $style_tree) {
				// get our style and script posts, print them out as objects
	
				function asset_cb($data) {
					$asset = new MagAsset($data->ID,$data);
					return $asset->get_obj_string();			
				}
				
				$scripts_objs 	= join( ",\n\t\t", array_map("asset_cb", $script_tree->flatten_tree()));
				$styles_objs 	= join( ",\n\t\t", array_map("asset_cb", $style_tree->flatten_tree() ));			
?>
<script type="text/javascript">
/* <![CDATA[ */
  enhance({
  	loadStyles: [
		<?php echo $styles_objs; ?>
  	],
  	loadScripts: [
		<?php echo $scripts_objs; ?>  		
  	],
  	onScriptsLoaded: function() { document.getElementsByTagName('html')[0].className += ' visible'; }
  });
/* ]]> */
</script>
<?php		
			});
		}
	}

	public function register_metaboxes() {
		add_action( 'add_meta_boxes', function() {
		    add_meta_box(
		        'ck_mag_cpt_style_opts',
		        'Style Options', 
		        'ck_mag_enhance_asset_opts',
		        'cpt-style',
		        'normal'
		    );	
		    add_meta_box(
		        'ck_mag_cpt_script_opts',
		        'Script Options', 
		        'ck_mag_enhance_asset_opts',
		        'cpt-script',
		        'normal'
		    );
		});				
	}
	
	public function metabox_opts($post) {
		$type = null;
		if ($post->post_type == 'cpt-script') {
			wp_nonce_field( plugin_basename( __FILE__ ), 'ck_mag_cpt_script_opts_noncename' );
			$type = 'script';
		} else { // style
			wp_nonce_field( plugin_basename( __FILE__ ), 'ck_mag_cpt_style_opts_noncename' );
			$type = 'style';
		}
		
		// Metabox options for both scripts and styles
		$post_id = $post->ID;
		
		$source_path = 	get_post_meta($post_id,'enh-source-path',true);
		$source_root = 	get_post_meta($post_id,'enh-source-root',true);
		$media = 		get_post_meta($post_id,'enh-media',true);
		$excludemedia = get_post_meta($post_id,'enh-excludemedia',true);
		$iecondition = 	get_post_meta($post_id,'enh-iecondition',true);	
?>
	<p>
		<strong>Source Path</strong>
	</p>
	<label class="screen-reader-text" for="ck_mag_enhance_opts_box_source_path">
		<?php echo ucfirst($type); ?> Source Path
	</label>
	<select id="ck_mag_enhance_opts_box_source_root" name="ck_mag_enhance_opts_box_source_root">
		<option value='none' 	<?php if ($source_root == 'none' || !isset($source_root)) echo 'selected="true"'; ?>>None&mdash;HTTP:/</option>
		<option value='theme' 	<?php if ($source_root == 'theme') echo 'selected="true"'; ?>>Theme Directory</option>
		<option value='plugins' <?php if ($source_root == 'plugins') echo 'selected="true"'; ?>>Plugins Directory</option>
	</select>
	&nbsp;/&nbsp;
	<input type="text" id="ck_mag_enhance_opts_box_source_path" name="ck_mag_enhance_opts_box_source_path" value="<?php echo $source_path; ?>" size="20" />
	<p>
		<strong>Media</strong>
	</p>
	<label class="screen-reader-text" for="ck_mag_enhance_opts_box_media">
		<?php echo ucfirst($type); ?> Media
	</label>
	<input type="text" id="ck_mag_enhance_opts_box_media" name="ck_mag_enhance_opts_box_media" value="<?php echo $media; ?>" size="20" />
	<p>
		<strong>Exclude Media</strong>
	</p>
	<label class="screen-reader-text" for="ck_mag_enhance_opts_box_excludemedia">
		<?php echo ucfirst($type); ?> Exclude Media
	</label>
	<input type="text" id="ck_mag_enhance_opts_box_excludemedia" name="ck_mag_enhance_opts_box_excludemedia" value="<?php echo $excludemedia; ?>" size="20" />
	<p>
		<strong>IE Condition</strong>
	</p>
	<label class="screen-reader-text" for="ck_mag_enhance_opts_box_iecondition">
		<?php echo ucfirst($type); ?> IE Condition
	</label>
	<input type="text" id="ck_mag_enhance_opts_box_iecondition" name="ck_mag_enhance_opts_box_iecondition" value="<?php echo $iecondition; ?>" size="20" />
<?php			
	}
	
	public function metabox_opts_save($post_id) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		    return;
				
		if ( !current_user_can( 'edit_page', $post_id ) )
		        return; 		
		    
		$type = null;    
		if ( isset($_POST['ck_mag_cpt_script_opts_noncename']) && wp_verify_nonce( $_POST['ck_mag_cpt_script_opts_noncename'], plugin_basename( __FILE__ ) ) ) {
			$type = 'script';
		} elseif ( isset($_POST['ck_mag_cpt_style_opts_noncename']) && wp_verify_nonce( $_POST['ck_mag_cpt_style_opts_noncename'], plugin_basename( __FILE__ ) ) ) {
			$type = 'style';
		} else {
			return;
		}
		
		$source_path = 	$_REQUEST['ck_mag_enhance_opts_box_source_path'];
		$source_root = 	$_REQUEST['ck_mag_enhance_opts_box_source_root'];
		$media = 		$_REQUEST['ck_mag_enhance_opts_box_media'];
		$excludemedia = $_REQUEST['ck_mag_enhance_opts_box_excludemedia'];
		$iecondition = 	$_REQUEST['ck_mag_enhance_opts_box_iecondition'];
		
		update_post_meta($post_id,'enh-source-path',	$source_path);
		update_post_meta($post_id,'enh-source-root',	$source_root);
		update_post_meta($post_id,'enh-media',			$media);
		update_post_meta($post_id,'enh-excludemedia',	$excludemedia);
		update_post_meta($post_id,'enh-iecondition',	$iecondition);				
	}
}

function ck_mag_enhance_asset_opts($post) {
	$enh = new MagEnhance();
	$enh->metabox_opts($post);
}


add_action('init', function() {
	$enh = new MagEnhance();
	$enh->init();
});

add_filter('is_protected_meta', 'protect_enhance_meta', 10, 2);
function protect_enhance_meta($protected, $meta_key) {

	$enhance_meta_keys = array(
		'enh-source-path',
		'enh-source-root',
		'enh-media',
		'enh-excludemedia',
		'enh-iecondition'
	);
	
    return in_array($meta_key, $enhance_meta_keys) ? true : $protected;
}

function html_class() {
	$classes = apply_filters('mag_html_classes',array());
	if ( count($classes) ) {
		echo 'class="' . join(' ', $classes) . '"';
	}
}