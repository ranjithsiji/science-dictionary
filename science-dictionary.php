<?php
/*
Plugin Name: Science Dictionary
Description: Science Dictionary Plugin for LUCA
Plugin URI: http://alphafork.com
Author URI: http://alphafork.com/
Author: Ranjith Siji
License: GPL V2
Version: 1.2
*/

/**
 * PART 1. Defining Custom Database Table
 * ============================================================================
 *
 * In this part you are going to define custom database table,
 * create it, update, and fill with some dummy data
 *
 * http://codex.wordpress.org/Creating_Tables_with_Plugins
 *
 * In case your are developing and want to check plugin use:
 *
 * DROP TABLE IF EXISTS wp_cte;
 * DELETE FROM wp_options WHERE option_name = 'science_dictionary_install_data';
 *
 * to drop table and option
 */

/**
 * $science_dictionary_db_version - holds current database version
 * and used on plugin update to sync database tables
 */
global $science_dictionary_db_version;
$science_dictionary_db_version = '1.2'; // version changed from 1.0 to 1.1

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
function science_dictionary_install()
{
    global $wpdb;
    global $science_dictionary_db_version;

    $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        enword varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        word varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        meaning text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        reftext varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
        refid int(11) DEFAULT NULL
        PRIMARY KEY  (id)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('science_dictionary_db_version', $science_dictionary_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin
     * just increment $science_dictionary_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('science_dictionary_db_version');
    if ($installed_ver != $science_dictionary_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            enword varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
            word varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
            meaning text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
            reftext varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
            refid int(11) DEFAULT NULL
            PRIMARY KEY  (id)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('science_dictionary_db_version', $science_dictionary_db_version);
    }
}

register_activation_hook(__FILE__, 'science_dictionary_install');

/**
 * register_activation_hook implementation
 *
 * [OPTIONAL]
 * additional implementation of register_activation_hook
 * to insert some dummy data
 */
function science_dictionary_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'enword' => 'Science',
        'word' => 'science',
		'meaning' => 'science',
		'reftext' => 'science',
        'refid' => 1
    ));
    $wpdb->insert($table_name, array(
        'enword' => 'Dictionary',
        'word' => 'dictionary',
		'meaning' => 'dictionary',
		'reftext' => 'dictionary',
        'refid' => 1
    ));

//	mkdir(ABSPATH."/upload_images",'0777');
}

register_activation_hook(__FILE__, 'science_dictionary_install_data');

/**
 * Trick to update plugin database, see docs
 */
function science_dictionary_update_db_check()
{
    global $science_dictionary_db_version;
    if (get_site_option('science_dictionary_db_version') != $science_dictionary_db_version) {
        science_dictionary_install();
    }
}

add_action('plugins_loaded', 'science_dictionary_update_db_check');

/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Custom_Table_Example_List_Table class that will display our custom table
 * records in nice table
 */
class Science_Dictionary_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
   public function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'word',
            'plural' => 'words',
            'ajax' => true
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    public function column_default($item, $column_word)
    {
        return $item[$column_word];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_word($item)
    {
        return '<strong>' . $item['word'] . '</strong>';
    }
    /*
	 function column_image($item)
    {
		$image_url=site_url()."/upload_images/".$item['image'];
        return "<img src='".$image_url."' alt='' width='100' />";
    }
    */
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_enword($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=words_form&id=%s">%s</a>', $item['id'], __('Edit', 'science_dictionary')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'science_dictionary')),
        );

        return sprintf('%s %s',
            $item['enword'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_cb($item)
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
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'enword' => __('English Word', 'science_dictionary'),
            'word' => __('Malayalam Word', 'science_dictionary'),
            'meaning' => __('Meaning', 'science_dictionary'),
            'reftext' => __('Ref. Text', 'science_dictionary'),
            'refid' => __('Reference', 'science_dictionary'),
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
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'enword' => array('enword', true),
            'word' => array('word', true),
            'meaning' => array('meaning', false),
            'reftext' => array('reftext', true),
            'refid' => array('refid', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
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
    public function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
                $nonce = esc_attr($_REQUEST['_wpnonce']);
                if (!wp_verify_nonce($nonce, 'bx_delete_records')) {
                die('Go get a life script kiddies');
                } else {
                    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
                    if (is_array($ids)) $ids = implode(',', $ids);

                    if (!empty($ids)) {
                       $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
                    //After Delete refresh the page
                        $redirect = admin_url('admin.php?page=words');
                        wp_redirect($redirect);
                        exit;
                    }
                }

        }
    }

    /**
    * Returns the count of records in the database.
    * * @return null|string
    */
    public static function record_count()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sde';
        $sql = "SELECT COUNT(id) FROM $table_name";
        if (isset($_REQUEST['s'])) {
        $sql.= ' where enword LIKE "%' . $_REQUEST['s'] . '%" or word LIKE "%' . $_REQUEST['s'] . '%"';
        }
        return $wpdb->get_var($sql);
    }

    /** *
    * Retrieve records data from the database
    * * @param int $per_page
    * @param int $page_number
    * * @return mixed
    */
    public static function get_records($per_page = 20, $page_number = 1)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix
        $sql = "select * from $table_name";
        if (isset($_REQUEST['s'])) {
        $sql.= ' where enword LIKE "%' . $_REQUEST['s'] . '%" or word LIKE "%' . $_REQUEST['s'] . '%"';
        }
        if (!empty($_REQUEST['orderby'])) {
        $sql.= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
        $sql.= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }
        $sql.= " LIMIT $per_page";
        $sql.= ' OFFSET ' . ($page_number - 1) * $per_page;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix

       // $per_page = 25; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum();
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('records_per_page', 10);
        $current_page = $this->get_pagenum();

        // will be used in pagination settings
        //$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $total_items = self::record_count();

        $data = self::get_records($per_page, $current_page);

        // prepare query params, as usual current page, order by and order direction
        /*$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'enword';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        */
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        // $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
        $this->items = $data;
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
function science_dictionary_admin_menu()
{
   $hook = add_menu_page(__('Words', 'science_dictionary'), __('Words', 'science_dictionary'), 'activate_plugins', 'words', 'science_dictionary_words_page_handler');
    add_submenu_page('words', __('Words', 'science_dictionary'), __('Words', 'science_dictionary'), 'activate_plugins', 'words', 'science_dictionary_words_page_handler');
    // add new will be described in next part
    add_submenu_page('words', __('Add new', 'science_dictionary'), __('Add new', 'science_dictionary'), 'activate_plugins', 'words_form', 'science_dictionary_words_form_page_handler');
    add_action( "load-$hook", 'science_dictionary_add_options' );
}

function science_dictionary_add_options() {
  global $table;
  $option = 'per_page';
  $args = array(
         'label' => 'Engish Word',
         'default' => 10,
         'option' => 'words_per_page'
         );
  add_screen_option( $option, $args );
  $table = new Science_Dictionary_List_Table();
}

add_action('admin_menu', 'science_dictionary_admin_menu');

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
function science_dictionary_words_page_handler()
{
    global $wpdb;

    $table = new Science_Dictionary_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Words deleted: %d', 'science_dictionary'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Words', 'science_dictionary')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=words_form');?>"><?php _e('Add new', 'science_dictionary')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="words-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->search_box( __( 'Search Words' ), 'word' ); ?>
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
function science_dictionary_words_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sde'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'enword' => '',
        'word' => '',
        'meaning' => '',
        'reftext' => '',
        'refid' => null,
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
	        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
		// print_r($item);exit;
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = science_dictionary_validate_word($item);
        if ($item_valid === true) {

            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Word was successfully saved', 'science_dictionary');
                } else {
                    $notice = __('There was an error while saving word', 'science_dictionary');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));

                if ($result) {
                    $message = __('Word was successfully updated', 'science_dictionary');
                } else {
					//$message = __('Word was successfully updated', 'science_dictionary');
                    $notice = __('There was an error while updating word', 'science_dictionary');
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
                $notice = __('Word not found', 'science_dictionary');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('words_form_meta_box', 'Word data', 'science_dictionary_words_form_meta_box_handler', 'word', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Word', 'science_dictionary')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=words');?>"><?php _e('Back to Word list', 'science_dictionary')?></a>
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
                    <?php do_meta_boxes('word', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'science_dictionary')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function science_dictionary_words_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="enword"><?php _e('English Word', 'science_dictionary')?></label>
        </th>
        <td>
            <input id="enword" name="enword" type="text" style="width: 95%" value="<?php echo esc_attr($item['enword'])?>"
                   size="50" class="code" placeholder="<?php _e('English Word', 'science_dictionary')?>" required>
        </td>
    </tr><tr class="form-field">
        <th valign="top" scope="row">
            <label for="word"><?php _e('Malayalam Word', 'science_dictionary')?></label>
        </th>
        <td>
            <input id="word" name="word" type="text" style="width: 95%" value="<?php echo esc_attr($item['word'])?>"
                   size="50" class="code" placeholder="<?php _e('Malayalam Word', 'science_dictionary')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="meaning"><?php _e('Word Meaning', 'science_dictionary')?></label>
        </th>
        <td>
            <textarea id="meaning" name="meaning" class="code" placeholder="<?php _e('Word Meaning', 'science_dictionary')?>" style="width: 95%;height:200px;" required><?php echo esc_attr($item['meaning'])?></textarea>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="reftext"><?php _e('Reference Text', 'science_dictionary')?></label>
        </th>
        <td>
        <input type="text" name="reftext" id="reftext" value="<?php echo esc_attr($item['reftext'])?>"  />
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="refid"><?php _e('Reference Id', 'science_dictionary')?></label>
        </th>
        <td>
            <input id="refid" name="refid" type="number" style="width: 95%" value="<?php echo esc_attr($item['refid'])?>"
                   size="50" class="code" placeholder="<?php _e('Reference Number', 'science_dictionary')?>" required>
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
function science_dictionary_validate_word($item)
{
    $messages = array();

    if (empty($item['enword'])) $messages[] = __('English Word is required', 'science_dictionary');
    if (empty($item['word'])) $messages[] = __('Malayalam Word is required', 'science_dictionary');
    if (empty($item['meaning'])) $messages[] = __('Meaning is required', 'science_dictionary');

    if (!ctype_digit($item['refid'])) $messages[] = __('Reference in wrong format', 'science_dictionary');


    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

function science_dictionary_words_code() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'sde';
    $sql = "select * from $table_name";

    $customPagHTML     = "";
    $total_query     = "SELECT COUNT(1) FROM (${sql}) AS combined_table";
    $total             = $wpdb->get_var( $total_query );
    $items_per_page = 20;
    $page             = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $offset         = ( $page * $items_per_page ) - $items_per_page;
    $result         = $wpdb->get_results( $sql . " ORDER BY enword ASC LIMIT ${offset}, ${items_per_page}" );
    $totalPage         = ceil($total / $items_per_page);

//    print_r ($result); die;

    $customPagHTML .='<table><tr><th>English word</th><th>Malayalam Word</th><th>Meaning</th></tr>';

    foreach ($result as $row)
    {
        $customPagHTML .='<tr>';
        $customPagHTML .= '<td><a href="' . home_url( '/science-word/?funcparam='.(str_replace(' ', '_', strtolower($row->enword))) ) .'" target="_blank">'.$row->enword.'</a></td>';
        $customPagHTML .= '<td><a href="https://en.wikipedia.org/w/index.php?search='.$row->enword.'" target="_blank">'.$row->word.'</a></td>';
        $customPagHTML .= '<td>'.$row->meaning.'</td>';
        $customPagHTML .='</tr>';
    }
    $customPagHTML .= '</table>';
    if($totalPage > 1){
        $customPagHTML     .=  '<div id="paginate" style="border:1px solid #333;padding:6px;"><span class="pages">Page '.$page.' of '.$totalPage.'</span> <span id="pages" style="margin-left:10px;border-left:2px solid #333; padding-left:10px;">'.paginate_links(      array(
        'base' => add_query_arg( 'cpage', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $totalPage,
        'current' => $page
        )).'</span></div>';
    }


	// Return output
	ob_start();
	echo $customPagHTML;
	return ob_get_clean();
}
add_shortcode( 'sciencewords', 'science_dictionary_words_code' );


function wpssearchform( $form ) {

    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/science-search/' ) . '" >
    <div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
    <input type="text" value="' . get_search_query() . '" name="param" id="param" />
    <input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
    </div>
    </form>';

    return $form;
}

add_shortcode('wpssearch', 'wpssearchform');

function sc_getParam($atts) {

    // get parameter(s) from the shortcode
    extract( shortcode_atts( array(
        "funcparam"    => 'hard',
    ), $atts, 'scienceword' ) );

    // check whether the parameter is not empty AND if there is
    // something in the $_GET[]
    if ( $funcparam != '' && isset( $_GET[ $funcparam ] ) ) {

        // sanitizing - this is for protection!
        $thisparam = sanitize_text_field( $_GET[ $funcparam ] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'sde';
        $sql = "select * from $table_name where enword like '%$thisparam%'";
        $customPagHTML     = "";
        $result         = $wpdb->get_results( $sql . " ORDER BY enword ASC" );
        $customPagHTML .='<table><tr><th>English word</th><th>Malayalam Word</th><th>Meaning</th></tr>';
        foreach ($result as $row)
        {
            $customPagHTML .='<tr>';
            $customPagHTML .= '<td><a href="' . home_url( '/science-word/?funcparam='.(str_replace(' ', '_', strtolower($row->enword))) ) .'" target="_blank">'.$row->enword.'</a></td>';
            $customPagHTML .= '<td><a href="https://en.wikipedia.org/w/index.php?search='.$row->enword.'" target="_blank">'.$row->word.'</a></td>';
            $customPagHTML .= '<td>'.$row->meaning.'</td>';
            $customPagHTML .='</tr>';
        }
        $customPagHTML .= '</table>';

        return $customPagHTML;

    } else {

        // something is not OK with the shortcode function, so it
        // returns false
        $customPagHTML = '<p>The search term not found.</p>';
        return $customPagHTML;

    }

}
add_shortcode( 'scienceword', 'sc_getParam' );

function sc_getValue($atts) {

    // get parameter(s) from the shortcode
    extract( shortcode_atts( array(
        "funcparam"    => 'funcparam',
    ), $atts, 'sciencewordvalue' ) );

    // check whether the parameter is not empty AND if there is
    // something in the $_GET[]
    if ( $funcparam != '' && isset( $_GET[ $funcparam ] ) ) {

        // sanitizing - this is for protection!
        $thisparam = sanitize_text_field( $_GET[ $funcparam ] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'sde';
        $sql = "select * from $table_name where enword LIKE '$thisparam'";
        $customPagHTML     = "";
        $result         = $wpdb->get_results( $sql . " Limit 1" );
        foreach ($result as $row)
        {
            $customPagHTML .= '<h2><a href="https://en.wikipedia.org/w/index.php?search='.$row->enword.'" target="_blank">'.$row->enword.'</a></h2>';
            $customPagHTML .= '<p>'.$row->word.'</p>';
            $customPagHTML .= '<p>'.$row->meaning.'</p>';
            $customPagHTML .= '<p><a href="https://en.wikipedia.org/w/index.php?search='.$row->enword.'" target="_blank">More at English Wikipedia</a></p>';
        }

        return $customPagHTML;

    } else {

        // something is not OK with the shortcode function, so it
        // returns false
        $customPagHTML = '<p>The term not found.</p>';
        return $customPagHTML;

    }

}
add_shortcode( 'sciencewordvalue', 'sc_getValue' );


/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == science_dictionary
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function science_dictionary_languages()
{
    load_plugin_textdomain('science_dictionary', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'science_dictionary_languages');
