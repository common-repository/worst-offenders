<?php
/*
Plugin Name: WO2 MD5Litmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for messages that contain multiple links
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('MD5Litmus', 'getCount'));
	add_action('wo3_tabs', array('MD5Litmus', 'tab'));
	add_action('wo3_content', array('MD5Litmus', 'content'));
	add_action('wo3_add_index', array('MD5Litmus', 'addIndex'));

	class MD5Litmus extends Litmus {

		function getName() {
			return "MD5";
		}
	
		public static function getMatches() {
			global $keys;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			return self::runCachedMatchesQuery(
				self::getName(),
				"SELECT count(*) num, MD5(comment_content) as comment_content_md5, group_concat(comment_id separator ',') as comment_id_list FROM wp_comments where comment_approved='spam' group by comment_content_md5 having num >= $lower_limit order by num desc;"
			);
		}

		function content() {	
			if (self::isActive() || AllLitmus::isActive()) {
				echo("<table>");
				$comments = self::getMatches();
				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}
				foreach($comments as $comment) {
					Litmus::wo3_show_row($comment->num, "messages match MD5(".$comment->comment_content_md5.")", $comment->comment_id_list);
				}
				echo("</table>");
			} else {
				echo "\n<!-- ".self::getName()." is not active -->\n";
			}
		}

		function addIndex() {
			self::ensureIndex("ALTER TABLE 'wp_comments' ENGINE = MyISAM ROW_FORMAT = DYNAMIC;");
			self::ensureIndex("alter table wp_comments add fulltext index content_fulltext ( comment_content );");
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