<?php
/*
Plugin Name: WO2 NameLengthLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for messages that contain multiple links
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('NameLengthLitmus', 'getCount'));
	add_action('wo3_tabs', array('NameLengthLitmus', 'tab'));
	add_action('wo3_content', array('NameLengthLitmus', 'content'));

	class NameLengthLitmus extends Litmus {

		function getName() {
			return "Name Length";
		}
	
		public static function getMatches() {
			global $keys;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			return self::runCachedMatchesQuery(
				self::getName(),
				"SELECT comment_author, comment_id FROM wp_comments where comment_approved='spam' and comment_author like '% % % % %';"
			);
		}

		function content() {	
			if (self::isActive() || AllLitmus::isActive()) {
				$comments = self::getMatches();
				echo("<table>");

				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}

				self::wo3_show_rows(1, $comments);
				echo("</table>");
			}
		}


		//============================================
		// common class methods for litmus tests  
		// (there's got to be a better way to do this!
		//============================================
		
		function getCount() {
			global $wo3_title;
			self::getMatches();
			return wp_cache_get(self::getName()."_count", $wo3_title);
		}
		
		public static function tab() {	
			parent::tab(self::getName(), self::getCount(), self::isActive());
		}
		
		function isActive() {
			return ($_GET['tab'] == self::getName() ) && parent::isActive();
		}


	}
}
?>