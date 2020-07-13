<?php
/*
Plugin Name: Favorites Child
Author: João Costa
Text Domain: favorites
Domain Path: /languages/
*/

function favorites_modify_query( $query ) {  

    $favorite_ids = get_user_favorites(get_current_user_id());

	if($query->is_main_query() && isset($_GET["search_in_favorites"])){  
		$query->set('post__in', $favorite_ids);
	}
}  
add_action( 'pre_get_posts', 'favorites_modify_query' );  