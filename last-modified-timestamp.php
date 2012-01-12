<?php
/*
Plugin Name: Last Modified Timestamp
Description: This plugin will add information to the admin interface about when each post/page was last modified. No options currently available, simply activate and enjoy!
Version: 0.3
Author: Evan Mattson
*/

/*  Copyright 2011 Evan Mattson (email: evanmattson at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function last_modified_add() {
	add_action( 'admin_head', 'print_last_modified_css' );
	
	add_filter( 'post_updated_messages','modify_messages' );
	add_action( 'post_submitbox_misc_actions', 'add_modified_to_meta' );

	add_filter( 'manage_page_posts_columns','last_modified_column_heading' );
	add_filter( 'manage_post_posts_columns','last_modified_column_heading' );
	add_action( 'manage_pages_custom_column','last_modified_column_content', 10, 2 );
	add_action( 'manage_posts_custom_column','last_modified_column_content', 10, 2 );
	
	add_filter( 'manage_edit-post_sortable_columns', 'last_modified_column_register_sortable' );
	add_filter( 'manage_edit-page_sortable_columns', 'last_modified_column_register_sortable' );
}
add_action( 'admin_init', 'last_modified_add' );

// returns a formatted timestamp as a string
function construct_timestamp($mode='') { 
// instead of IF to better handle other possible modes in future
	switch ($mode) { 
		case 'wp-table' :
			$modified_timestamp = get_the_modified_date('Y/m/d') . '<br />' . get_the_modified_time();  // wp-tables
			break;
		default :
			$modified_timestamp = get_the_modified_date('M j, Y') . ' @ ' . get_the_modified_time(); //  post messages & meta box
	}
return $modified_timestamp;
}

// filters the admin messages at the top of the page on post.php for pages & posts to include the last modified timestamp.
function modify_messages($messages) {
	$modified_timestamp = construct_timestamp();
	$messages['post'] = array(
		 1 => sprintf( __('Post updated. <strong>%2$s</strong>. <a href="%1$s">View post</a>'), esc_url( get_permalink($post_ID) ), $modified_timestamp ),
		 4 => sprintf( __('Post updated. <strong>%2$s</strong>. <a href="%1$s">View post</a>'), esc_url( get_permalink($post_ID) ), $modified_timestamp ),
		);
	$messages['page'] = array(
		 1 => sprintf( __('Page updated. <strong>%2$s</strong>. <a href="%1$s">View page</a>'), esc_url( get_permalink($post_ID) ), $modified_timestamp ),
		 4 => sprintf( __('Page updated. <strong>%2$s</strong>. <a href="%1$s">View page</a>'), esc_url( get_permalink($post_ID) ), $modified_timestamp ),
		);
	return $messages;
}

// Add the Last Modified timestamp to the 'Publish' meta box in post.php
function add_modified_to_meta() {
	$modified_timestamp = sprintf( __('Last modified on: <strong>%1$s</strong>'), construct_timestamp() );
	echo '<div class="misc-pub-section misc-pub-section-last">' . $modified_timestamp . '</div>';
}

// The next 2 functions add the column in admin post/page tables :

// Append the new column to the columns array
function last_modified_column_heading($columns) {
	$columns['last-modified'] = 'Last Modified';
	return $columns;
}
// Put the last modified date in the content area
function last_modified_column_content($column_name, $id) {
	if ($column_name == 'last-modified')
		echo construct_timestamp('wp-table');
}

// Register the column as sortable
function last_modified_column_register_sortable( $columns ) {
	$columns['last-modified'] = 'modified';
 	return $columns;
}


// Output CSS for width of new column 
function print_last_modified_css() {
?>
<style type="text/css">
	#last-modified { width: 120px; }
</style>
<?php  
}
?>