<?php 
/*
 Plugin Name: Responsive Food and Drink Menu
 Description: This plugin lets you easily create a responsive food and drink menu either as a page or by using shortcode to insert their menu into their posts or page.
 Version:     1.1
 Author:      Corporate Zen
 Author URI:  http://www.corporatezen.com/
 License:     GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 
 Responsive Food and Drink Menu is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.
 
 Responsive Food and Drink Menu is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with Responsive Food and Drink Menu. If not, see https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die( 'Error: Direct access to this code is not allowed.' );

// include our shortcode handling file
require_once 'menu_shortcode.php';

// de-activate hook
function rfadm_deactivate_plugin() {
	// clear the permalinks to remove our post type's rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rfadm_deactivate_plugin' );


// activation hook
function rfadm_active_plugin() {
	// trigger our function that registers the custom post type
	rfadm_setup_post_type();
	
	// clear the permalinks after the post type has been registered
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'rfadm_active_plugin' );

// Our custom post type function
function rfadm_setup_post_type() {
	
	$labels = array(
			'name'                => 'Menu Page',
			'singular_name'       => 'Menu Page',
			'menu_name'           => 'Menu Pages',
			'all_items'           => 'All Menu Pages',
			'view_item'           => 'View Menu Page',
			'add_new_item'        => 'Add New Menu Page',
			'add_new'             => 'Add New',
			'edit_item'           => 'Edit Menu Page',
			'update_item'         => 'Update Menu Page',
			'search_items'        => 'Search Menu Pages',
			'not_found'           => 'Not Found',
			'not_found_in_trash'  => 'Not found in Trash'
	);
	
	$args = array(
			'labels' => $labels,
			'menu_icon' => 'dashicons-carrot',
			'description' => 'Menu Pages are created automatically! You enter what is on your menu and how much it costs, and we do the rest.',
			'public' => true,
			'publicly_queryable' => true,
			'show_in_nav_menus' => true,
			//'_builtin' => true, /* internal use only. don't use this when registering your own post type. */
			//'_edit_link' => 'post.php?post=%d', /* internal use only. don't use this when registering your own post type. */
			'capability_type' => 'page',
			'map_meta_cap' => true,
			'menu_position' => 20,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'delete_with_user' => false,
			'supports' => array( 'title', 'editor', 'revisions' ),
			'show_in_rest' => true,
			'rest_base' => 'pages',
			'rest_controller_class' => 'WP_REST_Posts_Controller'
	);
	
	register_post_type( 'menu_page_cpt', $args );
}
add_action( 'init', 'rfadm_setup_post_type' );

/* handle meta below */
add_action( 'add_meta_boxes', 'rfadm_dynamic_add_custom_box' );

/* Do something with the data entered */
add_action( 'save_post', 'rfadm_dynamic_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function rfadm_dynamic_add_custom_box() {
	add_meta_box(
		'dynamic_sectionid',
		'Menu Items',
		'rfadm_dynamic_inner_custom_box',
		'menu_page_cpt');
}

/* Prints the box content */
function rfadm_dynamic_inner_custom_box() {
	global $post;
	
	//wp_nonce_field( plugin_basename( __FILE__ ), 'dynamicMeta_noncename' ); // Use nonce for verification
	
	?>
	<style>
	.menu_section {
		border: 1px solid black;
		padding: 10px;
		margin-bottom: 10px;
	}
	
	.menu_section .add_item {
		display: block;
	}
	
	.menu_section .title {
		min-width: 25%;
	}
	
	.add_section {
		margin-top: 10px !important;
		margin-bottom: 10px !important;
	}
	
	.item_titles {
	    padding-right: 200px;
	    margin-left: 140px;
	}
	
	.menu_section input.remove_item {
		margin-left: 5px;
		vertical-align: top;
	}
	
	p.menu_item_wrap {
		margin: 0.5em 0;
	}
	
	.sec_title {
		margin-bottom: 20px;
	}
	
	.item_name {
		min-width: 30%;
	}
	
	.remove_section_link {
		color: #a00;
		cursor: pointer;
    	text-decoration: underline;
    	float: right;
	}
	
	.remove_section_link:hover {
		color: red;
	    border: none;
	}
	
	.underline {
		text-decoration: underline;
	}
	</style>
	
	<?php if ( get_post_status($post->ID) == 'publish' ) { ?>
		<strong>Reminder: </strong>You can use this menu in any post or page by using this shortcode: <pre style="display:inline;">[display_menu p=<?php echo $post->ID; ?>]</pre><br>
	<?php } ?>
	
	<input type="button" value="Add New Section" class="add_section button-primary button" /> 
		
    <div id="meta_inner">
    <span id="menu">
    <?php

    $menu_items = get_post_meta ( $post->ID, 'menu_items', true );
    $section_names = get_post_meta ( $post->ID, 'menu_section_name', true );
    
    /*
    print "<pre>";
    print_r($section_names);
    echo '------------------------------------------<br>';
    print_r($menu_items);
    print "</pre>";
    */
    
    $item_count = 0;
    $section_count = 0;
    
    if ( count ( $section_names ) > 0 && !empty ( $section_names ) ) {
    	foreach ( $section_names as $section ) {
    		$section_count = $section_count + 1;
    		echo '
			<div class="menu_section" id="section_' . $section['section_id'] . '">
				<div class="menu_inner">
					<div class="sec_title">
						Section Title: <br><input type="text" class="title" name="menu_section[' . $section['section_id'] . '][section_name]" value="' . $section['section_name'] . '" /><input type="hidden" class="title" name="menu_section[' . $section['section_id'] . '][section_id]" value="' . $section['section_id'] . '" /><a class="remove_section_link">Remove Section</a>
					</div>';
    		
    		// display menu items
    		$new_itemID = '';
    		if ( count ( $menu_items ) > 0 ) {
    			echo '<span class="item_titles underline">Menu Item</span><span class="underline">Price</span>';
	    		foreach ($menu_items as $item) {
	    			$item_count = $item_count + 1;
	    			if ( $item['section'] == $section['section_id'] ) {
	    				echo '<p class="menu_item_wrap" id="item_' . $new_itemID . '"><input type="text" class="item_name" name="menu_items[' . $item_count . '][item_name]" value="' . $item['item_name'] . '" /> <input type="text" name="menu_items[' . $item_count . '][price]" value="' . $item['price'] . '" /><input type="hidden" name="menu_items[' . $item_count . '][section]" value="' . $section['section_id'] . '"/><input type="button" class="button remove_item" value="X"/></p>';
	    			} 
	    		}
    		}
    		
			echo '</div>
				<input type="button" value="Add New Item" class="add_item button" />
			</div>';
    	}
    }
    
    //echo '<div class="menu_section" id="section_' . $new_secID . '">Section Title: <input type="text" name="menu_section_name" value="' . $section_names . '" /><span class="add_item">Add Menu Item</span></div>';

    ?>
	</span><!-- END: #menu -->

<script>
    var $ = jQuery.noConflict();
    
    function getUniqueID(the_object) {
    	var the_ids = [];
    	
    	jQuery(the_object).each(function() {
        	id_attr = jQuery(this).attr('id');
        	curr_id = id_attr.substr(id_attr.length - 1);
    		the_ids.push(parseInt(curr_id));
    	});
    	
    	var i = 1;
    	
    	jQuery.each(the_ids, function(index, value) {
    		if (jQuery.inArray(i, the_ids) === -1) {
    			// not found, do nothing
    		} else {
    			// found
    			i = i + 1;
    		}
    	});
    	
    	return i;
    }
    
    $(document).ready(function() {

		// add new section
    	var numOfSections = <?php echo $section_count; ?>;
		$('.add_section').live('click', function() {
			numOfSections = numOfSections + 1;
			new_secID = getUniqueID('.menu_section');
			$('#menu').append('<div class="menu_section" id="section_' + new_secID + '"><div class="menu_inner"><div class="sec_title">Section Title: <br><input type="text" class="title" name="menu_section[' + new_secID + '][section_name]" value="" /><input type="hidden" class="title" name="menu_section[' + new_secID + '][section_id]" value="' + new_secID + '" /><a class="remove_section_link">Remove Section</a></div><span class="item_titles underline">Menu Item</span><span class="underline">Price</span></div><input type="button" value="Add New Item" class="add_item button" /></div>');
		});

		// add new item
    	var numOfItems = <?php echo $item_count; ?>;
    	$(".add_item").live('click', function() {
    		numOfItems = numOfItems + 1;
    		new_itemID = getUniqueID('.menu_item_wrap');
    		id_attr    = $(this).parents('.menu_section').attr('id');
    		curr_sec   = id_attr.substr(id_attr.length - 1);
    		                                                             
    		$(this).parents('.menu_section').find('.menu_inner').append('<p class="menu_item_wrap" id="item_' + new_itemID + '"> <input type="text" class="item_name" name="menu_items[' + numOfItems + '][item_name]" value="" /><input type="text" name="menu_items[' + numOfItems + '][price]" value="" /><input type="hidden" name="menu_items[' + numOfItems + '][section]" value="' + curr_sec + '"/><input type="button" class="button remove_item" value="X" /></p>');
    	});
    	
		// remove section
        $(".remove_section_link").live('click', function() {
        	var r = confirm("Are you sure you want to delete this entire section?");
        	if (r == true) {
        		$(this).parents('.menu_section').remove();
        	}
        });

		// remove single item
        $(".remove_item").live('click', function() {
            $(this).parents('.menu_item_wrap').remove();
        });
    });
    </script>
</div><?php

}

/* When the post is saved, saves our custom data */
function rfadm_dynamic_save_postdata( $post_id ) {
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    /*
    if ( !isset( $_POST['dynamicMeta_noncename'] ) )
        return;

    if ( !wp_verify_nonce( $_POST['dynamicMeta_noncename'], plugin_basename( __FILE__ ) ) )
        return;
	*/
    
    // OK, we're authenticated: we need to find and save the data
    $menu_items = ( isset( $_POST['menu_items'] ) ? $_POST['menu_items'] : array() );
    $section_names = ( isset( $_POST['menu_section'] ) ? $_POST['menu_section'] : array() );
    
    //$sanitized_menu_items = array();
    //$sanitized_section_names = array();
    
    /*
    if ( isset ( $_POST['menu_items'] ) ) {
    	foreach ( $_POST['menu_items'] as $item ) {
    		array_push ( $sanitized_menu_items, sanitize_text_field ( $item ) );
    	}
    }
    
    if ( isset ( $_POST['menu_section_name'] ) ) {
    	foreach ( $_POST['menu_section_name'] as $name ) {
    		array_push ( $sanitized_section_names, sanitize_text_field ( $name) ); 
    	}
    }
    */
    
    array_walk ( $menu_items, function ( &$value, &$key ) {
    	$value['item_name'] = sanitize_text_field ( $value['item_name'] );
    	$value['price']     = sanitize_text_field ( $value['price'] );
    	//$value['section']   = sanitize_text_field ( $value['section'] );
    });
    
    	array_walk ( $section_names, function ( &$value, &$key ) {
    	$value['section_name'] = sanitize_text_field ( $value['section_name'] );
    	$value['section_id'] = sanitize_text_field ( $value['section_id'] );
    	//$value['section']   = sanitize_text_field ( $value['section'] );
    });
        
    //$menu_items = isset( $_POST['menu_items'] ) ? (array) $_POST['menu_items'] : array();
    //$menu_items = array_map( 'sanitize_text_field', $menu_items);
    
    //$section_names = isset( $_POST['menu_section_name'] ) ? (array) $_POST['menu_section_name'] : array();
    //$section_names = array_map( 'sanitize_text_field', $section_names);

    update_post_meta ( $post_id, 'menu_items', $menu_items);
    update_post_meta ( $post_id, 'menu_section_name', $section_names );
}

// remove ?post_type= and &p= from url
/*
function rfadm_remove_cpt_slug( $post_link, $post, $leavename ) {
	
	if ( 'menu_page_cpt' != $post->post_type || 'publish' != $post->post_status ) {
		return $post_link;
	}
	
	$post_link = str_replace( '/?post_type=' . $post->post_type . '&p=' . $post->ID, '/' . $post->post_name, $post_link );
	
	return $post_link;
}
add_filter( 'post_type_link', 'rfadm_remove_cpt_slug', 10, 3 );


function rfadm_parse_request_trick( $query ) {
	
	// Only noop the main query
	if ( ! $query->is_main_query() )
		return;
		
		// Only noop our very specific rewrite rule match
		if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
			return;
		}
		
		// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
		if ( ! empty( $query->query['name'] ) ) {
			$query->set( 'post_type', array( 'post', 'page', 'menu_page_cpt' ) );
		}
}
add_action( 'pre_get_posts', 'rfadm_parse_request_trick' );
*/


// load our cpt template
add_filter( 'single_template', 'rfadm_custom_post_type_template' );
function rfadm_custom_post_type_template($single_template) {
	global $post;
	 
	if ($post->post_type == 'menu_page_cpt' ) {
		$single_template = dirname( __FILE__ ) . '/rfdm-single-menu-page.php';
	}
	
	return $single_template;
	wp_reset_postdata();
}

/////////////////////////////// SIGN UP ////////////////////////////
add_action('wp_dashboard_setup', 'rfadm_custom_dashboard_widgets');
function rfadm_custom_dashboard_widgets() {
	global $wp_meta_boxes;
	wp_add_dashboard_widget('corporatezen_newsletter', 'CZ Newsletter', 'rfadm_mailchimp_signup_widget');
}

function rfadm_mailchimp_signup_widget() {
	$user    = wp_get_current_user();
	$email   = (string) $user->user_email;
	$fname   = (string) $user->user_firstname;
	$lname   = (string) $user->user_lastname;
	?>
	
<!-- Begin MailChimp Signup Form -->
<div id="mc_embed_signup">
	<form action="//corporatezen.us13.list-manage.com/subscribe/post?u=e9426a399ea81798a865c10a7&amp;id=9c1dcdaf0e" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
	    <div id="mc_embed_signup_scroll">
	    
			<h2>Don't miss important updates!</h2>
			<p>Don't worry, we hate spam too. We send a max of 2 emails a month, and we will never share your email for any reason. Sign up to ensure you don't miss any important updates or information about this plugin or theme. </p>
		
			<div class="mc-field-group">
				<!--<label for="mce-EMAIL">Email Address  <span class="asterisk">*</span></label>-->
				<input type="email" value="<?php echo $email; ?>" name="EMAIL" class="fat_wide required email" id="mce-EMAIL" style="width: 75%;">
				<input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button button-primary">
			</div>
		
			<div class="mc-field-group">
				<input type="hidden" value="<?php echo $fname; ?>" name="FNAME" class="" id="mce-FNAME">
			</div>
			<div class="mc-field-group">
				<input type="hidden" value="<?php echo $lname; ?>" name="LNAME" class="" id="mce-LNAME">
			</div>
			
		
			<div id="mce-responses" class="clear">
				<div class="response" id="mce-error-response" style="display:none;color: red;font-weight: 500;margin-top: 20px; margin-bottom: 20px;"></div>
				<div class="response" id="mce-success-response" style="display:none;color: green;font-weight: 500;margin-top: 20px; margin-bottom: 20px;"></div>
			</div>    
			
			<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
		    <div style="position: absolute; left: -5000px;" aria-hidden="true">
		    	<input type="text" name="b_e9426a399ea81798a865c10a7_9c1dcdaf0e" tabindex="-1" value="">
		    </div>
	
	    </div>
	</form>
</div>

<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>
<script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
<!--End mc_embed_signup-->
	
	<?php
}
?>