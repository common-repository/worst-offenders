<?php
/*
Plugin Name: WO2 FirstWordLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for messages that repeat the name of the poster right at the start of the message.
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

//TODO write a strip tags function so the instr position check can just look for "position 1".

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('FirstWordLitmus', 'getCount'));
	add_action('wo3_tabs', array('FirstWordLitmus', 'tab'));
	add_action('wo3_content', array('FirstWordLitmus', 'content'));
	add_action('wo3_add_index', array('FirstWordLitmus', 'addIndex'));

	class FirstWordLitmus extends Litmus {

		function addIndex() {
			self::ensureIndex("ALTER TABLE wp_comments ADD FULLTEXT INDEX author_fulltext ( comment_author );");
		}
			
		function getName() {
			return "FirstWord";
		}
	
		public static function getMatches() {
			global $obvious_words;
			return self::runCachedMatchesQuery(
				self::getName(),
				"SELECT comment_author, comment_content, comment_id FROM wp_comments where comment_approved='spam' and  INSTR(comment_content, comment_author)>0 and  INSTR(comment_content, comment_author)<10 order by comment_author"
			);
		}

		function content() {	
			if (self::isActive() || AllLitmus::isActive()) {
				echo("<table>");
				$comments = self::getMatches();
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