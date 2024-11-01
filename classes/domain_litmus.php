<?php
/*
Litmus Name: WO2 DomainLitmus
Litmus URI: http://boakes.org/worst-offenders-plugin
Litmus Description: Litmus test for Domains that regularly spam
Litmus Version: 1
Litmus Author: Rich Boakes
Litmus Author URI: http://boakes.org/
*/

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('DomainLitmus', 'getCount'));
	add_action('wo3_tabs', array('DomainLitmus', 'tab'));
	add_action('wo3_content', array('DomainLitmus', 'content'));
	add_action('wo3_add_index', array('DomainLitmus', 'addIndex'));

	class DomainLitmus extends Litmus {

		function getName() {
			return "Domain";
		}

		public static function getMatches() {
			global $keys, $wpdb;
			$lower_limit = get_option( $keys['ui_vis'] ); 
			$wpdb->get_results("SET SESSION group_concat_max_len = 8192");
			return self::runCachedMatchesQuery(
				self::getName(),
				"select count(*) as num, address(comment_author_url) as comment_author_url_simplified, group_concat(comment_id separator ',') as comment_id_list from wp_comments where comment_approved='spam' and comment_author_url != '' group by comment_author_url_simplified having num >= $lower_limit order by num desc;"
			);
		}

		function content() {	
			if (DomainLitmus::isActive() || AllLitmus::isActive() ) {
				echo("<table>");
				$comments = DomainLitmus::getMatches();
				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}
				foreach($comments as $comment) {
					self::wo3_show_row($comment->num, "comments from ".$comment->comment_author_url_simplified, $comment->comment_id_list);
				}
				echo("</table>");
			}
		}
		
		function addIndex() {
			global $wpdb;
			self::ensureIndex("create function address(url varchar(255)) RETURNS varchar(255) CONTAINS SQL DETERMINISTIC RETURN SUBSTRING_INDEX( TRIM( LEADING 'http://' FROM TRIM(url) ),'?',1);");
			self::ensureIndex("alter table wp_comments ADD INDEX url_spotter (comment_author_url);");
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