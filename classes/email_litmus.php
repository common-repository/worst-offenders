<?php
/*
Plugin Name: WO2 EmailLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for Emails that regularly spam
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('EmailLitmus', 'getCount'));
	add_action('wo3_tabs', array('EmailLitmus', 'tab'));
	add_action('wo3_content', array('EmailLitmus', 'content'));
	add_action('wo3_add_index', array('EmailLitmus', 'addIndex'));

	class EmailLitmus extends Litmus {

		function getName() {
			return "Email";
		}

		public static function getMatches() {
			global $keys;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			return self::runCachedMatchesQuery(
				self::getName(),
				"select count(*) as num, comment_author_email, group_concat(comment_id separator ',') as comment_id_list from wp_comments where comment_approved='spam' and comment_author_email != '' group by comment_author_email having num >= $lower_limit order by num desc;"
			);
		}

		function content() {	
			if ( self::isActive() || AllLitmus::isActive() ) {
				echo("<table>");
				$comments = EmailLitmus::getMatches();
				
				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}

				foreach($comments as $comment) {
					Litmus::wo3_show_row($comment->num, "comments from ".$comment->comment_author_email, $comment->comment_id_list);
				}
				echo("</table>");
			}
		}

		function addIndex() {
			self::ensureIndex( "ALTER TABLE wp_comments ADD INDEX email_spotter (comment_author_email);");
		}

		//============================================
		// common class methods for litmus tests:  
		// (there's got to be a better way to do this!
		// somebody please tell me what it is, because
		// include doesn't work in objects and moving
		// these to the superclass results in getName()
		// (etc) not referencing the subclass.
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