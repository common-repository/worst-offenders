<?php
/*
Plugin Name: WO2 MultiLinkLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for messages that contain multiple links
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('MultiLinkLitmus', 'getCount'));
	add_action('wo3_tabs', array('MultiLinkLitmus', 'tab'));
	add_action('wo3_content', array('MultiLinkLitmus', 'content'));
	add_action('wo3_add_index', array('MultiLinkLitmus', 'addIndex'));

	class MultiLinkLitmus extends Litmus {

		function getName() {
			return "MultiLink";
		}

		public static function getMatches() {
			global $keys, $wpdb;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			$wpdb->get_results("SET SESSION group_concat_max_len = 8192");
			return self::runCachedMatchesQuery(
				self::getName(),
				"SELECT wordcount2(comment_content, 'http://') as num, group_concat(comment_id separator ',') as comment_id_list FROM wp_comments where comment_approved='spam' group by num having num >= $lower_limit order by num desc;");
		}
		
		function content() {	
			if (self::isActive() || AllLitmus::isActive()) {
				echo("<table>");
				$comments = MultiLinkLitmus::getMatches();
				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}
				foreach($comments as $comment) {
					self::wo3_show_row($comment->num, "links found in", $comment->comment_id_list );
				}
				echo("</table>");
			}
		}

		function addIndex() {
			self::ensureIndex("ALTER TABLE 'wp_comments' ENGINE = MyISAM ROW_FORMAT = DYNAMIC;");
			self::ensureIndex("ALTER TABLE wp_comments ADD FULLTEXT INDEX content_fulltext ( comment_content );");
			self::ensureIndex(
				"CREATE FUNCTION wordcount2 ( a text, b VARCHAR(255) )
				RETURNS INTEGER
				CONTAINS SQL DETERMINISTIC
				RETURN (CHAR_LENGTH(a)-CHAR_LENGTH(REPLACE(a, b, '')))/CHAR_LENGTH(b);
			");
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