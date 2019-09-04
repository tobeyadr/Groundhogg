<?php

namespace Groundhogg\Admin\Contacts\Tables;

use function Groundhogg\current_user_is;
use function Groundhogg\get_db;
use function Groundhogg\get_request_query;
use function Groundhogg\get_request_var;
use function Groundhogg\get_screen_option;
use function Groundhogg\get_url_var;
use function Groundhogg\html;
use function Groundhogg\isset_not_empty;
use Groundhogg\Preferences;
use \WP_List_Table;
use Groundhogg\Plugin;
use Groundhogg\Contact;
use Groundhogg\Contact_Query;


/**
 * Contacts Table Class
 *
 * This class shows the data table for accessing information about a customer.
 *
 * @package     Admin
 * @subpackage  Admin/Contacts
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @see         WP_List_Table, contact-editor.php
 * @since       File available since Release 0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Contacts_Table extends WP_List_Table
{

    private $query;

    /**
     * TT_Example_List_Table constructor.
     *
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     */
    public function __construct()
    {
        // Set parent defaults.
        parent::__construct(array(
            'singular' => 'contact',     // Singular name of the listed records.
            'plural' => 'contacts',    // Plural name of the listed records.
            'ajax' => true,       // Does this table support ajax?
            'screen' => wp_doing_ajax() ? 'admin_ajax' : null
        ));
    }

    /**
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information.
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Render a checkbox instead of text.
            'email' => _x('Email', 'Column label', 'groundhogg'),
            'first_name' => _x('First Name', 'Column label', 'groundhogg'),
            'last_name' => _x('Last Name', 'Column label', 'groundhogg'),
            'user_id' => _x('Username', 'Column label', 'groundhogg'),
            'owner_id' => _x('Owner', 'Column label', 'groundhogg'),
            'date_created' => _x('Date', 'Column label', 'groundhogg'),
        );
        return apply_filters('groundhogg_contact_columns', $columns);
    }

    /**
     * Get a list of sortable columns. The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => array( 'orderby', true )
     *
     * The second format will make the initial sorting order be descending
     * @return array An associative array containing all the columns that should be sortable.
     */
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'email' => array('email', false),
            'first_name' => array('first_name', false),
            'last_name' => array('last_name', false),
            'user_id' => array('user_id', false),
            'owner_id' => array('owner_id', false),
            'date_created' => array('date_created', false)
        );
        return apply_filters('groundhogg_contact_sortable_columns', $sortable_columns);
    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_email($contact)
    {

        $editUrl = admin_url('admin.php?page=gh_contacts&action=edit&contact=' . $contact->get_id());
        $html = '<div id="inline_' . intval($contact->get_id()) . '" class="hidden">';
        $html .= '  <div class="email">' . esc_html($contact->get_email()) . '</div>';
        $html .= '  <div class="first_name">' . esc_html($contact->get_first_name()) . '</div>';
        $html .= '  <div class="last_name">' . esc_html($contact->get_last_name()) . '</div>';
        $html .= '  <div class="optin_status">' . esc_html($contact->get_optin_status()) . '</div>';
        if ($contact->get_owner_id()) {
            $html .= '  <div class="owner">' . esc_html($contact->get_owner_id()) . '</div>';
        }
        $html .= '  <div class="tags">' . esc_html(json_encode($contact->get_tags())) . '</div>';
        $html .= '  <div class="tags-data">' . esc_html(wp_json_encode($contact->get_tags_for_select2())) . '</div>';
        $html .= '</div>';

        $html .= "<strong>";

        $html .= "<a class='row-title' href='$editUrl'>" . html()->e( 'img', [ 'src' => $contact->get_profile_picture(), 'style' => [ 'float' => 'left', 'margin-right' => '10px' ], 'width' => 40 ] ) . esc_html($contact->get_email()) . "</a>";

        if (!get_request_var('optin_status')) {

            $html .= " &#x2014; " . "<span class='post-state'>(" . Preferences::get_preference_pretty_name($contact->get_optin_status()) . ")</span>";
        }

        $html .= "</strong>";

        return $html;

    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_first_name($contact)
    {
        return $contact->get_first_name() ? $contact->get_first_name() : '&#x2014;';
    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_last_name($contact)
    {
        return $contact->get_last_name() ? $contact->get_last_name() : '&#x2014;';
    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_user_id($contact)
    {
        return $contact->get_userdata() ? '<a href="' . admin_url('user-edit.php?user_id=' . $contact->get_userdata()->ID) . '">' . $contact->get_userdata()->display_name . '</a>' : '&#x2014;';
    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_owner_id($contact)
    {
        return !empty($contact->get_owner_id()) ? '<a href="' . admin_url('admin.php?page=gh_contacts&owner_id=' . $contact->get_owner_id()) . '">' . $contact->owner->user_login . '</a>' : '&#x2014;';
    }

    /**
     * @param $contact Contact
     * @return string
     */
    protected function column_date_created($contact)
    {
        $dc_time = mysql2date('U', $contact->get_date_created());
        $cur_time = (int)current_time('timestamp');
        $time_diff = $dc_time - $cur_time;
        $time_prefix = __('Created', 'groundhogg');
        if (absint($time_diff) > 24 * HOUR_IN_SECONDS) {
            $time = date_i18n('Y/m/d \@ h:i A', intval($dc_time));
        } else {
            $time = sprintf("%s ago", human_time_diff($dc_time, $cur_time));
        }
        return $time_prefix . '<br><abbr title="' . date_i18n(DATE_ISO8601, intval($dc_time)) . '">' . $time . '</abbr>';
    }

    /**
     * Get default column value.
     * @param object $contact A singular item (one full row's worth of data).
     * @param string $column_name The name/slug of the column to be processed.
     * @return string Text or HTML to be placed inside the column <td>.
     */
    protected function column_default($contact, $column_name)
    {

        do_action('groundhogg_contacts_custom_column', $contact, $column_name);

        return '';
    }

    /**
     * Get value for checkbox column.
     *
     * @param  $contact Contact A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_cb($contact)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  // Let's simply repurpose the table's singular label ("movie").
            $contact->get_id()                // The value of the checkbox should be the record's ID.
        );
    }

    /**
     * Get an associative array ( option_name => option_title ) with the list
     * of bulk steps available on this table.
     * @return array An associative array containing all the bulk steps.
     */
    protected function get_bulk_actions()
    {
        $actions = array(
            'apply_tag' => _x('Apply Tag', 'List table bulk action', 'groundhogg'),
//            'export' => _x( 'Export', 'List table bulk action', 'groundhogg' ),
            'remove_tag' => _x('Remove Tag', 'List table bulk action', 'groundhogg'),
            'delete' => _x('Delete', 'List table bulk action', 'groundhogg'),
        );

        if ($this->get_view() === 'spam') {
            $actions['unspam'] = _x('Approve', 'List table bulk action', 'groundhogg');
        } else {
            $actions['spam'] = _x('Mark as Spam', 'List table bulk action', 'groundhogg');
        }


        return apply_filters('groundhogg_contact_bulk_actions', $actions);
    }

    protected function get_view()
    {
        return (isset($_GET['optin_status'])) ? absint($_GET['optin_status']) : 10;
    }

    protected function get_views()
    {
        $base_url = admin_url('admin.php?page=gh_contacts&optin_status=');

        $view = $this->get_view();

        $count = array(
            'unconfirmed' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::UNCONFIRMED]),
            'confirmed' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::CONFIRMED]),
            'weekly' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::WEEKLY]),
            'monthly' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::MONTHLY]),
            'opted_out' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::UNSUBSCRIBED]),
            'spam' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::SPAM]),
            'bounce' => Plugin::$instance->dbs->get_db('contacts')->count(['optin_status' => Preferences::HARD_BOUNCE]),
        );

        return apply_filters('contact_views', array(
            'all' => "<a class='" . ($view === 10 ? 'current' : '') . "' href='" . admin_url('admin.php?page=gh_contacts') . "'>" . _x('All', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['unconfirmed'] + $count['confirmed']) . ')</span>' . "</a>",
            'confirmed' => "<a class='" . ($view === Preferences::CONFIRMED ? 'current' : '') . "' href='" . $base_url . Preferences::CONFIRMED . "'>" . _x('Confirmed', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['confirmed']) . ')</span>' . "</a>",
            'weekly' => "<a class='" . ($view === Preferences::WEEKLY ? 'current' : '') . "' href='" . $base_url . Preferences::WEEKLY . "'>" . _x('Weekly', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['weekly']) . ')</span>' . "</a>",
            'monthly' => "<a class='" . ($view === Preferences::MONTHLY ? 'current' : '') . "' href='" . $base_url . Preferences::MONTHLY . "'>" . _x('Monthly', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['monthly']) . ')</span>' . "</a>",
            'unconfirmed' => "<a class='" . ($view === Preferences::UNCONFIRMED ? 'current' : '') . "' href='" . $base_url . Preferences::UNCONFIRMED . "'>" . _x('Unconfirmed', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['unconfirmed']) . ')</span>' . "</a>",
            'opted_out' => "<a class='" . ($view === Preferences::UNSUBSCRIBED ? 'current' : '') . "' href='" . $base_url . Preferences::UNSUBSCRIBED . "'>" . _x('Unsubscribed', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['opted_out']) . ')</span>' . "</a>",
            'spam' => "<a class='" . ($view === Preferences::SPAM ? 'current' : '') . "' href='" . $base_url . Preferences::SPAM . "'>" . _x('Spam', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['spam']) . ')</span>' . "</a>",
            'bounce' => "<a class='" . ($view === Preferences::HARD_BOUNCE ? 'current' : '') . "' href='" . $base_url . Preferences::HARD_BOUNCE . "'>" . _x('Bounced', 'view', 'groundhogg') . ' <span class="count">(' . number_format_i18n($count['bounce']) . ')</span>' . "</a>"
        ));
    }

    /**
     * Prepares the list of items for displaying.
     * @global $wpdb \wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    function prepare_items()
    {

        $columns = $this->get_columns();
        $hidden = array(); // No hidden columns
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page = absint(get_url_var('limit', get_screen_option( 'per_page' ) ));
        $paged = $this->get_pagenum();
        $offset = $per_page * ($paged - 1);
        $search = get_url_var('s');
        $order = get_url_var('order', 'DESC');
        $orderby = get_url_var('orderby', 'time');

        $query = get_request_query(['optin_status' => [Preferences::CONFIRMED, Preferences::UNCONFIRMED, Preferences::WEEKLY, Preferences::MONTHLY]]);

        // Since unconfirmed is 0 (aside maybe we should change that) we need to specify we actually want it still.
        if (isset($_GET['optin_status'])) {
            $query['optin_status'] = absint($_GET['optin_status']);
        }

        // Sales person can only see their own contacts...
        if (current_user_is('sales_manager')) {
            $query['owner'] = get_current_user_id();
        }

        $query['number'] = $per_page;
        $query['offset'] = $offset;
        $query['orderby'] = $orderby;
        $query['search'] = $search;
        $query['order'] = $order;

        $this->query = $query;

        $c_query = new Contact_Query();
        $data = $c_query->query($query);

        set_transient('groundhogg_contact_query_args', $c_query->query_vars, HOUR_IN_SECONDS);

        $total = get_db('contacts')->count($query);

        $this->items = $data;

        // Add condition to be sure we don't divide by zero.
        // If $this->per_page is 0, then set total pages to 1.
        $total_pages = $per_page ? ceil((int)$total / (int)$per_page) : 1;

        $this->set_pagination_args(array(
            'total_items' => $total,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
        ));
    }

    /**
     * Generates and displays row action superlinks.
     *
     * @param $contact Contact Contact being acted upon.
     * @param string $column_name Current column name.
     * @param string $primary Primary column name.
     * @return string Row steps output for posts.
     */
    protected function handle_row_actions($contact, $column_name, $primary)
    {
        if ($primary !== $column_name) {
            return '';
        }

        $actions = array();
        $title = $contact->get_email();

        $actions['inline hide-if-no-js'] = sprintf(
            '<a href="#" class="editinline" aria-label="%s">%s</a>',
            /* translators: %s: title */
            esc_attr(sprintf(__('Quick edit &#8220;%s&#8221; inline'), $title)),
            __('Quick&nbsp;Edit')
        );

        $editUrl = admin_url('admin.php?page=gh_contacts&action=edit&contact=' . $contact->get_id());

        $actions['edit'] = sprintf(
            '<a href="%s" class="edit" aria-label="%s">%s</a>',
            /* translators: %s: title */
            $editUrl,
            esc_attr(__('Edit')),
            __('Edit')
        );

        if (absint(get_request_var('optin_status')) === Preferences::SPAM) {
            $actions['unspam'] = sprintf(
                '<a href="%s" class="unspam" aria-label="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=gh_contacts&contact=' . $contact->get_id() . '&action=unspam')),
                /* translators: %s: title */
                esc_attr(sprintf(_x('Mark %s as approved.', 'action', 'groundhogg'), $title)),
                __('Approve')
            );
        } else if (absint(get_request_var('optin_status')) === Preferences::HARD_BOUNCE) {
            $actions['unbounce'] = sprintf(
                '<a href="%s" class="unbounce" aria-label="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=gh_contacts&contact=' . $contact->get_id() . '&action=unbounce')),
                /* translators: %s: title */
                esc_attr(sprintf(_x('Mark %s as a valid email.', 'action', 'groundhogg'), $title)),
                _x('Valid Email', 'action', 'groundhogg')
            );
        } else {
            $actions['spam'] = sprintf(
                '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=gh_contacts&contact=' . $contact->get_id() . '&action=spam')),
                /* translators: %s: title */
                esc_attr(sprintf(_x('Mark %s as spam', 'action', 'groundhogg'), $title)),
                __('Spam')
            );
        }

        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
            wp_nonce_url(admin_url('admin.php?page=gh_contacts&contact=' . $contact->get_id() . '&action=delete')),
            /* translators: %s: title */
            esc_attr(sprintf(__('Delete &#8220;%s&#8221; permanently'), $title)),
            __('Delete')
        );

        return $this->row_actions(apply_filters('groundhogg_contact_row_actions', $actions, $contact, $column_name));
    }

    /**
     * @param object|Contact $contact
     * @param int $level
     */
    public function single_row($contact, $level = 0)
    {

        if (!$contact instanceof Contact) {
            $contact = Plugin::$instance->utils->get_contact(absint($contact->ID));
        }

        if (!$contact) {
            return;
        }

        ?>
        <tr id="contact-<?php echo $contact->get_id(); ?>">
            <?php $this->single_row_columns($contact); ?>
        </tr>
        <?php
    }


    /**
     * @param string $which
     */
    protected function extra_tablenav($which)
    {
        if ($which === 'top') {
            ?>
            <script>
                jQuery(function ($) {
                    $('#bulk-action-selector-top,#bulk-action-selector-bottom').on('change', function () {
                        var $bulk = $(this);
                        if ($bulk.val() === 'apply_tag' || $bulk.val() === 'remove_tag') {
                            $('.bulk-tag-action').removeClass('hidden');
                        } else {
                            $('.bulk-tag-action').addClass('hidden');
                        }
                    })
                });
            </script>
            <?php
        }
        ?>
        <div class="alignleft gh-actions bulk-tag-action hidden">
            <div style="width: 300px;display: inline-block;margin: 0 20px 5px 0"><?php echo Plugin::$instance->utils->html->tag_picker([
                    'name' => 'bulk_tags[]',
                    'id' => 'bulk_tags',
                    'class' => 'gh-tag-picker',
                    'data' => array(),
                    'selected' => array(),
                    'multiple' => true,
                    'placeholder' => __('Bulk Apply/Remove Tags', 'groundhogg'),
                    'tags' => true,
                ]); ?></div>
        </div>
        <div class="alignleft gh-actions">
            <a class="button action export-contacts"
               href="<?php echo Plugin::$instance->bulk_jobs->export_contacts->get_start_url($this->query); //todo uncomment
               ?>"><?php printf(_nx('Export %s contact', 'Export %s contacts', $this->get_pagination_arg('total_items'), 'action', 'groundhogg'), number_format_i18n($this->get_pagination_arg('total_items'))); ?></a>
        </div><?php
        do_action('groundhogg/admin/contacts/table/extra_tablenav', $this);
    }

    /**
     * Outputs the hidden row displayed when inline editing
     *
     * @global string $mode List table view mode.
     */
    public function inline_edit()
    {
        ?>
        <table style="display: none">
            <tbody id="inlineedit">
            <tr id="inline-edit"
                class="inline-edit-row inline-edit-row-contact quick-edit-row quick-edit-row-contact inline-edit-contact inline-editor"
                style="display: none">
                <td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
                    <fieldset class="inline-edit-col-left">
                        <legend class="inline-edit-legend"><?php echo __('Quick Edit'); ?></legend>
                        <div class="inline-edit-col">
                            <label>
                                <span class="title"><?php _e('Email'); ?></span>
                                <span class="input-text-wrap"><input type="text" name="email"
                                                                     class="cemail regular-text" value=""/></span>
                            </label>
                            <label>
                                <span class="title"><?php _e('First Name', 'groundhogg'); ?></span>
                                <span class="input-text-wrap"><input type="text" name="first_name"
                                                                     class="cfirst_name regular-text" value=""/></span>
                            </label>
                            <label>
                                <span class="title"><?php _e('Last Name', 'groundhogg'); ?></span>
                                <span class="input-text-wrap"><input type="text" name="last_name"
                                                                     class="clast_name regular-text" value=""/></span>
                            </label>
                            <label>
                                <span class="title"><?php _e('Owner', 'groundhogg'); ?></span>
                                <span class="input-text-wrap">
                                    <?php $args = array('show_option_none' => __('Select an owner'), 'id' => 'owner', 'name' => 'owner', 'role' => 'administrator', 'class' => 'cowner'); ?>
                                    <?php wp_dropdown_users($args) ?>
                                </span>
                            </label>
                            <label>
                                <input type="checkbox"
                                       name="unsubscribe"><?php _ex('Unsubscribe this contact.', 'action', 'groundhogg'); ?>
                            </label>
                        </div>
                    </fieldset>
                    <fieldset class="inline-edit-col-right">
                        <div class="inline-edit-col">
                            <label class="inline-edit-tags">
                                <span class="title"><?php _e('Tags'); ?></span>
                            </label>
                            <?php echo Plugin::$instance->utils->html->dropdown(array('id' => 'tags', 'name' => 'tags[]')); ?>
                        </div>
                    </fieldset>
                    <div class="submit inline-edit-save">
                        <button type="button" class="button cancel alignleft"><?php _e('Cancel'); ?></button>
                        <?php wp_nonce_field('inlineeditnonce', '_inline_edit'); ?>
                        <button type="button"
                                class="button button-primary save alignright"><?php _e('Update'); ?></button>
                        <span class="spinner"></span>
                        <br class="clear"/>
                        <div class="notice notice-error notice-alt inline hidden">
                            <p class="error"></p>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}