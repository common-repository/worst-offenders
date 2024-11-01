<?php
/*
Plugin Name: WO2 ObviousTextLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Litmus test for messages that contain words that are obviously not names
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

include("obvious_strings.php");

$obvious_words_array = explode(" ",$obvious_words);

if (class_exists('Litmus')) {
	add_action('wo3_prep', array('ObviousTextLitmus', 'getCount'));
	add_action('wo3_tabs', array('ObviousTextLitmus', 'tab'));
	add_action('wo3_content', array('ObviousTextLitmus', 'content'));
	add_action('wo3_add_index', array('ObviousTextLitmus', 'addIndex'));

	class ObviousTextLitmus extends Litmus {

		function addIndex() {
			self::ensureIndex("ALTER TABLE wp_comments ADD FULLTEXT INDEX content_fulltext ( comment_content );");
		}
			
		function getName() {
			return "ObviousText";
		}
	
		public static function getMatches() {
			global $obvious_words;
			return self::runCachedMatchesQuery(
				self::getName(),
				"SELECT comment_author, comment_id, MATCH (comment_content) AGAINST ('".$obvious_words."' IN BOOLEAN MODE) as score FROM wp_comments where comment_approved='spam' and MATCH (comment_content) AGAINST ('".$obvious_words."' IN BOOLEAN MODE) order by score desc;"
			);
		}

		function content() {	
			if (self::isActive() || AllLitmus::isActive()) {
				echo("<table>");
				$comments = self::prepare(self::getMatches());
				if (self::isActive()) {
					self::wo3_include_select_all($comments);
				}
				self::wo3_show_rows(1, $comments);
				echo("</table>");
			}
		}


		function prepare($comments) {
			global $obvious_words_array;
			$results = array();
			foreach($comments as $item) {
				$num=0;
				$new_name_bits = array();
				$name_bits = explode(" ",strtolower($item->comment_content));
				foreach($name_bits as $name_bit) {
					if (in_array($name_bit,$obvious_words_array)) {
						$name_bit = "<strong>$name_bit</strong>";
						$num++;
					}
					$new_name_bits[] = $name_bit;
				}
				$item->comment_content = implode(" ",$new_name_bits);
				$item->num = $num;
				$results[] = $item;
			}
			return $results;
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
