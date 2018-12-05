<?php
/**
 * Page gh_contacts
 *
 * This class registers the page with the admin menu, contains the private scripts to add contacts,
 * delete contacts, and manage contacts in the admin area
 *
 * There are several hooks you can use to add your own functionality to manage a contact in the default admin view.
 * The most relevant will likely be the following...
 *
 * add_action( 'wpgh_admin_update_contact_after', 'my_save_function' ); ($id)
 *
 * When saving custom information or doing something else. Runs after the admin saves a contact via the admin screen.
 *
 * @package     Admin
 * @subpackage  Admin/Contacts
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class WPGH_Contacts_Page
{

    /**
     * @var WPGH_Notices
     */
    public $notices;

    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'register' ) );

        add_action('wp_ajax_wpgh_inline_save_contacts', array( $this, 'save_inline' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

        if ( isset( $_GET['page'] ) && $_GET[ 'page' ] === 'gh_contacts' ){

            add_action( 'init' , array( $this, 'process_action' )  );

            $this->notices = WPGH()->notices;

        }
    }

    /**
     * Get the scripts in there
     */
    public function scripts()
    {

        if ( $this->get_action() === 'edit' || $this->get_action() === 'add'  ){
            wp_enqueue_style( 'contact-editor', WPGH_ASSETS_FOLDER . 'css/admin/contact-editor.css', array(), filemtime( WPGH_PLUGIN_DIR . 'assets/css/admin/contact-editor.css' ) );
        } else {
            wp_enqueue_style( 'select2' );
            wp_enqueue_script( 'select2' );
            wp_enqueue_script( 'wpgh-inline-edit-contacts', WPGH_ASSETS_FOLDER . '/js/admin/inline-edit-contacts.js' );
            wp_enqueue_style( 'wpgh-inline-edit-contacts', WPGH_ASSETS_FOLDER . '/css/admin/contacts.css'  );
        }
    }

    /* Register the page */
    public function register()
    {
        $page = add_submenu_page(
            'groundhogg',
            'Contacts',
            'Contacts',
            'view_contacts',
            'gh_contacts',
            array($this, 'page')
        );

        add_action("load-" . $page, array($this, 'help'));
    }

    /* help bar */
    public function help()
    {
        $screen = get_current_screen();

        $screen->add_help_tab(
            array(
                'id' => 'gh_overview',
                'title' => __('Overview'),
                'content' => '<p>' . __( "This is where you can manage and view your contacts. Click the quick edit to quickly change contact details.", 'groundhogg' ) . '</p>'
            )
        );

        $screen->add_help_tab(
            array(
                'id' => 'gh_edit',
                'title' => __('Editing'),
                'content' => '<p>' . __( "While editing a contact you can modify any of their personal information. There are several points of interest...", 'groundhogg' ) . '</p>'
                    . '<ul> '
                    . '<li>' . __( 'Manually unsubscribe a contact by checking the "mark as unsubscribed" button.', 'groundhogg' ) . '</li>'
                    . '<li>' . __( 'Make sure your in compliance by ensuring the terms of agreement and GDPR consent are both checked under the compliance section.', 'groundhogg' ) . '</li>'
                    . '<li>' . __( 'View the origin of the contact by looking at the lead source field.', 'groundhogg' ) . '</li>'
                    . '<li>' . __( 'Add or remove custom information about the contact by enabling the "Edit Meta" section. Each meta also includes a replacement code to include it in an email.', 'groundhogg' ) . '</li>'
                    . '<li>' . __( 'Re-run or cancel events for this contact by viewing the "Upcoming Events" or "Recent History" Section', 'groundhogg' ) . '</li>'
                    . '<li>' . __( 'Monitor their engagement by looking in the "Recent Email History" section.', 'groundhogg' ) . '</li>'
                    . '</ul>'
            )
        );
    }


    /**
     * Get the affected contacts
     *
     * @return array|bool
     */
    private function get_contacts()
    {
        $contacts = isset( $_REQUEST['contact'] ) ? $_REQUEST['contact'] : null;

        if ( ! $contacts )
            return false;

        return is_array( $contacts )? array_map( 'intval', $contacts ) : array( intval( $contacts ) );
    }

    /**
     * Get the current action
     *
     * @return bool
     */
    private function get_action()
    {
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

        return false;
    }

    /**
     * Get the previous action
     *
     * @return mixed
     */
    private function get_previous_action()
    {
        $action = get_transient( 'gh_last_action' );

        delete_transient( 'gh_last_action' );

        return $action;
    }

    /**
     * Get the screen title
     */
    private function get_title()
    {
        switch ( $this->get_action() ){
            case 'add':
                _e( 'Add Contact' , 'groundhogg' );
                break;
            case 'edit':
                _e( 'Edit Contact' , 'groundhogg' );
                break;
            case 'search':
                _e( 'Search Contacts' , 'groundhogg' );
                break;
            default:
                _e( 'Contacts', 'groundhogg' );
        }
    }

    /**
     * Process the given action
     */
    public function process_action()
    {

        if ( ! $this->get_action() || ! $this->verify_action() )
            return;

        $base_url = remove_query_arg( array( '_wpnonce', 'action' ), wp_get_referer() );

        switch ( $this->get_action() )
        {
            case 'add':

                if ( ! current_user_can( 'add_contacts' ) ){
                    wp_die( WPGH()->roles->error( 'add_contacts' ) );
                }

                if ( ! empty( $_POST ) )
                {
                    $this->add_contact();
                }

                break;

            case 'edit':

                if ( ! current_user_can( 'edit_contacts' ) ){
                    wp_die( WPGH()->roles->error( 'edit_contacts' ) );
                }

                if ( ! empty( $_POST ) ){

                    $this->update_contact();

                }

                break;

            case 'spam':

                if ( ! current_user_can( 'edit_contacts' ) ){
                    wp_die( WPGH()->roles->error( 'edit_contacts' ) );
                }

                foreach ( $this->get_contacts() as $id ) {

                    $contact = new WPGH_Contact( $id );
                    $args = array( 'optin_status' => WPGH_SPAM );
                    $contact->update( $args );

                    $ip_address = $contact->get_meta('ip_address' );

                    if ( $ip_address ) {
                        $blacklist = wpgh_get_option( 'blacklist_keys' );
                        $blacklist .= "\n" . $ip_address;
                        $blacklist = sanitize_textarea_field( $blacklist );
                        update_option( 'blacklist_keys', $blacklist );
                    }

                    do_action( 'wpgh_contact_marked_as_spam', $id );
                }

	            $this->notices->add(
		            esc_attr( 'spammed' ),
		            sprintf( "%s %d %s",
			            __( 'Marked', 'groundhogg' ),
			            count( $this->get_contacts() ),
			            __( 'contacts as spam', 'groundhogg' ) ),
		            'success'
	            );

                do_action( 'wpgh_spam_contacts' );

                break;

            case 'delete':

                if ( ! current_user_can( 'delete_contacts' ) ){
                    wp_die( WPGH()->roles->error( 'delete_contacts' ) );
                }

                foreach ( $this->get_contacts() as $id ){

                    do_action( 'wpgh_pre_admin_delete_contact', $id );

                    $result = WPGH()->contacts->delete( $id );

                    if ( $result ){
                        do_action( 'wpgh_post_admin_delete_contact', $id );
                    }

                }

	            $this->notices->add(
		            esc_attr( 'deleted' ),
		            sprintf( "%s %d %s",
			            __( 'Deleted', 'groundhogg' ),
			            count( $this->get_contacts() ),
			            __( 'Contacts', 'groundhogg' ) ),
		            'success'
	            );

                do_action( 'wpgh_delete_contacts' );

                break;

            case 'unspam':

                if ( ! current_user_can( 'edit_contacts' ) ){
                    wp_die( WPGH()->roles->error( 'edit_contacts' ) );
                }

                foreach ( $this->get_contacts() as $id ) {
                    $contact = new WPGH_Contact( $id );
                    $args = array( 'optin_status' => WPGH_UNCONFIRMED );
                    $contact->update( $args );
                }

	            $this->notices->add(
		            esc_attr( 'unspam' ),
		            sprintf( "%s %d %s",
			            __( 'Approved', 'groundhogg' ),
			            count( $this->get_contacts() ),
			            __( 'Contacts', 'groundhogg' ) ),
		            'success'
	            );

                do_action( 'wpgh_unspam_contacts' );

                break;

        }

        set_transient( 'gh_last_action', $this->get_action(), 30 );

        if ( $this->get_action() === 'edit' || $this->get_action() === 'add' ){
            return true;
        }

        $base_url = add_query_arg( 'ids', urlencode( implode( ',', $this->get_contacts() ) ), $base_url );

        wp_redirect( $base_url );
        die();
    }

    /**
     * Create a contact via the admin area
     */
    private function add_contact()
    {
        if ( ! current_user_can( 'add_contacts' ) ){
            wp_die( WPGH()->roles->error( 'add_contacts' ) );
        }

        do_action( 'wpgh_admin_add_contact_before' );

        if ( ! isset( $_POST['email'] ) ){
            $this->notices->add( 'NO_EMAIL', __( "Please enter a valid email address", 'groundhogg' ), 'error' );
            return;
        }

        if ( isset( $_POST[ 'first_name' ] ) )
            $args['first_name'] = sanitize_text_field( $_POST[ 'first_name' ] );

        if ( isset( $_POST[ 'last_name' ] ) )
            $args['last_name'] = sanitize_text_field( $_POST[ 'last_name' ] );

        if ( isset( $_POST[ 'email' ] ) ){

            $email = sanitize_email( $_POST[ 'email' ] );

            if ( ! WPGH()->contacts->exists( $email ) ){
                $args[ 'email' ] = $email;
            } else {
                $this->notices->add( 'email_exists', sprintf( __( 'Sorry, the email %s already belongs to another contact.', 'groundhogg' ), $email ), 'error' );
                return;
            }

        }

        if ( ! is_email( $args['email'] ) ){
            $this->notices->add( 'BAD_EMAIL', __( "Please enter a valid email address", 'groundhogg' ), 'error' );
            return;
        }

        if ( isset( $_POST['owner_id'] ) ){
            $args[ 'owner_id' ] = intval( $_POST['owner_id'] );
        }

        $id = WPGH()->contacts->add( $args );

        $contact = new WPGH_Contact( $id );

        if ( isset( $_POST[ 'primary_phone' ] ) ){
            $contact->update_meta( 'primary_phone', sanitize_text_field( $_POST[ 'primary_phone' ] ) );
        }

        if ( isset( $_POST[ 'primary_phone_extension' ] ) ){
            $contact->update_meta( 'primary_phone_extension', sanitize_text_field( $_POST[ 'primary_phone_extension' ] ) );
        }

        if ( isset( $_POST[ 'notes' ] ) ){
            $contact->add_note( $_POST[ 'notes' ] );
        }

        if ( isset( $_POST[ 'tags' ] ) ) {
            $contact->add_tag( $_POST[ 'tags' ] );
        }

        $this->notices->add( 'created', __( "Contact created!", 'groundhogg' ), 'success' );

        do_action( 'wpgh_admin_add_contact_after', $id );

        wp_redirect( admin_url( 'admin.php?page=gh_contacts&action=edit&contact=' . $id ) );
        die();
    }

    /**
     * Update the contact via the admin screen
     */
    private function update_contact()
    {

        if ( ! current_user_can( 'edit_contacts' ) ){
            wp_die( WPGH()->roles->error( 'edit_contacts' ) );
        }

        $id = intval( $_GET[ 'contact' ] );

        if ( ! $id ){
            return;
        }

        $contact = new WPGH_Contact( $id );

        do_action( 'wpgh_admin_update_contact_before', $id );

        //todo security check

        /* Save the meta first... as actual fields might overwrite it later... */
        $cur_meta = WPGH()->contact_meta->get_meta( $id );

        if ( isset( $_POST[ 'meta' ] ) ){
            $posted_meta = $_POST[ 'meta' ];

            foreach ( $cur_meta as $key => $value ){

                if ( isset( $posted_meta[ $key ] ) ){

                    $contact->update_meta( $key, sanitize_text_field( $posted_meta[ $key ] ) );

                } else {

                    $contact->delete_meta( $key );

                }
            }
        }

        /* add new meta */
        if ( isset( $_POST[ 'newmetakey' ] ) && isset( $_POST[ 'newmetavalue' ] ) ){

            $new_meta_keys = $_POST[ 'newmetakey' ];
            $new_meta_vals = $_POST[ 'newmetavalue' ];

            foreach ( $new_meta_keys as $i => $new_meta_key ){
                if ( strpos( $new_meta_vals[ $i ], PHP_EOL  ) !== false ){
                    $contact->update_meta( sanitize_key( $new_meta_key ), sanitize_textarea_field( stripslashes( $new_meta_vals[ $i ] ) ) );
                } else {
                    $contact->update_meta( sanitize_key( $new_meta_key ), sanitize_text_field( stripslashes( $new_meta_vals[ $i ] ) ) );
                }
            }

        }

        /* Update Main Contact Information */
        $args = array();

        if ( isset( $_POST[ 'unsubscribe' ] ) ) {

            $args[ 'optin_status' ] = WPGH_UNSUBSCRIBED;

            do_action( 'wpgh_preference_unsubscribe', $id );

            $this->notices->add(
                esc_attr( 'unsubscribed' ),
                __( 'This contact will no longer receive email communication', 'groundhogg' ),
                'info'
            );
        }

        if ( isset( $_POST[ 'email' ] ) ) {

            $email = sanitize_email( $_POST[ 'email' ] );

            //check if it's the current email address.
            if ( $contact->email !== $email ){

                //check if another email address like it exists...
                if ( ! WPGH()->contacts->exists( $email ) ){
                    $args[ 'email' ] = $email;

                    //update new optin status to unconfirmed
                    $args[ 'optin_status' ] = WPGH_UNCONFIRMED;
                    $this->notices->add( 'optin_status_updated', sprintf( __( 'The email address of this contact has been changed to %s. Their optin status has been changed to [unconfirmed] to reflect the change as well.', $email ), 'groundhogg' ), 'error' );

                } else {

                    $this->notices->add( 'email_exists', sprintf( __( 'Sorry, the email %s already belongs to another contact.', 'groundhogg' ), $email ), 'error' );

                }

            }

        }

        if ( isset( $_POST['first_name'] ) ){
            $args[ 'first_name' ] = sanitize_text_field( $_POST['first_name'] );
        }

        if ( isset( $_POST['last_name'] ) ){
            $args[ 'last_name' ] = sanitize_text_field( $_POST['last_name'] );
        }

        if ( isset( $_POST['owner_id'] ) ){
            $args[ 'owner_id' ] = intval( $_POST['owner_id'] );
        }

        $args = array_map( 'stripslashes', $args );
        $contact->update( $args );

        if ( isset( $_POST['primary_phone'] ) ){
            $contact->update_meta( 'primary_phone', sanitize_text_field( $_POST['primary_phone'] ) );
        }

        if ( isset( $_POST['primary_phone_extension'] ) ){
            $contact->update_meta( 'primary_phone_extension', sanitize_text_field( $_POST['primary_phone_extension'] ) );
        }

        if ( isset( $_POST['street_address_1'] ) ){
            $contact->update_meta( 'street_address_1', sanitize_text_field( stripslashes( $_POST['street_address_1'] ) ) );
        }

        if ( isset( $_POST['street_address_2'] ) ){
            $contact->update_meta( 'street_address_2', sanitize_text_field( stripslashes( $_POST['street_address_2'] ) ) );
        }
        if ( isset( $_POST['city'] ) ){
            $contact->update_meta( 'city', sanitize_text_field( stripslashes( $_POST['city'] ) ) );
        }

        if ( isset( $_POST['postal_zip'] ) ){
            $contact->update_meta( 'postal_zip', sanitize_text_field( stripslashes( $_POST['postal_zip'] ) ) );
        }

        if ( isset( $_POST['region'] ) ){
            $contact->update_meta( 'region', sanitize_text_field( stripslashes( $_POST['region'] ) ) );
        }

        if ( isset( $_POST['country'] ) ){
            $contact->update_meta( 'country', sanitize_text_field( stripslashes( $_POST['country'] ) ) );
        }

        if ( isset( $_POST[ 'notes' ] ) ){
            $contact->update_meta( 'notes', sanitize_textarea_field( stripslashes( $_POST['notes'] ) ) );
        }

        if ( isset( $_POST[ 'lead_source' ] ) ){
            $contact->update_meta( 'lead_source', esc_url_raw( $_POST['lead_source'] ) );
        }

        if ( isset( $_POST[ 'source_page' ] ) ){
            $contact->update_meta( 'source_page', esc_url_raw( $_POST['source_page'] ) );
        }

        if ( isset( $_POST[ 'tags' ] ) ){

            $tags = WPGH()->tags->validate( $_POST['tags' ] );

            $cur_tags = $contact->tags;
            $new_tags = $tags;

            $delete_tags = array_diff( $cur_tags, $new_tags );
            if ( ! empty( $delete_tags ) ) {
                $contact->remove_tag( $delete_tags );
            }

            $add_tags = array_diff( $new_tags, $cur_tags );
            if ( ! empty( $add_tags ) ){

//                print_r( $add_tags );

                $result = $contact->add_tag( $add_tags );

                if ( ! $result ){
                    $this->notices->add( 'bad-tag', 'Hmm, looks like we couldn\'t add the new tags...' );
                }
            }
        }

        if ( isset( $_POST[ 'send_email' ] ) && isset( $_POST[ 'email_id' ] ) && current_user_can( 'send_emails' ) ){

            $mail = new WPGH_Email( intval( $_POST[ 'email_id' ] ) );
            if ( $mail->send( $contact ) ){
                $this->notices->add( 'sent', __( "Email Sent!", 'groundhogg' ), 'info' );
            }
        }

        if ( isset( $_POST[ 'start_funnel' ] ) && isset( $_POST[ 'add_contacts_to_funnel_step_picker' ] ) && current_user_can( 'edit_contacts' ) ){

            $step = new WPGH_Step( intval( $_POST[ 'add_contacts_to_funnel_step_picker' ] ) );
            if ( $step->enqueue( $contact ) ){
                $this->notices->add( 'started', __( "Contact added to funnel.", 'groundhogg' ), 'info' );
            }
        }

        $this->notices->add( 'update', __( "Contact updated!", 'groundhogg' ), 'success' );

        do_action( 'wpgh_admin_update_contact_after', $id );
    }

    /**
     * Save the contact during inline edit
     */
    public function save_inline()
    {

        if ( ! wp_doing_ajax() ){
            wp_die( 'should not be calling this function' );
        }

        if ( ! current_user_can( 'edit_contacts' ) ){
            wp_die( WPGH()->roles->error( 'edit_contacts' ) );
        }

        //todo security check

        $id = (int) $_POST['ID'];

        $contact = new WPGH_Contact( $id );

        do_action( 'wpgh_inline_update_contact_before', $id );

        $email = sanitize_email( $_POST['email'] );

        $args[ 'first_name' ] = sanitize_text_field( $_POST['first_name'] );
        $args[ 'last_name' ] = sanitize_text_field( $_POST['last_name'] );
        $args[ 'owner_id' ] = intval( $_POST['owner' ] );

        $err = array();

        if( !$email ) {
            $err[] = __( 'Email can not be blank.', 'groundhogg' );
        } else if ( ! is_email( $email ) ) {
            $err[] = __( 'Invalid email address.', 'groundhogg' );
        }

        //check if it's the current email address.
        if ( $contact->email !== $email ) {

            //check if another email address like it exists...
            if ( ! WPGH()->contacts->exists( $email ) ) {
                $args['email'] = $email;

                //update new optin status to unconfirmed
                $args['optin_status'] = WPGH_UNCONFIRMED;
                $err[] = sprintf(__('The email address of this contact has been changed to %s. Their optin status has been changed to [unconfirmed] to reflect the change as well.', $email), 'groundhogg');

            } else {

                $err[] =  sprintf(__('Sorry, the email %s already belongs to another contact.', 'groundhogg'), $email);

            }

        }

        if( !$args[ 'first_name' ] ) {
            $err[] = __( 'First name can not be blank.', 'groundhogg' );
        }

        if( $err ) {
            echo implode(', ', $err);
            exit;
        }

        $args = array_map( 'stripslashes', $args );

        $contact->update( $args );

        $tags = WPGH()->tags->validate( $_POST['tags' ] );

        $cur_tags = $contact->tags;
        $new_tags = $tags;

        $delete_tags = array_diff( $cur_tags, $new_tags );
        if ( ! empty( $delete_tags ) ) {
            $contact->remove_tag( $delete_tags );
        }

        $add_tags = array_diff( $new_tags, $cur_tags );
        if ( ! empty( $add_tags ) ){
            $contact->add_tag( $add_tags );

        }

        do_action( 'wpgh_inline_update_contact_after', $id );

        if ( ! class_exists( 'WPGH_Contacts_Table' ) ) {
            include_once 'class-wpgh-contacts-table.php';
        }

        $contactTable = new WPGH_Contacts_Table;
        $contactTable->single_row( WPGH()->contacts->get( $id ) );

        wp_die();
    }

    /**
     * Verify that the current user can perform the action
     *
     * @return bool
     */
    function verify_action()
    {
        if ( ! isset( $_REQUEST['_wpnonce'] ) && ! isset( $_REQUEST[ '_edit_contact_nonce' ] ) )
            return false;

        return wp_verify_nonce( $_REQUEST[ '_wpnonce' ] ) || wp_verify_nonce( $_REQUEST[ '_wpnonce' ], $this->get_action() ) || wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'bulk-contacts' ) || wp_verify_nonce( $_REQUEST[ '_edit_contact_nonce' ], $this->get_action() ) ;
    }

    /**
     * Display the contact table
     */
    function table()
    {

        if ( ! current_user_can( 'view_contacts' ) ){
            wp_die( WPGH()->roles->error( 'view_contacts' ) );
        }

        if ( ! class_exists( 'WPGH_Contacts_Table' ) ){
            include dirname(__FILE__) . '/class-wpgh-contacts-table.php';
        }

        $contacts_table = new WPGH_Contacts_Table();

        $contacts_table->views(); ?>
        <form method="post" class="search-form wp-clearfix" >
            <!-- search form -->
            <p class="search-box">
                <label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Contacts ', 'groundhogg'); ?>:</label>
                <input type="search" id="post-search-input" name="s" value="">
                <input type="submit" id="search-submit" class="button" value="<?php _e( 'Search Contacts ', 'groundhogg'); ?>">
            </p>
            <?php $contacts_table->prepare_items(); ?>
            <?php $contacts_table->display(); ?>
            <?php
            if ( $contacts_table->has_items())
                $contacts_table->inline_edit();
            ?>
        </form>

        <?php
    }

    /**
     * Display the edit screen
     */
    function edit()
    {

        if ( ! current_user_can( 'view_contacts' ) ){
            wp_die( WPGH()->roles->error( 'view_contacts' ) );
        }

        include dirname( __FILE__ ) . '/contact-editor.php';

    }

    /**
     * Display the add screen
     */
    function add()
    {
        if ( ! current_user_can( 'add_contacts' ) ){
            wp_die( WPGH()->roles->error( 'add_contacts' ) );
        }

        include dirname( __FILE__ ) . '/add-contact.php';
    }

    function search()
    {
        if ( ! current_user_can( 'view_contacts' ) ){
            wp_die( WPGH()->roles->error( 'view_contacts' ) );
        }

        include dirname( __FILE__ ) . '/search.php';
    }

    /**
     * Display the title and dependent action include the appropriate page content
     */
    function page()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php $this->get_title(); ?></h1><a class="page-title-action aria-button-if-js" href="<?php echo admin_url( 'admin.php?page=gh_contacts&action=add' ); ?>"><?php _e( 'Add New' ); ?></a>
            <?php $this->notices->notices(); ?>
            <hr class="wp-header-end">
            <?php switch ( $this->get_action() ){
                case 'add':
                    $this->add();
                    break;
                case 'edit':
                    $this->edit();
                    break;
                case 'search':
                    $this->search();
                    break;
                default:
                    $this->table();
            } ?>
        </div>
        <?php
    }
}