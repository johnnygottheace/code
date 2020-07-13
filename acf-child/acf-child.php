<?php
/*
Plugin Name: Advanced Custom Fields Child
Description: Add custom functionality to ACF Plugin
Version: 5.8.7
Author: João Costa
Text Domain: acf
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function acf_get_fields_from_db(){
	global $wpdb;

	$sql = "SELECT p.ID, p.post_title, p.post_name, p.post_excerpt, pm.meta_value as rule
			FROM $wpdb->posts p 
			LEFT JOIN $wpdb->postmeta pm 
			ON ( p.ID = pm.post_id AND pm.meta_key = 'rule' ) 
			WHERE p.post_type = 'acf-field' GROUP BY p.post_title";

	$result = $wpdb->get_results($sql);

	$groups = array();

	foreach($result as $row){
		$groups[$row->ID] = array('post_title'=>$row->post_title,'ID'=>$row->ID,'post_name'=>$row->post_name,'post_excerpt'=>$row->post_excerpt);
	}

	return $groups;

}


function get_all_acf_fields_2(){
	$options = array();

	$forbidden_groups = array('Post Restrictions');
	
	$field_groups = acf_get_field_groups();
	foreach ( $field_groups as $group ) {
		if(in_array($group['title'], $forbidden_groups)) continue;
		// // DO NOT USE here: $fields = acf_get_fields($group['key']);
		// // because it causes repeater field bugs and returns "trashed" fields
		$fields = get_posts(array(
			'posts_per_page'   => -1,
			'post_type'        => 'acf-field',
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'suppress_filters' => true, // DO NOT allow WPML to modify the query
			'post_parent'      => $group['ID'],
			'post_status'      => 'any',
			'update_post_meta_cache' => false
		));
		foreach ( $fields as $field ) {
			$options[$field->post_name] = $field->post_excerpt;
		}
	}
	return $options;
}

function get_all_acf_fields(){
	$options = array();

	$fields = acf_get_fields_from_db();
	foreach ( $fields as $field ) {
		// // DO NOT USE here: $fields = acf_get_fields($group['key']);
		// // because it causes repeater field bugs and returns "trashed" fields
		// $fields = get_posts(array(
		// 	'posts_per_page'   => -1,
		// 	'post_type'        => 'acf-field',
		// 	'orderby'          => 'menu_order',
		// 	'order'            => 'ASC',
		// 	'suppress_filters' => true, // DO NOT allow WPML to modify the query
		// 	'post_parent'      => $group['ID'],
		// 	'post_status'      => 'any',
		// 	'update_post_meta_cache' => false
		// ));
		// foreach ( $fields as $field ) {
			$options[] = array('post_excerpt' => $field['post_excerpt'], 'post_title' => $field['post_title']);
		// }
	}
	return $options;
}

function my_pre_get_posts( $query ) {
	
	// do not modify queries in the admin
	if( is_admin() || !$query->is_main_query() ) {
		
		return $query;
		
	}
	
	$acf_fields = get_all_acf_fields();

	// var_dump($acf_fields);

	foreach($acf_fields as $key=>$value){

		$v = $value['post_excerpt'];

		// allow the url to alter the query
		if( isset($_GET[$v]) ) {

			if($v == "allowed_users"){
				$query->set('meta_key', $v);
				$query->set('meta_value', '"'.$_GET[$v].'"');
				$query->set('meta_compare', 'LIKE');
			}elseif($v == "relevance" || $v == "visibility"){
				$query->set('meta_key', $v);
				$query->set('meta_value', $_GET[$v]);
				$query->set('meta_compare', '=');
			}else{
				$query->set('meta_key', $v);
				$query->set('meta_value', $_GET[$v]);
				$query->set('meta_compare', 'LIKE');
			}
			
		} 

	}
	
	// return
	return $query;

}


function get_advanced_search_post(){

	$the_slug = 'search';
	$args = array(
	'name'        => $the_slug,
	'post_type'   => 'advanced_search',
	'post_status' => 'publish',
	'numberposts' => 1
	);
	$the_query = new WP_Query($args);

	return $the_query;

}

add_action('pre_get_posts', 'my_pre_get_posts');

add_action('pre_get_posts', 'posts_orderby');

function posts_orderby($query){
	
	if(!isset($_GET["orderby"])) return;
	
	if(!$query->is_main_query()) return;

	$query->set( 'post_type', array( 'post', 'news', 'todo', 'content-page' ) );
	
	$orderby = $_GET["orderby"];

	if($orderby == 'due_date'){

		$query->set('orderby', 'meta_value_num');
		$query->set('meta_key', 'due_date');
		$query->set('order', 'DESC' );

	}else{
		$query->set('orderby', $orderby);
		$query->set('order', 'DESC' );
	}
}


add_action('pre_get_posts', 'posts_filter');

function posts_filter($query){
	
	if(!isset($_GET["filter"])) return;
	
	if(!$query->is_main_query()) return;

	$query->set( 'post_type', array( 'post', 'news', 'todo', 'content-page' ) );

	add_filter( 'posts_where', function($where = ''){

		$filter = $_GET["filter"];

		$filter = explode('_', $filter);
		//is date interval
		if(count($filter) > 1){

			$week_start = $filter[0];
			$week_end = $filter[1];
			
			$where .= ' AND post_date > "'.$week_start.'" AND post_date < "'.$week_end.'"';
		}
		else{
			$filter = $filter[0];

			if($filter == "all"){

			}
			//filter is today
			else{
				$where .= ' AND post_date > "'.$filter.'"';
			}
		}

		return $where;
	});
}

