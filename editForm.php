<?php

	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";	
	// old - sql injection warning $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $sid LIMIT 1");
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d LIMIT 1", $sid ) );	

	//echo 'holes from db is' . 	$row->holes;

if($row->holes == 18){
	show18EditForm($sid);
	//echo "18 edit holes selcted";
}

if($row->holes == 9){
	show9EditForm($sid);		
	//echo "9 edit holes selcted";
}
	
function show9EditForm($sid){
	
	$currentUser = get_current_user_id();
	$allowedUser = get_option('golfScoresUser');
	if($currentUser == $allowedUser) {

	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";	
	// old - sql injection warning $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $sid LIMIT 1");
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d LIMIT 1", $sid ) );	
	
	if($currentUser == $allowedUser) {
				?>
		<script type="text/javascript">
				function calcNet(form){
					form.net.value = form.gross.value - (form.handicap.value / 2);
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
				
				jQuery(document).ready(function() {
					jQuery('#MyDateNine').datepicker({
						dateFormat : 'yy-mm-dd'
					});
				});

			</script>

			
	  
	  
	<form name="scoreForm" id="gstask" action="" onsubmit="return validateForm(this);" method="post">
		  <table class="scoreEntry">
					<tr>
					  <td colspan="2"><div align="center">
						<h4>Edit 9-Hole Score</h4>
					  </div></td>
					</tr>
					<tr>
						<td>Date:</td>
						<td><input type="text" id="MyDate" name="MyDate" value="<?php echo $row->date; ?>" readonly/></td>	
					</tr>
					<tr>
						<td><label for="course">Course:</label></td>
						<td><input name="course" type="text" size="30" class="inputbox" value="<?php echo stripslashes($row->course); ?>"/></td>
					</tr>
					<tr>
						<td><label for="tee">Tees:</label></td>
						<td><input name="tee" type="text" size="30" class="inputbox" value="<?php echo stripslashes($row->tee); ?>"/></td>
					</tr>
					<tr>
						<td><label for="gross">Gross Score:</label></td>
						<td><input name="gross" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="<?php echo $row->gross_score; ?>" size="5" maxlength="3" /></td>
					</tr>
					<tr>
						<td><label for="handicap">Handicap:</label></td>
						<td><input name="handicap" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="<?php echo $row->handicap; ?>" size="5" maxlength="5" /></td>
					</tr>
					<tr>
						<td><label for="net">Net Score:</label></td>
						<td><input name="net" type="text" class="inputbox" onfocus="this.form.net.blur()" value="<?php echo $row->net_score; ?>" size="5" maxlength="3" /></td>
					</tr>
					<tr>
						<td colspan="2"><label for="comments">Comments:</label></td>
                  </tr>      
					<tr>
                    	<td colspan="2"><textarea name="comments" class="textarea"><?php echo stripslashes($row->comments)
; ?></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="submit" value="Submit" class="golfScoresSubmit" />
	              	
              <input type="hidden" name="gstask" value="submitForm">
				<input type="hidden" name="user_id" value="<?php echo $row->user_id; ?> " />
				<input type="hidden" name="score_id" value="<?php echo $sid; ?>" />
				<input type="hidden" name="formType" value="updateRecord" />
  				<input type="hidden" name="holes" value="9" />
            </form>
            <form name="cancelEdit" id="cancelEdit" action=""  method="post">
				<input action="action" type="button" class="golfScoresCancel" value="Cancel" onclick="history.go(-1);" />
            </form>	
            <form name="deleteScore" id="deleteRecord" action=""  method="post">
               <input type="hidden" name="score_id" value="<?php echo $sid; ?>" />
               <input type="hidden" name="gstask" value="submitForm">
               <input type="hidden" name="formType" value="deleteRecord" />
               <input type="submit" name="delete" value="Delete" class="golfScoresDelete" />      
            </form>		
            </td>
        </tr>
    </table>
		<?php	 
		}
	} // end authorized user
		
} // end show9form

function show18EditForm($sid){
	
	$currentUser = get_current_user_id();
	//echo "Current id is " . $currentUser;
	
	$allowedUser = get_option('golfScoresUser');
	//echo " allowed user is " . $allowedUser;
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";	
	// old - sql injection warning $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $sid LIMIT 1");
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d LIMIT 1", $sid ) );	
	
	
	if($currentUser == $allowedUser) {
				?>
		<script type="text/javascript">
				function calcNet(form){
					form.net.value = form.gross.value - form.handicap.value;
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
				
				jQuery(document).ready(function() {
					jQuery('#MyDateNine').datepicker({
						dateFormat : 'yy-mm-dd'
					});
				});

			</script>

			
	  
	  
	<form name="scoreForm" id="gstask" action="" onsubmit="return validateForm(this);" method="post">
		  <table class="scoreEntry">
					<tr>
					  <td colspan="2"><div align="center">
						<h4>Edit 18-Hole Score</h4>
					  </div></td>
					</tr>
					<tr>
						<td>Date:</td>
						<td><input type="text" id="MyDate" name="MyDate" value="<?php echo $row->date; ?>" readonly/></td>	
					</tr>
					<tr>
						<td><label for="course">Course:</label></td>
						<td><input name="course" type="text" size="30" class="inputbox" value="<?php echo stripslashes($row->course); ?>"/></td>
					</tr>
					<tr>
						<td><label for="tee">Tee:</label></td>
						<td><input name="tee" type="text" size="30" class="inputbox" value="<?php echo stripslashes($row->tee); ?>"/></td>
					</tr>
					<tr>
						<td><label for="gross">Gross Score:</label></td>
						<td><input name="gross" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="<?php echo $row->gross_score; ?>" size="5" maxlength="3" /></td>
					</tr>
					<tr>
						<td><label for="handicap">Handicap:</label></td>
						<td><input name="handicap" type="text" class="inputbox" onkeyup="calcNet(this.form)" value="<?php echo $row->handicap; ?>" size="5" maxlength="5" /></td>
					</tr>
					<tr>
						<td><label for="net">Net Score:</label></td>
						<td><input name="net" type="text" class="inputbox" onfocus="this.form.net.blur()" value="<?php echo $row->net_score; ?>" size="5" maxlength="3" /></td>
					</tr>
					<tr>
						<td colspan="2"><label for="comments">Comments:</label></td>
                  </tr>      
					<tr>
                    	<td colspan="2"><textarea name="comments" class="textarea"><?php echo stripslashes($row->comments)
; ?></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="submit" value="Submit" class="golfScoresSubmit" />
	              	
              <input type="hidden" name="gstask" value="submitForm">
				<input type="hidden" name="user_id" value="<?php echo $row->user_id; ?> " />
				<input type="hidden" name="score_id" value="<?php echo $sid; ?> " />
				<input type="hidden" name="formType" value="updateRecord" />
				<input type="hidden" name="holes" value="18" />
			</form>
           <form name="cancelEdit" id="cancelEdit" action=""  method="post">
				<input action="action" type="button" class="golfScoresCancel" value="Cancel" onclick="history.go(-1);" />
           </form>	
            <form name="deleteScore" id="deleteRecord" action=""  method="post">
               <input type="hidden" name="score_id" value="<?php echo $sid; ?>" />
               <input type="hidden" name="gstask" value="submitForm">
               <input type="hidden" name="formType" value="deleteRecord" />
               <input type="submit" name="delete" value="Delete" class="golfScoresDelete" />      
            </form>
			</td>
		</tr>
  </table>
			<?php	 
		}
		
} // end show18form

?>