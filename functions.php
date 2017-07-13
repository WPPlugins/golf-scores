<?php

function getUserName($user_id){
	
	global $wpdb;
	$table_name = $wpdb->prefix . "users";	
	// old sql injection warning $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $user_id LIMIT 1");
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d LIMIT 1", $user_id) );	
		return $row->display_name;

}

function golfmenu(){
  
	$currentUser = get_current_user_id();	
	$allowedUser = get_option('golfScoresUser');
		if ($currentUser == $allowedUser){
		?>
			<div id="golfScoresMenu"> <a href="?gstask=showForm&holes=9">Enter 9</a> <a href="?gstask=showForm&holes=18">Enter 18</a> </div>
		<?php 
		}
}
//  end function golfMenu



function getUser ($user_id) {

		$user_info = get_userdata($user_id);
		$username = $user_info->user_login;
		return $username;
}



//  function to show the scores on wordpress front end widget.
function widgetScores() {
	
	$allowedUser = get_option('golfScoresUser');
	$showNet = get_option('golfScoresShowNet');	
	$showTee = get_option('golfScoresShowTee');	
	$allowedUser = get_option('golfScoresUser');
	$currentUser = get_current_user_id();	
	$showHoles = get_option('golfScoresShowHoles');	
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";	

	// find out how many rows are in the table 
	// old sql injection warning $sql = "SELECT count(*) FROM $table_name WHERE user_id = $allowedUser";
	$sql = ( $wpdb->prepare( "SELECT count(*) FROM $table_name WHERE user_id = %d ", $allowedUser ) );	
	$result = mysql_query($sql) or trigger_error("SQL", E_USER_ERROR);
	$r = mysql_fetch_row($result);
	$numrows = $r[0];
	
	// number of rows to show per page
	$rowsperpage = get_option('golfScoresWidgetList');
	// find out total pages
	$totalpages = ceil($numrows / $rowsperpage);
	
	
	if ( is_home() ) {
		$current_url = 'index.php';
		} else {
		$current_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}

	
	// get the current page or set a default
	if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
	   // cast var as int
	   $currentpage = (int) $_GET['currentpage'];
	} else {
	   // default page num
	   $currentpage = 1;
	} // end if
	
	// if current page is greater than total pages...
	if ($currentpage > $totalpages) {
	   // set current page to last page
	   $currentpage = $totalpages;
	} // end if
	// if current page is less than first page...
	if ($currentpage < 1) {
	   // set current page to first page
	   $currentpage = 1;
	} // end if
	
	// the offset of the list, based on current page 
	$offset = ($currentpage - 1) * $rowsperpage;
	
	// get the info from the db 
	// old query sql injection warning $sql = "SELECT user_id, id, holes, course, date, gross_score, net_score, course, tee FROM $table_name WHERE user_id = $allowedUser ORDER BY date DESC LIMIT $offset, $rowsperpage";
	$sql = ( $wpdb->prepare( "SELECT user_id, id, holes, course, date, gross_score, net_score, course, tee FROM $table_name WHERE user_id = %d ORDER BY date DESC LIMIT %d, %d", $allowedUser, $offset, $rowsperpage ) );	
	
	$result = mysql_query($sql) or trigger_error("SQL", E_USER_ERROR);

		?>
<table class="tableGolfScores">
  <tr>
    <th scope="col">Date</th>
    <th scope="col">Course
      <?php if($showTee == 1){echo '/Tee'; } ?></th>
    <?php if($showHoles == 1){echo '<th scope="col">Holes</th>'; } ?>
    <th scope="col">Score
      <?php if($showNet == 1){echo '/Net'; } ?></th>
  </tr>
  <?php
						  
	// while there are rows to be fetched...
	while ($row = mysql_fetch_assoc($result)) {
		//$holes = $row['holes'];
		//$sid = $row['id'];
		//echo 'tee is ' . $row['tee'];
	   // echo data
	   
	   $date_long = $row['date'];
	   list($month,$day,$year) = preg_split('/\D+/', $row['date']);
	   $date = array (
	   					'year' => $year,
						'month' => $month,
						'day' => $day
						
						);
	   	 ?>
  <tr>
    <td class="golfScoresDate"><?php echo $date['month']. '-' . $date['day'] . '-' . $date['year']; ?></td>
    <td class="golfScoresCourse"><?php if ($currentUser == $allowedUser){ ?>
      <a title="Edit Score" href="?gstask=edit&sid=<?php echo $row['id']; ?>"> <?php echo stripslashes($row['course']); ?> </a>
      <?php } ?>
      <?php if ($currentUser != $allowedUser){ ?>
      <?php echo stripslashes($row['course']); ?>
      <?php } ?>
      <?php if($showTee == 1){echo '<br />' . stripslashes($row['tee']) . ' tee';} ?></td>
      <?php if($showHoles == 1){echo '<td class="golfScoresHoles">' . $row['holes'] . '</td>'; } ?>
    <td class="golfScoresScore"><?php echo $row['gross_score']; ?>
      <?php if($showNet == 1){echo ' (' . $row['net_score'] . ' net)';} ?></td>
  </tr>
  <?php
		} // end while
	?>
</table>
<?php
	
	/******  build the pagination links ******/
	// range of num links to show
	$range = 3;
	?>
<div id="golfScoresPaging">
  <?php
	// if not on page 1, don't show back links
	if ($currentpage > 1) {
	   // show << link to go back to page 1
	   echo " <a href='" . $current_url . "?currentpage=1'><<</a> ";
	   // get previous page num
	   $prevpage = $currentpage - 1;
	   // show < link to go back to 1 page
	   echo " <a href='" . $current_url . "?currentpage=$prevpage'><</a> ";
	} // end if 
	
	// loop to show links to range of pages around current page
	for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
	   // if it's a valid page number...
	   if (($x > 0) && ($x <= $totalpages)) {
		  // if we're on current page...
		  if ($x == $currentpage) {
			 // 'highlight' it but don't make a link
			 echo " [<b>$x</b>] ";
		  // if not current page...
		  } else {
			 // make it a link
			 echo " <a href='" . $current_url . "?currentpage=$x'>$x</a> ";
		  } // end else
	   } // end if 
	} // end for
					 
	// if not on last page, show forward and last page links        
	if ($currentpage != $totalpages) {
	   // get next page
	   $nextpage = $currentpage + 1;
		// echo forward link for next page 
	   echo " <a href='" . $current_url . "?currentpage=$nextpage'>></a> ";
	   // echo forward link for lastpage
	   echo " <a href='" . $current_url . "?currentpage=$totalpages'>>></a> ";
	} // end if
	/****** end build pagination links ******/
	?>
</div>
<?php
}





function getUrl() {
	  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	  $url .= $_SERVER["REQUEST_URI"];
	  return $url;
}





function showForm() {
	
	$holes = $_GET['holes'];
	$currentUser = get_current_user_id();
	//echo "Current id is " . $currentUser;
	
	$allowedUser = get_option('golfScoresUser');
	//echo " allowed user is " . $allowedUser;
	
	//echo " holes is " . $holes;
	
	if($currentUser == $allowedUser) {
	
//    wp_enqueue_style( 'golfScores', plugins_url('assets/styles/golfscores.css', __FILE__) );
	
				?>
<script type="text/javascript">
				function calcNet(form){
					form.net.value = form.gross.value - (form.handicap.value <?php if($holes == 9){echo ' / 2';} ?>);
				}
				
				function validateForm(form){
					var errMsg = '';
					
					if(form.course.value == ''){
						errMsg = errMsg + "Course is Required" + "\n";
					}
					if(form.tee.value == ''){
						errMsg = errMsg + "Tee Played is Required" + "\n";
					}
					
					if( errMsg ){
						alert( errMsg );
						return false;
					}
					
					return true;
				}
			</script> 
<script type="text/javascript">

				jQuery(document).ready(function() {
					jQuery('#MyDate').datepicker({
						dateFormat : 'yy-mm-dd'
					});
				});

			</script>
<form name="scoreForm" id="gstask" action="" onsubmit="return validateForm(this);" method="post">
  <table class="scoreEntry">
    <tr>
      <td colspan="2"><div align="center">
          <h4>Enter <?php echo $holes; ?>-Hole Score</h4>
        </div></td>
    </tr>
    <tr>
      <td>Date:</td>
      <td><input type="text" id="MyDate" name="MyDate" value="" readonly/></td>
    </tr>
    <tr>
      <td><label for="course">Course:</label></td>
      <td><input name="course" type="text" size="25" class="inputbox" /></td>
    </tr>
    <tr>
      <td><label for="tee">Tee:</label></td>
      <td><input name="tee" type="text" size="25" class="inputbox" /></td>
    </tr>
    <tr>
      <td><label for="gross">Gross Score:</label></td>
      <td><input name="gross" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="0" size="5" maxlength="3" /></td>
    </tr>
    <tr>
      <td><label for="handicap">Handicap:</label></td>
      <td><input name="handicap" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="0" size="5" maxlength="3" /></td>
    </tr>
    <tr>
      <td><label for="net">Net Score:</label></td>
      <td><input name="net" type="text" class="inputbox" onfocus="this.form.net.blur()" value="0" size="5" maxlength="3" /></td>
    </tr>
    <tr>
      <td colspan="2"><label for="comments">Comments:</label></td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="comments" class="textarea"></textarea></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" name="submit" value="Submit" class="button" /></td>
    </tr>
  </table>
  <input type="hidden" name="gstask" value="submitForm">
  <input type="hidden" name="user_id" value="<?php echo $currentUser; ?> " />
  <input type="hidden" name="formType" value="newRecord" />
  <input type="hidden" name="holes" value="<?php echo $holes; ?>" />
</form>
<?php	 
			}
}

	
function golfScoresPage(){		
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";	
	$rows_per_page = esc_attr(get_option('golfScoresPageList'));
	$current = (intval(get_query_var('paged'))) ? intval(get_query_var('paged')) : 1;
	 
	$rows = $wpdb->get_results("SELECT * FROM $table_name");
	 
	global $wp_rewrite;
	 
	$pagination_args = array(
		 'base' => @add_query_arg('paged','%#%'),
		 'format' => '',
		 'total' => ceil(sizeof($rows)/$rows_per_page),
		 'current' => $current,
		 'show_all' => false,
		 'type' => 'plain',
	);
	 
	if( $wp_rewrite->using_permalinks() )
	 $pagination_args['base'] = user_trailingslashit( trailingslashit( remove_query_arg('s',get_pagenum_link(1) ) ) . 'page/%#%/', 'paged');
	 
	if( !empty($wp_query->query_vars['s']) )
	 $pagination_args['add_args'] = array('s'=>get_query_var('s'));
	 
	 
	$start = ($current - 1) * $rows_per_page;
	$end = $start + $rows_per_page;
	$end = (sizeof($rows) < $end) ? sizeof($rows) : $end;
	 
    for ($i=$start;$i < $end ;++$i ) {
	 		$row = $rows[$i];
		?>
<div class="golfScoresResult well well-small">
  <div class="golfScores_date">Date: <?php echo $row->date; ?></div>
  <div class="golfScores_course">Course: <?php echo $row->course; ?></div>
  <div class="golfScores_tee">Tee: <?php echo $row->tee; ?></div>
  <div class="golfScores_gross_score">Gross: <?php echo $row->gross_score; ?></div>
  <div class="golfScores_handicap">Handicap: <?php echo $row->handicap; ?></div>
  <div class="golfScores_net_score">Net: <?php echo $row->net_score; ?></div>
  <div class="golfScores_comments">Comments: <?php echo $row->comments; ?></div>
</div>
<?php
    }

echo paginate_links($pagination_args);

}// end function golfScoresPage 
	// bring in jquery for date picker
	
add_action('wp_enqueue_scripts', 'load_my_scripts');

function load_my_scripts(){
	//wp_enqueue_script( 'jquery-ui-datepicker' );    
	//wp_enqueue_script( 'jquery-ui-dialog' );    
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
}
function load_all_jquery() {
    wp_enqueue_script("jquery");
    $jquery_ui = array(
        "jquery-ui-core",            //UI Core - do not remove this one
        "jquery-ui-widget",
        "jquery-ui-mouse",
        "jquery-ui-accordion",
        "jquery-ui-autocomplete",
        "jquery-ui-slider",
        "jquery-ui-tabs",
        "jquery-ui-sortable",    
        "jquery-ui-draggable",
        "jquery-ui-droppable",
        "jquery-ui-selectable",
        "jquery-ui-position",
        "jquery-ui-datepicker",
        "jquery-ui-resizable",
        "jquery-ui-dialog",
        "jquery-ui-button"
    );
    foreach($jquery_ui as $script){
        wp_enqueue_script($script);
    }
}
add_action('wp_enqueue_scripts', 'load_all_jquery');

function my_add_frontend_scripts() {
    if( ! is_admin() ) {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-core');
    }
}
add_action('init', 'my_add_frontend_scripts');
?>