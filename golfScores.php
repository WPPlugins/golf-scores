<?php
/*
Plugin Name: Golf Scores
Plugin URI: http://www.tkserver.com
Description: Golf tracker for Wordpress which tracks one selected WordPress user's golf rounds, dates, gross and net scores as well as comments on each round.  Scores can be entered/edited in the WordPress admin as well as within a widget.  Front end widget display is customizable via administrative settings.  Widget may also be displayed in a WP page or post via shortcode, but it is not recommended to show the widget and shortcoded page/post at the same time.
Author: Tony Korologos
Version: 1.0.4.1
Author URI: http://www.hookedongolfblog.com
*/

/* Check WP version */

global $wp_version;

	if ( !version_compare($wp_version, "3.0",">=") ) 
	{
		die("WP 3.0 or higher required");
	}
// end check WP version

// bring in Golf Scores functions
include_once("functions.php");

/*function my_scripts_method() {
	wp_enqueue_script(
		'newscript',
		plugins_url( 'thickbox.js' , __FILE__ ),
		array( 'scriptaculous' )
	);
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );*/



/**
 * Enqueue plugin style-file
 */
function prefix_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'prefix-style', plugins_url('assets/styles/golfscores.css', __FILE__) );
    wp_enqueue_style( 'prefix-style' );
}
/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action( 'wp_enqueue_scripts', 'prefix_add_my_stylesheet' );




// this section allows us to put the widget on a PAGE with a short code

function widget($atts) {
    
    global $wp_widget_factory;
    

    extract(shortcode_atts(array(
        'widget_name' => FALSE
    ), $atts));
    
    $widget_name = wp_specialchars($widget_name);
    
    if (!is_a($wp_widget_factory->widgets[$widget_name], 'golfScores')):
        $wp_class = 'golfScores_'.ucwords(strtolower($class));
        
        if (!is_a($wp_widget_factory->widgets[$wp_class], 'golfScores')):
            return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$class.'</strong>').'</p>';
        else:
            $class = $wp_class;
        endif;
    endif;
    
    ob_start();
    the_widget($widget_name, $instance, array('widget_id'=>'arbitrary-instance-'.$id,
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => ''
    ));
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
    
}
add_shortcode('widget','widget');

// end of page insert stuff




// create the widget

class golfScores extends WP_Widget
{
		
function golfScores()
	{
		$classname = get_option('golfScores');
		$widget_options = array(
		'classname' => $classname,
		'description' => 'Golf Scorekeeper for Wordpress' );
		
		parent::WP_Widget('golfScores','Golf Scores', $widget_options);
	}
	



function widget($args, $instance)
	{
		
		
		extract( $args, EXTR_SKIP );
		$title = ( $instance['title'] ) ? $instance['title'] : '' ;

		?>
<?php echo $before_widget; ?><?php echo $before_title . $title . $after_title ?>
<?php
				
				$gstask = $_REQUEST['gstask'];
				$sid = $_REQUEST['sid'];
				$score_id = $_REQUEST['score_id'];
				$formType = $_REQUEST['formType'];
				
				//echo 'sid is ' . $sid;
				//echo 'gstask is ' . $gstask;
				
				echo '<div id="golfScoresSubtitle">' . esc_attr(get_option('golfScoresSubtitle')) . '</div>';

		switch ($gstask){
				
				case "":
					widgetScores();
					golfmenu();
				break;
				
				case "edit":
					$currentUser = get_current_user_id();
					$allowedUser = get_option('golfScoresUser');
					if($currentUser == $allowedUser) {
						include_once('editForm.php');
					}
				break;
				
				case "submitForm":
					$currentUser = get_current_user_id();
					$allowedUser = get_option('golfScoresUser');
						if($currentUser == $allowedUser) {					
							include_once('submitForm.php');
						}
				break;
				
				case "showForm":
					// not used showForm();
				break;
				
		} // end switch gstask
				
				
}  // end function widget
	
	
	
function form($instance)
		{
			?>

<label for="<?php echo $this->get_field_id('title');?>"> Title:
  <input id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>"
                        value="<?php echo esc_attr($instance['title'])?>" />
</label>
<?php
				$defaults = array( 'class' => 'golfscores' );
				$instance = wp_parse_args( (array) $instance, $defaults ); 
            }
}


function golfScores_init()
	{
		register_widget("golfScores");
	}
	
add_action('widgets_init','golfScores_init');

// OPTIONS/SETTINGS PAGE
function golfScores_option_page()
	{
		
		
		if ($_POST['golfScores_hidden'] == 'Y' )
		{
			update_option('golfScores', $_POST['golfScores-class']);
			update_option('trackPlayer', $_POST['trackPlayer']);
			update_option('golfScoresUser', $_POST['golfScoresUser']);
			update_option('golfScoresWidgetList', $_POST['golfScoresWidgetList']);
			update_option('golfScoresAdminList', $_POST['golfScoresAdminList']);
			update_option('golfScoresShowName', $_POST['golfScoresShowName']);
			update_option('golfScoresShowNet', $_POST['golfScoresShowNet']);
			update_option('golfScoresSubtitle', $_POST['golfScoresSubtitle']);
			update_option('golfScoresShowTee', $_POST['golfScoresShowTee']);
			update_option('golfScoresShowHoles', $_POST['golfScoresShowHoles']);
		?>
<div id="message" class="updated">Golf Scores Options Updated</div>
<?php
				
		}
		?>
<div class="wrap">
  <div id="golfScoresIcon"> <?php echo '<img src="' . plugins_url( 'assets/images/golfscores.png' , __FILE__ ) . '" > ';?> </div>
  <h2>Golf Scores Options</h2>
  <?php  
            if( isset( $_GET[ 'tab' ] ) ) {  
                $active_tab = $_GET[ 'tab' ];  
            } // end if  
			 if( !isset( $_GET[ 'tab' ] ) ) {  
                $active_tab = 'settings';  
            } // end if  
        ?>
  <h2 class="nav-tab-wrapper"> <a href="?page=golfScores-plugin&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a> <a href="?page=golfScores-plugin&tab=about" class="nav-tab <?php echo $active_tab == 'about' ? 'nav-tab-active' : ''; ?>">About</a> </h2>
  <form method="post" action="">
    <?php  
          
        if( $active_tab == 'settings' ) {  
            echo golfScoresSettings(); 
        } elseif ( $active_tab == 'about' ){ 
			 echo aboutGolfScores();
		}
          
        //submit_button();  
          
    ?>
  </form>
  <?php
		 


} // end golfScoresSettings
	
function aboutGolfScores(){
	
	?>
  <div id="aboutGolfScores">
    <p>
    <h3>About Golf Scores</h3>
    </p>
    <p>Version 1.0 - November 16, 2013</p>
    <p>Written by Tony Korologos at <a href="http://www.hookedongolfblog.com">Hooked On Golf Blog</a></p>
    <p>© Copyright 2013</p>
    <p><a href="http://www.tkserver.com/index.php?option=com_content&view=article&id=65:wp-golfscores&catid=12&Itemid=171">Golf Scores for WordPress Home Page</a></p>
    <p><a href="http://www.tkserver.com/index.php?option=com_kunena&view=category&catid=34&Itemid=0">Golf Scores Support Forum</a></p>
  </div>
  <?php
		
}

function golfScoresSettings(){
	?>
  <form action="" method="post" id="golfScores-class-form">
  <table class="form-table">
    <tr>
      <th colspan="2"> <h3>Welcome to Golf Scores.  Here you can edit the functions of the widget.</h3>
      </th>
    </tr>
    <tr>
      <th scope="row"> <label title="CSS class to match your theme CSS style" for="golfScores-class">Custom CSS Class:</label>
      </th>
      <td><input type="text" id="golfScores-class" name="golfScores-class" value="<?php echo esc_attr(get_option('golfScores')); ?>" /></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Subtitle - Text which appears under widget header" for="golfScoresSubtitle">Widget Subtitle Text:</label>
      </th>
      <td><input type="text" id="golfScores-class" name="golfScoresSubtitle" value="<?php echo esc_attr(get_option('golfScoresSubtitle')); ?>" /></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Select user to track scores for. Pro version allows multiple users!" for="trackPlayer">Track Scores For: </label>
      </th>
      <td><select name="trackPlayer" id="trackPlayer">
          <option value="1">Single User</option>
          <option value="2" disabled>All Users (Pro Version)</option>
          <option value="3" disabled>Selected Users (Pro Version)</option>
        </select></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Select user. Upgrade to PRO version for multiple users!" for="golfScoresUser">Select User: </label>
      </th>
      <td><select name="golfScoresUser" id="golfScoresUser">
          <?PHP    
						$blogusers = get_users('blog_id=1&orderby=nicename');
						
						foreach ($blogusers as $user) {
						?>
          <option value="<?php echo $user->id ?>"><?php echo $user->display_name; ?> (Login: <?php echo $user->user_login; ?>) (ID: <?php echo $user->id; ?>)</option>
          <?php 
							} // end foreach $blogusers as $user
						?>
        </select>
        <?php if(get_option('golfScoresUser')){
							$user_id = get_option('golfScoresUser');
							
						echo ' Current: ' . getUserName($user_id); }
						if(!get_option('golfScoresUser')){echo 'No user selected';} ?></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Select Name Format" for="golfScoresShowName">Username Format:</label>
      </th>
      <td><select name="golfScoresShowName" id="golfScoresShowName">
          <?php if(get_option('golfScoresShowName')){ ?>
          <?php if (get_option('golfScoresShowName') == 1) { 
					               	echo '<option value="1" selected>Display Name</option>';
								   		echo '<option value="2">Login Name</option>';
						}
							if (get_option('golfScoresShowName') == 2) { 
					               	echo '<option value="2" selected>Login Name</option>';
								   		echo '<option value="1">Display Name</option>';
						}
								   
                	?>
          </option>
          <?php }
						else {
							if(!get_option('golfScoresShowName')){ 
                  ?>
          <option value="">--Select--</option>
          <option value="1">Display Name</option>
          <option value="2">User Login</option>
          <?php } } ?>
        </select></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Show Net Score In Scores Table" for="golfScoresShowNet">Show Net Score On Front End:</label>
      </th>
      <td><select name="golfScoresShowNet" id="golfScoresShowNet">
          <?php if(get_option('golfScoresShowNet')){ ?>
          <?php if (get_option('golfScoresShowNet') == 1) { 
					               	echo '<option value="1" selected>Yes</option>';
								   		echo '<option value="2">No</option>';
						}
							if (get_option('golfScoresShowNet') == 2) { 
					               	echo '<option value="2" selected>No</option>';
								   		echo '<option value="1">Yes</option>';
						}
								   
                	?>
          </option>
          <?php }
						else {
							if(!get_option('golfScoresShowNet')){ 
                  ?>
          <option value="">--Select--</option>
          <option value="1">Yes</option>
          <option value="2">No</option>
          <?php } } ?>
        </select></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Show Tee Played In Scores Table" for="golfScoresShowTee">Show Tee Played On Front End:</label>
      </th>
      <td><select name="golfScoresShowTee" id="golfScoresShowTee">
          <?php if(get_option('golfScoresShowTee')){ ?>
          <?php if (get_option('golfScoresShowTee') == 1) { 
					               	echo '<option value="1" selected>Yes</option>';
								   		echo '<option value="2">No</option>';
						}
							if (get_option('golfScoresShowTee') == 2) { 
					               	echo '<option value="2" selected>No</option>';
								   		echo '<option value="1">Yes</option>';
						}
								   
                	?>
          </option>
          <?php }
						else {
							if(!get_option('golfScoresShowTee')){ 
                  ?>
          <option value="">--Select--</option>
          <option value="1">Yes</option>
          <option value="2">No</option>
          <?php } } ?>
        </select></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Show Holes Played In Scores Table" for="golfScoresShowHoles">Show Holes Played On Front End:</label>
      </th>
      <td><select name="golfScoresShowHoles" id="golfScoresShowHoles">
          <?php if(get_option('golfScoresShowHoles')){ ?>
          <?php if (get_option('golfScoresShowHoles') == 1) { 
					               	echo '<option value="1" selected>Yes</option>';
								   		echo '<option value="2">No</option>';
						}
							if (get_option('golfScoresShowHoles') == 2) { 
					               	echo '<option value="2" selected>No</option>';
								   		echo '<option value="1">Yes</option>';
						}
                	?>
          </option>
          <?php }
						else {
							if(!get_option('golfScoresShowHoles')){ 
                  ?>
          <option value="">--Select--</option>
          <option value="1">Yes</option>
          <option value="2">No</option>
          <?php } } ?>
        </select></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Number of list items to show in Golf Scores widget. Enter 0 for no list." for="golfScoresWidgetList">Front End Score List Limit:</label>
      </th>
      <td><input type="text" id="golfScoresWidgetList" size="2" name="golfScoresWidgetList" 
                	value="<?php echo esc_attr(get_option('golfScoresWidgetList')); ?>" onkeyup="this.value=this.value.replace(/[^\d]/,'')"/></td>
    </tr>
    <tr>
      <th scope="row"> <label title="Number of list items to show in Golf Scores administration." for="golfScoresAdminList">Admin Score List Limit:</label>
      </th>
      <td><input type="text" id="golfScoresAdminList" size="2" name="golfScoresAdminList" 
                	value="<?php echo esc_attr(get_option('golfScoresAdminList')); ?>" onkeyup="this.value=this.value.replace(/[^\d]/,'')"/></td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></td>
    </tr>
    <tr>
    	<td colspan="2">To put Golf Scores into a wordpress page or post, insert this code into the content: [widget widget_name="golfScores"] </td>
  </table>
  <br />
  <hr>
  <a href="http://www.tkserver.com/index.php?option=com_content&view=article&id=65:wp-golfscores&catid=12&Itemid=171">Golf Scores 1.0</a> for Wordpress
  <input type="hidden" name="golfScores_hidden" value="Y" />
  <?php
	
}  // end admin settings page


// add Golf Scores to settings menu
function golfScores_plugin_menu()
	{
		add_options_page('golfScores', 'Golf Scores', 'manage_options','golfScores-plugin','golfScores_option_page');
	}
	
add_action('admin_menu','golfScores_plugin_menu');

// start it up
function golfScores_activate()
	{		
	
		global $wpdb;
		$table_name = $wpdb->prefix . "golfScores";
		if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name )
		{
			$sql = 'CREATE TABLE ' . $table_name . '(
			id INTEGER(10) UNSIGNED AUTO_INCREMENT,
			date VARCHAR(255),
			user_id VARCHAR(255),
			course VARCHAR(255),
			tee VARCHAR(255),
			holes VARCHAR(255),
			gross_score VARCHAR(255),
			handicap VARCHAR (255),
			net_score VARCHAR(255),
			comments VARCHAR(255),
			PRIMARY KEY (id) )';
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			add_option('golfScores_database_version','1.0');
		}
	}
register_activation_hook(__FILE__, 'golfScores_activate');


/// can I implement the listing shit down here?
/*************************** LOAD THE BASE CLASS *******************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 * Create a new list table package that extends the core WP_List_Table class.
 */
class Golf_Scores_List_Table extends WP_List_Table {
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor.
     ***************************************************************************/
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'score',
            'plural' => 'scores',
        ));
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column.
     **************************************************************************/
   function column_default($item, $column_name)
    {
        return $item[$column_name];
    }
	
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'.
     **************************************************************************/
    function column_date($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=scores_form&id=%s">%s</a>', $item['id'], __('Edit', 'golfScores')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'golfScores')),
        );

        return sprintf('%s %s',
            $item['date'],
            $this->row_actions($actions)
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. 
     **************************************************************************/
	function column_cb($item){
        return sprintf(
             '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	function column_course($item)
    {
        return sprintf($item['course']);
    }
	
	function column_user_id($item)
    {
     	$user_id = sprintf($item['user_id']);
		echo getUserName($user_id);    
	}
	
	function column_tee($item)
    {
        return sprintf($item['tee']);
    }
	
	function column_gross_score($item)
    {
        return sprintf($item['gross_score']);
    }
	
	function column_handicap($item)
    {
        return sprintf($item['handicap']);
    }
	
	function column_net_score($item)
    {
        return sprintf($item['net_score']);
    }
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. 
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'date'  => 'Date',
            'user_id'  => 'User',
            'course'    => 'Course',
            'holes'    => 'Holes',
            'tee'    => 'Tee',
            'gross_score'    => 'Gross',
            'handicap'    => 'Handicap',
            'net_score'    => 'Net',
            'id'     => 'ID'
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here.
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'     => array('id',false),     //true means it's already sorted
            'course'    => array('course',false),
            'user_id'    => array('user_id',false),
            'tee'    => array('tee',false),
            'holes'    => array('holes',false),
            'gross_score'    => array('gross_score',false),
            'handicap'    => array('handicap',false),
            'net_score'    => array('net_score',false),
            'date'  => array('date',true)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. 
     **************************************************************************/
function get_bulk_actions() {
    $actions = array(
       'delete' => __( 'Delete')
    );

    return $actions;
}
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     *
     **************************************************************************/

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'golfScores'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
		//var_dump($idshole);
    }



    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display.
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = esc_attr(get_option('golfScoresAdminList'));
        
        
        /**
         * REQUIRED. Now we need to define our column headers.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. 
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**

         */
        $querydata = $wpdb->get_results(
			"SELECT * FROM wp_golfScores WHERE 1"
		);
         $data=array();
			foreach ($querydata as $querydatum ) {
			   array_push($data, (array)$querydatum);}       
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to date
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to desc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='desc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');

                
        /**
         * REQUIRED for pagination.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}



/** *************************************************************************
 * Define an admin page. 
 */
function golfscores_add_menu_items(){
   		add_menu_page('Golf Scores', 'Golf Scores', 'activate_plugins', 'golfscores', 'golfScores_scores_page_handler');
    	add_submenu_page('Golf Scores', __('Golf Scores', 'golfScores'), __('Golf Scores', 'golfScores'), 'activate_plugins', 'golfscores', 'golfScores_scores_page_handler');
    		// add new will be described in next part
    	add_submenu_page('golfscores', __('Enter Score', 'golfScores'), __('Enter Score', 'golfScores'), 'activate_plugins', 'scores_form', 'golfScores_scores_form_page_handler');
}
add_action('admin_menu', 'golfscores_add_menu_items');

/**
 * List page handler
 *
 * This function renders our custom table
 */
function golfScores_scores_page_handler()
{
    global $wpdb;

    $table = new Golf_Scores_List_Table ();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'golfScores'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
  <div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit"><br>
  </div>
  <h2>
    <?php _e('Golf Scores', 'golfScores')?>
    <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=scores_form');?>">
    <?php _e('Add New', 'golfScores')?>
    </a> </h2>
  <?php echo $message; ?>
  <form id="scores-table" method="GET">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
    <?php $table->display() ?>
  </form>
</div>
<?php
}



/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 */
function golfScores_scores_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'golfScores'; // do not forget about tables prefix

    $message = '';
    $notice = '';
	$holes = $_POST['holes'];
	$gross_score = $_POST['gross_score'];
	$handicap = $_POST['handicap'];
	if($holes == 18){$net_score = $gross_score - $handicap;}
	if($holes == 9){$net_score = $gross_score - ($handicap / 2);}
    // this is default $item which will be used for new records
    $default = array(
        'user_id' => $_POST['user_id'],
        'course' => $_POST['course'],
        'tee' => $_POST['tee'],
        'holes' => $_POST['holes'],
        'date' => $_POST['golfScoresDate'],
        'gross_score' => $_POST['gross_score'],
        'handicap' => $_POST['handicap'],
        'net_score' => $net_score,
        'comments' => $_POST['comments'],
        'id' => $_POST['id']
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = golfScores_validate_score($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'golfScores');
                } else {
                    $notice = __('There was an error while saving item', 'golfScores');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'golfScores');
                } else {
                    $notice = __('There was an error while updating item', 'golfScores');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'golfScores');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('scores_form_meta_box', 'Score Data', 'golfScores_scores_form_meta_box_handler', 'score', 'normal', 'default');

    ?>
<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit"><br>
  </div>
  <h2>
    <?php _e('Score', 'golfScores')?>
    <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=golfscores');?>">
    <?php _e('Back To Scores List', 'golfScores')?>
    </a> </h2>
  <?php if (!empty($notice)): ?>
  <div id="notice" class="error">
    <p><?php echo $notice ?></p>
  </div>
  <?php endif;?>
  <?php if (!empty($message)): ?>
  <div id="message" class="updated">
    <p><?php echo $message ?></p>
  </div>
  <?php endif;?>
  <form id="form" method="POST">
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
    <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
    <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
    <div class="metabox-holder" id="poststuff">
      <div id="post-body">
        <div id="post-body-content">
          <?php /* And here we call our custom meta box */ ?>
          <?php do_meta_boxes('score', 'normal', $item); ?>
          <input type="submit" value="<?php _e('Save', 'golfScores')?>" id="submit" class="button-primary" name="submit">
        </div>
      </div>
    </div>
  </form>
</div>
<?php
}



 
function golfScores_scores_form_meta_box_handler($item)
{
	
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
?>
<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#golfScoresDate').datepicker({
                dateFormat : 'yy-mm-dd'
            });
        });

    </script>
<?php // check to see if reading user_id from existing record (edit form).  If not, display allowed user (new form).
				if($item['user_id']){$user_id = $item['user_id'];}
				if(!$item['user_id']){$user_id = get_option('golfScoresUser');}
			?>
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
  <tbody>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="user_id">
          <?php _e('User', 'golfScores')?>
        </label>
      </th>
      <td><input id="user_id" name="user_id" type="hidden" style="width: 100%" value="<?php echo $user_id; ?>"
                   size="50" class="code" placeholder="<?php _e('User', 'golfScores')?>">
        <input id="username" name="username" type="text" style="width: 100%" value="<?php echo getUserName($user_id) . ' (ID: ' . $user_id . ')';  ?>"
                   size="50" class="code" readonly></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="golfScoresDate">
          <?php _e('Date', 'golfScores')?>
        </label>
      </th>
      <td><input type="text" id="golfScoresDate" name="golfScoresDate" style="width: 100%" value="<?php echo esc_attr($item['date'])?>" placeholder="<?php _e('Select Date', 'golfScores')?>" readonly/></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="course">
          <?php _e('Course', 'golfScores')?>
        </label>
      </th>
      <td><input id="course" name="course" type="text" style="width: 100%" value="<?php echo esc_attr($item['course'])?>"
                   size="50" class="code" placeholder="<?php _e('Course', 'golfScores')?>" required></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="tee">
          <?php _e('Tee Played', 'golfScores')?>
        </label>
      </th>
      <td><input id="tee" name="tee" type="text" style="width: 100%" value="<?php echo esc_attr($item['tee'])?>"
                   size="50" class="code" placeholder="<?php _e('Tee', 'golfScores')?>" required></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="holes">
          <?php _e('Holes', 'golfScores')?>
        </label>
      </th>
      <td><select name="holes" id="holes">
          <?php if($holes == 18) { 
					               	echo '<option value="18" selected>18</option>';
								   		echo '<option value="9">9</option>';
						}
							if($holes == 9) { 
					               	echo '<option value="9" selected>9</option>';
								   		echo '<option value="18">18</option>';
						}
								   
                	?>
          </option>
          <?php 
						if (!$holes) {
							
                  ?>
          <option value="18" selected>18</option>
          <option value="9">9</option>
          <?php  } ?>
        </select></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="gross_score">
          <?php _e('Gross Score', 'golfScores')?>
        </label>
      </th>
      <td><input id="gross_score" name="gross_score" type="text" style="width: 100%" value="<?php echo esc_attr($item['gross_score'])?>"
                    class="code" placeholder="<?php _e('Gross Score', 'golfScores')?>" size="5" maxlength="3" required></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="handicap">
          <?php _e('Handicap', 'golfScores')?>
        </label>
      </th>
      <td><input id="handicap" name="handicap" type="text" style="width: 100%" value="<?php echo esc_attr($item['handicap'])?>"
                    class="code" placeholder="<?php _e('Handicap', 'golfScores')?>" size="5" maxlength="4" required></td>
    </tr>
    <tr class="form-field">
      <th valign="top" scope="row"> <label for="comments">
          <?php _e('Comments', 'golfScores')?>
        </label>
      </th>
      <td><input id="comments" name="comments" type="text" style="width: 100%" value="<?php echo esc_attr($item['comments'])?>"
                   size="50" class="code" placeholder="<?php _e('Comments', 'golfScores')?>" ></td>
    </tr>
  </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 */
function golfScores_validate_score($item)
{
    $messages = array();

    if (empty($item['course'])) $messages[] = __('Course is required', 'golfScores');
    if (empty($item['tee'])) $messages[] = __('Tee is required', 'golfScores');
    if (empty($item['date'])) $messages[] = __('Date is required', 'golfScores');
    if (empty($item['gross_score'])) $messages[] = __('Gross score is required', 'golfScores');
    if (empty($item['handicap'])) $messages[] = __('Handicap is required', 'golfScores');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}