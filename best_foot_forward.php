<?php
/*
Plugin Name: Best Foot Forward
Plugin URI: http://www.dyers.org/blog/
Description: Best Foot Forward provides an easy way for you to create a featured post list using Wordpress tags like "featured", "favorite", etc.
Author: Jon Dyer
Version: 1.2
Author URI: http://www.dyers.org/blog/
*/

function widget_bestfootforward_init() {

if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) 
		return;
		
		function widget_bestfootforward($args) {
		
			// "$args is an array of strings that help widgets to conform to
			// the active theme: before_widget, before_title, after_widget,
			// and after_title are the array keys." - These are set up by the theme
			extract($args);

			// These are our own options
			$options = get_option('widget_bestfootforward');
			$bff_title = $options['title'];  // Title in sidebar for widget
			$bff_number = $options['show'];  // # of Posts we are showing
			$bff_tags=$options['tags']; //Limit the list to a particular tag.  Tags can be chained by using commas
			$bff_clean_tags= implode ("','",explode(",",$bff_tags));//cleans tags for use in SQL IN statment
			$bff_order=$options['order'];
			if (!$bff_number || $bff_number<1) $bff_number = 10;
			if (!$bff_title) $bff_title = 'Best Foot Forward';
			
		// Output
			
			global $wpdb;
			$querystr = "SELECT DISTINCT post_title, ID FROM $wpdb->posts INNER JOIN ($wpdb->term_relationships INNER JOIN( $wpdb->terms INNER JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id) ON $wpdb->term_taxonomy.term_taxonomy_id=$wpdb->term_relationships.term_taxonomy_id)ON $wpdb->posts.ID=$wpdb->term_relationships.object_id WHERE $wpdb->term_taxonomy.taxonomy='post_tag' AND $wpdb->terms.name IN ('$bff_clean_tags')  AND $wpdb->posts.post_type IN ('post','page') ORDER BY $bff_order LIMIT $bff_number";	

			
			$bff_posts = $wpdb->get_results($querystr, OBJECT);

			echo $before_widget . $before_title . $bff_title . $after_title;
			echo "<ul>";
			if (!empty($bff_posts)){
				foreach ($bff_posts as $bff_post){
					$bff_permalink = get_permalink($bff_post->ID);
					$bff_title = $bff_post->post_title;
					echo '<li><a href="'.$bff_permalink.'">'.$bff_title.'</a></li>';
				}
			}
			echo "</ul>";
			echo $after_widget;
		}


		// Settings form
	function widget_bestfootforward_control() {

		// Get options
		$options = get_option('widget_bestfootforward');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Best Foot Forward', 'show'=>10);
		
		// form posted?
		if ( $_POST['bestfootforward-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['bestfootforward-title']));
			$options['show'] = strip_tags(stripslashes($_POST['bestfootforward-show']));
			$options['tags'] = strip_tags(stripslashes($_POST['bestfootforward-tags']));
			$options['order'] = strip_tags(stripslashes($_POST['bestfootforward-order']));
			update_option('widget_bestfootforward', $options);
		}

		// Get options for form fields to show
		$bff_title = htmlspecialchars($options['title'], ENT_QUOTES);
		$bff_number = htmlspecialchars($options['show'], ENT_QUOTES);
		$bff_tags = htmlspecialchars($options['tags'], ENT_QUOTES);
		$bff_order = htmlspecialchars($options['order'], ENT_QUOTES);
		// The form fields
		echo '<p style="text-align:left;">
				<label for="bestfootforward-title">' . __('Title:') . ' 
				<input style="width: 200px;" id="bestfootforward-title" name="bestfootforward-title" type="text" value="'.$bff_title.'" />
				</label></p>';
		echo '<p style="text-align:left;">
				<label for="bestfootforward-tags">' . __('Tags (separate with commas):') . ' 
				<input style="width: 200px; id="bestfootforward-tags" name="bestfootforward-tags" type="text" value="'.$bff_tags.'" />
				</label></p>';
		echo '<p style="text-align:left;">
				<label for="bestfootforward-show">' . __('Number of Posts to Show:') . ' 
				<input style="width: 31px;" id="bestfootforward-show" name="bestfootforward-show" type="text" value="'.$bff_number.'" />
				</label></p>';
		echo '<p style="text-align:left;">
				<label for="bestfootforward-order">' . __('Sort Order:').'<br/>';
		$bff_sort_array = array('ASC'=>'post_title ASC','DSC'=>'post_title DESC','Random'=>'rand()');
		foreach($bff_sort_array as $text=>$sqltext){
			echo '<input style="width: 25px;" id="bestfootforward-order'.$text.'" name="bestfootforward-order" type="radio" value="'.$sqltext.'" ';
			if ($sqltext==$bff_order){echo 'checked';}
			echo ' />'.$text.' ';
		}		
		echo '</label></p>';
		echo '<input type="hidden" id="bestfootforward-submit" name="bestfootforward-submit" value="1" />';
	}
	
	// Register widget for use
	register_sidebar_widget(array('Best Foot Forward', 'widgets'), 'widget_bestfootforward');

	// Register settings for use, 300x500 pixel form
	register_widget_control(array('Best Foot Forward', 'widgets'), 'widget_bestfootforward_control', 200, 200);
}

// Run code and init
add_action('widgets_init', 'widget_bestfootforward_init');

?>