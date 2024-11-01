<?php

abstract class Litmus {
	
	// Override this method to name your litmus test
	// Note that the name must not contain spaces!
	abstract function getName();

	// Override this method to count the current number of items matched by your litmus test
	abstract function getCount();
	
	abstract static function getMatches() ;

	// This method should only be overridden if you want your tab to look different
	public static function tab($name, $count, $extra = false) {
		global $wo3_title, $submenu;
		$active = ($extra ? "active" : "");
		if ( isset(     $submenu['edit-comments.php']   )       )
			echo "<li class='$active'><a href='edit-comments.php?page=$wo3_title&amp;tab=$name'>$name <sup class='wo3_count'>$count</sup></a></li>";
		elseif ( function_exists('add_management_page') )
			echo "<li class='$active'><a href='tools.php?page=$wo3_title&amp;tab=$name'>$name <sup class='wo3_count'>$count</sup></a></li>";
	}

	public static function runCachedMatchesQuery($name, $q) {
		global $wpdb, $wo3_title;
		$result = wp_cache_get($name, $wo3_title);
		if($result == false) {
			$result= $wpdb->get_results($q);
			wp_cache_add($name, $result, $wo3_title);
		} 
		self::updateCount($name, $result);
		return $result;
	}

	function ensureIndex($q) {
		global $wpdb;
		$x = $wpdb->query($q);
		if ( !empty($wpdb->error) ) {
			echo ("<p>Error (".mysql_errno()." - ".mysql_error()." - ".$wpdb->error->get_error_message().") doing $q</p>");
		} else {
			echo("<p>Done: $q</p>");
		}
	}


	private static function updateCount($name, $results) {
		global $wo3_title;
		$ids = array();
		foreach($results as $result) {
			if (isset($result->comment_id_list)) {
				$ids = array_merge($ids, explode(",",$result->comment_id_list));
			}
			if (isset($result->comment_id)) {
				$ids[] = $result->comment_id;
			}
		}
		$ids = array_unique($ids);
		wp_cache_set($name."_count", sizeof($ids), $wo3_title);

		$current_ids = wp_cache_get("current_ids", $wo3_title);
		if ($current_ids == false) $current_ids = array();
		$current_ids = array_merge($current_ids, $ids);
		$current_ids = array_unique($current_ids);
		wp_cache_set("current_ids", $current_ids, $wo3_title);
		wp_cache_set("current_count", sizeof($current_ids), $wo3_title);

	}

	function isActive() {
		global $wo3_title;
		return $_GET['page']==$wo3_title;
	}


	public static function wo3_show_row($num, $reason_or_offender, $ids) {
		$preselect_threshold = 3; // should be an option
		$checked = ($num >= $preselect_threshold ? "checked" : "");
		if (AllLitmus::isActive()) {
			echo("<input style='visibility:hidden;display:none;' id='$hids' type='checkbox' name='worst[]' $checked value='$ids' />");
		} else {
			echo("<tr>");
			echo("<td><input id='$hids' type='checkbox' name='worst[]' $checked value='$ids' /></td>");
			echo("<td>$num</td>");
			echo("<td>$reason_or_offender</td><td>");
			self::link_to_comments($ids);
			echo("</td></tr>");
		}
	}


	public static function wo3_show_rows($num, $comments) {
		foreach($comments as $comment) {
			self::wo3_show_row(1, $comment->comment_author, $comment->comment_id);
		}
	}


	public static function wo3_include_select_all($comments) {
		if (sizeof($comments) > 0) {
			echo("<tr><td><input id='all' type='checkbox' onclick='toggleCheck(this)' name='' value='' /></td><td></td><td>Select all</td></tr>");
		}
	}

	function link_to_comments($comma_separated_list) {
		foreach( explode( ",", $comma_separated_list ) as $id ) {
			$id = trim($id);
			$link[] = self::link_to($id);
		}
		echo implode(", ",$link);
	}

	function link_to($id) {
		return "<span class='spam' id='c$id' >$id</span>";
	}
}

?>
