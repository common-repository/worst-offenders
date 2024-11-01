<?php

add_action('init', 'wo3_init');
add_action('admin_menu', 'wo3_list_page');
add_action('admin_menu', 'wo3_config_page');

function wo3_init() {
	wo3_ensureDefaults();
	do_action('wo3_litmus_init');
}

function wo3_ensureDefaults() {
	global $keys;

	$n = "worst_offenders_";
	$keys['ui_vis'] = $n."ui_visibility_threshold";
	$keys['ui_preselect'] = $n."ui_preselect_threshold";
	$keys['ui_list_size'] = $n."ui_size_threshold";
	$keys['user_ban_list'] = $n."current_user_ban";
	$keys['user_ban_list_max_size'] = $n."user_ban_list_max_size";
	$keys['user_ban_list_size'] = $n."current_user_ban_count";
	$keys['debug'] = $n."debug";
	$keys['domain_cache'] = $n."domain_cache";
	$keys['counter'] = $n."counter";
	$keys['counter_offset'] = $n."counter_offset";

	$ui_vis = get_option( $keys['ui_vis'] );
	$ui_preselect = get_option( $keys['ui_preselect'] );
	$ui_list_size = get_option( $keys['ui_list_size'] );
	$ban_list = get_option( $keys['ban_list'] );
	$user_ban_list = get_option( $keys['user_ban_list'] );
	$ban_list_size = get_option( $keys['ban_list_size'] );
	$user_ban_list_size = get_option( $keys['user_ban_list_size'] );
	$user_ban_list_max_size = get_option( $keys['user_ban_list_max_size'] );
	$counter = get_option( $keys['counter'] );
	$counter_offset = get_option( $keys['$counter_offset'] );

	if ($ui_vis=="") update_option( $keys['ui_vis'], 2);
	if ($ui_preselect=="") update_option( $keys['ui_preselect'], 4);
	if ($ui_list_size=="") update_option($keys['ui_list_size'], 20);
	// if (!is_array($ban_list)) wo3_reset_user_ban( );
	if ($user_ban_list_max_size=="") update_option($keys['user_ban_list_max_size'], 800);
	if ($counter=="") update_option( $keys['counter'], 0);
	
	// if there's no offset, then set it to the current akismet spam count
	if ($counter_offset=="") {
		$count = get_option( 'akismet_spam_count' );
		$count = ($count == "" ? 0 : $count);
		update_option( $keys['counter_offset'], $count);
	}
	
}

//add_action('akismet_tabs', 'wo3_akismet_tab');

function wo3_akismet_tab() {	
	global $wo3_title, $keys, $submenu;
	$active = $_GET['ctype'] === "worstoffenders";
	$count = get_current_count();
	$extra = ($active ? ' class="active"' : '');

	// draw the tab always
	if ( isset(     $submenu['edit-comments.php']   )       )
		echo "<li $extra><a href='edit-comments.php?page=akismet-admin&amp;ctype=worstoffenders'>Worst ($count)</a></li>";
	elseif ( function_exists('add_management_page') )
		echo "<li $extra><a href='tools.php?page=akismet-admin&amp;ctype=worstoffenders'>Worst ($count)</a></li>";
	// but draw the content only when selected
	if ($active) {
		?>
		<ul class="commentlist" style="list-style:none;margin:0;padding:0;">
			<li>
				<p>Use Worst Offenders to remove <?php echo $count; ?> of spam comments immediately.</p>
				<?php
				if ( isset(     $submenu['edit-comments.php']   )       )
					echo "<p><a href='edit-comments.php?page=$wo3_title"; ?>'>Worst Offenders</a> has so far removed <?php	_e(get_option( $keys['counter'] )); ?> messages.</p>";
				elseif ( function_exists('add_management_page') )
					echo "<p><a href='tools.php?page=$wo3_title"; ?>'>Worst Offenders</a> has so far removed <?php	_e(get_option( $keys['counter'] )); ?> messages.</p>";
				?>
			</li>
		</ul>
		<?php
	}
}

function wo3_config_page() {
	global $wpdb,	$wo3_title;
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php',	__($wo3_title.'	Config'),	__($wo3_title.'	Config'),	1, __($wo3_title), 'wo3_conf');
}

function wo3_conf()	{
	if ( isset($_POST['submit']) ) {
		echo '<div class="wrap">';
		do_action( "wo3_add_index" );
		echo '<p>OK</p></div>';
	}
?><title></title>


<div class="wrap">
<h2><?php	_e($wo3_title.'	Configuration'); ?></h2>
<form	action=""	method="post"	id="wo-conf" >
<h3><label for="key"><?php _e('Indexes'); ?></label></h3>
<!-- <p><input	id="key" name="key"	type="text"	size="15"	maxlength="12" value="<?php	echo get_option('wo-thing'); ?>"/> </p> -->
<p><input class="button" type="submit" name="submit" value="<?php	_e('Add / Update Indexes &raquo;');	?>"	/></p>
</form>


</div>
<?php
}




function wo3_list_page() {
	global $wpdb,	$wo3_title,	$submenu;
	$count = get_current_count();

	wp_enqueue_script('jquery');

	wo3_check_user_input();
	
	if ( isset(	$submenu['edit-comments.php']	)	)
		add_submenu_page('edit-comments.php',	__($wo3_title),	__($wo3_title . "(" . $count . ")"),	1, __($wo3_title), 'wo3_list'	);
	elseif ( function_exists('add_management_page')	)
		add_management_page(__($wo3_title),	__($wo3_title),	1, __($wo3_title), 'wo3_list');
}

function css() { ?>
	<style type="text/css">
	.wo3_tabs	{
		list-style:	none;
		margin:	0;
		padding: 0;
		clear: both;
		border-bottom: 1px solid #ccc;
		height:	31px;
		margin-bottom: 20px;
		background:	#ddd;
		border-top:	1px	solid	#bdbdbd;
	}
	.wo3_tabs	li {
		float: left;
		margin:	5px	0	0	20px;
	}
	.wo3_tabs	a	{
		display: block;
		padding: 4px .5em	3px;
		border-bottom: none;
		color: #036;
	}
	.wo3_tabs	.active	a	{
		background:	#fff;
		border:	1px	solid	#ccc;
		border-bottom: none;
		color: #000;
		font-weight: bold;
		padding-bottom:	4px;
	}

	.wo3_count {
		color: #c30;
	}

	span.spam{
    position:relative;
    z-index:24;
    background-color:#fff;
    color:#000;
    text-decoration:none;
    }

	span.spam:hover{
		z-index:25;
		background-color:#EEE;
		}

	span.spam span{
		display: none;
		}

	span.spam:hover span{
    display:block;
    position:absolute;
    top:1em;
    left:1em;
    width:20em;
    border:1px solid #666;
    background-color:#FFF;
    color:#333;
    margin:0em;
    padding:1ex 2ex;
    }

	</style>

	<script type="text/javascript">
	//<![CDATA[
		function wibble(id) {
		  var elem = document.getElementById(id);
			while ( elem.hasChildNodes() ) {
					elem.removeChild(elem.firstChild);
			}
			var para = document.createElement('p');
			var txt = document.createTextNode('wibble');
			para.appendChild(txt);
			elem.appendChild(para);
		}
		
		$j=jQuery.noConflict();

		$j(document).ready(function(){
			$('.spam').css('opacity', '0.5');
		});

		
	//]]>
</script>

<?php
}

function reset_current_count() {
	global $keys, $wo3_title;
	wp_cache_delete("current_count", $wo3_title);
	wp_cache_delete("current_ids", $wo3_title);
}

function get_current_count() {
	global $wo3_title;
	$result = wp_cache_get("current_count", $wo3_title);
	if ($result === false) {
		do_action('wo3_prep');
		$result = wp_cache_get("current_count", $wo3_title);
	}
	return $result;
}


function wo3_list()	{
	global $wpdb,	$comment,	$wo3_title, $wo3_feedback;
	css();
	?>
	<div class='wrap'>
	<h2><?php	_e($wo3_title);	?></h2>
	<?php
	if (is_array($wo3_feedback)) {
		foreach($wo3_feedback as $feedback) { 
			echo $feedback;
		}
		$wo3_feedback = array();
	}
	wo3_tabs();
	echo("</div>");
}


function do_delete($comma_separated_id_list) {
	global $wpdb, $keys;
	$info["banned"] = wo3_prepare_ban_ip_addresses($comma_separated_id_list);
	$query = "delete from wp_comments where comment_ID in ($comma_separated_id_list)";
	$result = $wpdb->query($query);
	$info["deleted"] = $result;
	return $info;
}


function wo3_prepare_ban_ip_addresses($comma_separated_id_list) {
	global $wpdb, $keys, $wo3_feedback;
	$query = "select distinct comment_author_IP from wp_comments where comment_ID in ($comma_separated_id_list)";
	$results = $wpdb->get_results($query);
	$ban=0;
	foreach($results as $result) {
		do_action("ban_ip_address", $result->comment_author_IP);
		$ban++;
	}
	return $ban;	
}


function wo3_tabs()	{
	global $wo3_title;
	?>

<ul	class="wo3_tabs">
	<?php
	do_action( "wo3_tabs" );
	?>
</ul>

<script type="text/javascript">
	function toggleCheck(me) {
		var new_value = me.checked;
		for(i=0; i<me.form.length;i++){
   	   if(me.form[i].type == 'checkbox') {
				me.form[i].checked = new_value;
			}
		}
	}
</script>

<form	method="post"	action="">
	<input type="submit" class="button delete" name="submit" value="<?php	_e('Delete selected'); ?>" />
	<?php	do_action( "wo3_content" );	?>
	<input type="hidden" name="act"	value="delete" />
	<input type="submit" class="button delete" name="submit" value="<?php	_e('Delete selected'); ?>" />
</form>
<?php
}

function wo3_check_user_input()	{
	global $keys, $wo3_feedback;
	if ($_POST['act']	== 'delete') {
		if (!empty($_POST['worst'])) {
			$deletionList	=	implode(",", $_POST['worst']);
			$info = do_delete($deletionList);
			if ($info["deleted"] === false)	{
				$wo3_feedback[] = "<div class='updated'>DB	Error	-	Failed to	delete - Boo!	 Perhaps the DB	log	can	help!</div>";
			}	else {
				$wo3_feedback[] = "<div class='updated'>Congratulations!  You just ignored <strong>".$info["deleted"]."</strong> servings of spam, from <strong>".$info["banned"]."</strong> worst offenders.</div>";
				update_option( $keys['counter'], $deleted + get_option( $keys['counter'] ) );
				reset_current_count();
			}
		} else {
			$wo3_feedback[] = "<div class='updated'>Nothing selected for deletion.</div>";
		}
	}

}


function wo3_dashboard() {
	global $keys, $wo3_title, $submenu;
	$count = get_current_count();
	if ( isset(     $submenu['edit-comments.php']   )       )
		$link = "edit-comments.php?page=$wo3_title";
	elseif ( function_exists('add_management_page') )
		$link = "tools.php?page=$wo3_title";
		
	if ($count > 0) {
		$msg = "Worst Offenders has identified <a href='$link'>$count spam messages that you can delete immediately</a>.";
	} else {
		$msg = "The bad guys got squished.  No <a href='$link'>Worst Offenders</a> in your spam queue!";
	}
	echo "<p class='right-now'>$msg</p>\n";

}

add_action('rightnow_end', 'wo3_dashboard');
