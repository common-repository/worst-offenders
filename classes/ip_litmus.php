<?php
/*
Plugin Name: WO2 IPLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for IP addresses that regularly spam
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('IPLitmus', 'getCount'));
	add_action('wo3_tabs', array('IPLitmus', 'tab'));
	add_action('wo3_content', array('IPLitmus', 'content'));
	add_action('wo3_add_index', array('IPLitmus', 'addIndex'));

	class IPLitmus extends Litmus {

		function getName() {
			return "IP";
		}
		
		public static function getMatches() {
			global $keys, $wpdb;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			$wpdb->get_results("SET SESSION group_concat_max_len = 8192");
			return self::runCachedMatchesQuery(
				self::getName(),
				"select comment_author_ip, count(comment_id) as num, group_concat(comment_ID separator ',') as comment_id_list from wp_comments where comment_approved='spam' group by comment_author_ip having num >= $lower_limit order by num desc;"			
			);
		}

		function content() {	
			if (IPLitmus::isActive() || AllLitmus::isActive()) {
				$comments = self::getMatches();
				echo("<table>");

				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}

				foreach($comments as $comment) {
					Litmus::wo3_show_row($comment->num, "messages from ".$comment->comment_author_ip, $comment->comment_id_list);
				}
				echo("</table>");
			}
		}


		function addIndex() {
			self::ensureIndex("ALTER TABLE wp_comments ADD INDEX ip_spotter ( comment_author_IP );");
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