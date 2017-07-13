<?php
	
	$score_id = $_POST['score_id'];
	$date = $_POST['MyDate'];
	$tee = sanitize_text_field(stripslashes($_POST['tee']));
	$holes = $_POST['holes'];
	$gross_score = $_POST['gross'];
	$course = sanitize_text_field(stripslashes($_POST['course']));
	$handicap = $_POST['handicap'];
	$comments = sanitize_text_field(stripslashes($_POST['comments']));
	$user_id = $_POST['user_id'];
	$username = $_POST['user'];
	$net_score = $_POST['net'];
	$formType = $_POST['formType'];
	
	// delete record
	if($formType == 'deleteRecord'){
		
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";
	$wpdb->delete( $table_name, array( 'id' => $score_id ), array( '%d' ) );

	
	echo '<div class="golfScoresAlert">Round deleted.</div>';
	unset($gtask);
	} // end if round deleted.

	// insert new scores
	
	if($formType == 'newRecord'){
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";
	$wpdb->insert( 
		$table_name, 
			array( 
				'date' => $date, 
				'course' => $course,
				'tee' => $tee,
				'holes' => $holes,
				'gross_score' => $gross_score,
				'handicap' => $handicap,
				'net_score' => $net_score,
				'user_id' => $user_id,
				'comments' => $comments
			
		)
	);
	
	echo '<div class="golfScoresAlert">Score of ' . $gross_score . ' at ' . $course . ' recorded.</div>';
	unset($gtask);
	} // end if new record
	
	
	
	if($formType == 'updateRecord'){
	
	global $wpdb;
	$table_name = $wpdb->prefix . "golfScores";
	$wpdb->update( 
	$table_name, 
	array( 
			'date' => $date, 
			'course' => $course,
			'tee' => $tee,
			'holes' => $holes,
			'gross_score' => $gross_score,
			'handicap' => $handicap,
			'net_score' => $net_score,
			'user_id' => $user_id,
			'comments' => $comments
		
	), 
	array( 'id' => $score_id ), 
	array( 
		'%s',	// date
		'%s',	// course
		'%s',	// tee
		'%d',	// holes
		'%s',	// gross_score
		'%s',	// handicap
		'%s',	// net_score
		'%d',	// user_id
		'%s'	// comments
	), 
	array( '%d' ) 
);


	
	echo '<div class="golfScoresAlert">Round at ' . $course . ' updated!</div>';
	unset($gtask);

	} // end if update record
	
?>