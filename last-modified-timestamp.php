<?php
/*
Plugin Name: Last Modified Timestamp
Description: This plugin will add information to the admin interface about when each post/page was last modified. No options currently available, simply activate and enjoy!
Version: 0.4
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
function construct_timestamp($context='') {
	
	$defaults = array(
		'wp-table'    => array(
			'df'  => 'Y/m/d',
			'tf'  => '',
			'sep' => '<br />'),
		'messages'    => array(
			'df'  => 'M j, Y',
			'tf'  => '',
			'sep' => ' @ '),
		'publish-box' => array(
			'df'  => 'M j, Y',
			'tf'  => '',
			'sep' => ' @ '),
	);

	$format = apply_filters( 'last_modified_timestamp_defaults', $defaults );

	$modified_timestamp = get_the_modified_date( $format[$context]['df'] ) . $format[$context]['sep'] . get_the_modified_time( $format[$context]['tf'] );

	$modified_timestamp = '<span class="last-modified-timestamp">' . $modified_timestamp . '</span>';

return $modified_timestamp;
}

// filters the admin messages at the top of the page on post.php for pages & posts to include the last modified timestamp.
function modify_messages($messages) {

	$modified_timestamp = construct_timestamp('messages');
	
	// define a pattern to only match appropriate messages
	$match = array('updated','published','saved','submitted','restored');
	// internationalize match terms
	$match = array_map('__', $match);

	$pattern = '/' . implode('|', $match) . '/';
	
	foreach ($messages as $key => &$array) {
		foreach ($array as $inner_key => &$value) {
			if (! empty($value) && preg_match($pattern , $value) ) {
				if (0 != $entry_point = strpos($value, '.') ) {
					$first_half = substr($value, 0, $entry_point+1 );
					$second_half = substr($value, strlen($first_half));
					$value = $first_half . ' ' . $modified_timestamp . '. ' . $second_half;	
				} else {
					$value = $modified_timestamp . ': ' . $value;
				}
			}
		}
	}
	return $messages;
}

// Add the Last Modified timestamp to the 'Publish' meta box in post.php
function add_modified_to_meta() {
	$modified_timestamp = vsprintf( __('Last modified on: <strong>%1$s</strong>'), construct_timestamp('publish-box') );
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

	echo '<style type="text/css">#last-modified{width:120px;}#message .last-modified-timestamp{font-weight:bold;}</style>'."\n";

}
?>