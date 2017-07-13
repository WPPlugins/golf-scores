<?php

/// can I implement the listing shit down here?



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * golfScores_List_Table class that will display our custom table
 * records in nice table
 */
class golfScores_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'Score',
            'plural' => 'Scores',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
function column_user_id($item)
    {
		$user_id = $item['user_id'];
       // return '<em>poop' . $item['user_id'] . '</em>';
		
		return getUserName($user_id); 
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
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

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'date' => __('Date', 'golfScores'),
            'user_id' => __('User', 'golfScores'),
            'course' => __('Course', 'golfScores'),
            'tee' => __('Tee', 'golfScores'),
            'gross_score' => __('Gross', 'golfScores'),
            'handicap' => __('Handicap', 'golfScores'),
            'net_score' => __('Net', 'golfScores'),
            'id' => __('ID', 'golfScores')
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'course' => array('course', false),
            'id' => array('id', false),
            'user_id' => array('user_id', false),
            'date' => array('date', true),
            'gross_score' => array('gross_score', true),
            'handicap' => array('handicap', true),
            'net_score' => array('net_score', true)
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
            // 'edit' => 'Edit'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
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
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'golfScores'; // do not forget about tables prefix

        $per_page = 10; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'date';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */

/**
 * admin_menu hook implementation, will add pages to list persons and to add new one
 */
function golfScores_admin_menu()
{
    add_menu_page(__('Golf Scores', 'golfScores'), __('Golf Scores', 'golfScores'), 'activate_plugins', 'golfscores', 'golfScores_scores_page_handler');
    add_submenu_page('Golf Scores', __('Golf Scores', 'golfScores'), __('Golf Scores', 'golfScores'), 'activate_plugins', 'golfscores', 'golfScores_scores_page_handler');
    // add new will be described in next part
    add_submenu_page('golfscores', __('Add New', 'golfScores'), __('Add New', 'golfScores'), 'activate_plugins', 'scores_form', 'golfScores_scores_form_page_handler');
}

add_action('admin_menu', 'golfScores_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function golfScores_scores_page_handler()
{
    global $wpdb;

    $table = new golfScores_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'golfScores'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Golf Scores', 'golfScores')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=scores_form');?>"><?php _e('Add New', 'golfScores')?></a>
    </h2>
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
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function golfScores_scores_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'golfScores'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'user_id' => $_POST['user_id'],
        'course' => $_POST['course'],
        'tee' => $_POST['tee'],
        'holes' => $_POST['holes'],
        'date' => $_POST['golfScoresDate'],
        'gross_score' => $_POST['gross_score'],
        'handicap' => $_POST['handicap'],
        'net_score' => $_POST['net_score'],
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
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Score', 'golfScores')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=golfscores');?>"><?php _e('Back To Scores List', 'golfScores')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
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
        <th valign="top" scope="row">
            <label for="user_id"><?php _e('User', 'golfScores')?></label>
        </th>
        <td>
            <input id="user_id" name="user_id" type="hidden" style="width: 50%" value="<?php echo $user_id; ?>"
                   size="50" class="code" placeholder="<?php _e('User', 'golfScores')?>">
                   <input id="username" name="username" type="text" style="width: 50%" value="<?php echo getUserName($user_id) . ' (ID: ' . $user_id . ')';  ?>"
                   size="50" class="code" readonly>
        </td>
    </tr>
	<tr class="form-field">
        <th valign="top" scope="row">
            <label for="golfScoresDate"><?php _e('Date', 'golfScores')?></label>
        </th>
        <td>
            <input type="text" id="golfScoresDate" name="golfScoresDate" style="width: 50%" value="<?php echo esc_attr($item['date'])?>" placeholder="<?php _e('Select Date', 'golfScores')?>" readonly/>
        </td>
    </tr>    
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="course"><?php _e('Course', 'golfScores')?></label>
        </th>
        <td>
            <input id="course" name="course" type="text" style="width: 50%" value="<?php echo esc_attr($item['course'])?>"
                   size="50" class="code" placeholder="<?php _e('Course', 'golfScores')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="tee"><?php _e('Tee Played', 'golfScores')?></label>
        </th>
        <td>
            <input id="tee" name="tee" type="text" style="width: 50%" value="<?php echo esc_attr($item['tee'])?>"
                   size="50" class="code" placeholder="<?php _e('Tee', 'golfScores')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="holes"><?php _e('Holes', 'golfScores')?></label>
        </th>
        <td>
            <input id="holes" name="holes" type="text" style="width: 50%" value="<?php echo esc_attr($item['holes'])?>"
                   size="50" class="code" placeholder="<?php _e('Holes', 'golfScores')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="gross_score"><?php _e('Gross Score', 'golfScores')?></label>
        </th>
        <td>
            <input id="gross_score" name="gross_score" type="text" style="width: 50%" value="<?php echo esc_attr($item['gross_score'])?>"
                   size="50" class="code" placeholder="<?php _e('Gross Score', 'golfScores')?>" required>
        </td>
    </tr>
        <tr class="form-field">
        <th valign="top" scope="row">
            <label for="handicap"><?php _e('Handicap', 'golfScores')?></label>
        </th>
        <td>
            <input id="handicap" name="handicap" type="text" style="width: 50%" value="<?php echo esc_attr($item['handicap'])?>"
                   size="50" class="code" placeholder="<?php _e('Handicap', 'golfScores')?>" required>
        </td>
    </tr>
        <tr class="form-field">
        <th valign="top" scope="row">
            <label for="net_score"><?php _e('Net Score', 'golfScores')?></label>
        </th>
        <td>
            <input id="net_score" name="net_score" type="text" style="width: 50%" value="<?php echo esc_attr($item['net_score'])?>"
                   size="50" class="code" placeholder="<?php _e('Net Score', 'golfScores')?>" required>
        </td>
    </tr>
        <tr class="form-field">
        <th valign="top" scope="row">
            <label for="comments"><?php _e('Comments', 'golfScores')?></label>
        </th>
        <td>
            <input id="comments" name="comments" type="text" style="width: 50%" value="<?php echo esc_attr($item['comments'])?>"
                   size="50" class="code" placeholder="<?php _e('Comments', 'golfScores')?>" >
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
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