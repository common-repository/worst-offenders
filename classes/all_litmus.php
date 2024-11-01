<?php
/*
Plugin Name: WO3 AllLitmus
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Combine All Litmus tests
Author: Rich Boakes
Version: 1
Author URI: http://boakes.org/
*/

// TODO w03_total_count shoudl be an array of entries that cna then be counted for an accurate score.

if (class_exists('Litmus')) {

	add_action('wo3_tabs', array('AllLitmus', 'tab'));
	add_action('wo3_content', array('AllLitmus', 'content'));

	class AllLitmus extends Litmus {

		function getName() {
			return "All";
		}

		function getCount() {
			return 0;
		}

		public static function getMatches() {
		}
		
		public static function tab() {	
			global $wo3_title, $keys;

			parent::tab(
				self::getName(),
				wp_cache_get("current_count", $wo3_title),
				self::isActive()
			);
		}

		function isActive() {
			return ($_GET['tab'] == self::getName() ) || (sizeof($_GET['tab']) == 0) && parent::isActive();
		}

		function content() {	
			global $wpdb, $keys;
			$counter = get_option( $keys['counter'] );
			
			if (self::isActive()) {
				echo "<p>Separating	spam from	ham	can	be difficult when	there is so	much spam.</p>";
				echo "<p>To	make things	easier,	Worst Offenders identifies comments from the most prolific spammers so you can discard them with confidence.</p>";

				if ($counter >0) {
					$akismet_total = get_option( 'akismet_spam_count' );
					$counter_offset = get_option( $keys['counter_offset'] );
					$denominator = $akismet_total - $counter_offset;
					if ($denominator == 0) {
						$average = "0";
					} else {
						$average = $counter / $denominator;
						$average = number_format($average*100, 0);
					}

					$sum = "(AkToday $akismet_total - AkDayOne $counter_offset) = $denominator spams deleted and $counter were deleted en masse by WorstOffenders.";
					echo "<p>Worst Offenders has removed $counter messages (that's <abbr title='$sum'>$average%</abbr>).  Knockout!</p>";
				}
			}
		}

	}
}
?>