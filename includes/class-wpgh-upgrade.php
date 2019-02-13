<?php
/**
 * Upgrade
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 1.0.16
 */

class WPGH_Upgrade{

    /**
     * @var string the version which is registered in the DB
     */
    public $db_version;

    /**
     * @var string the version which is set by the plugin
     */
    public $current_version;

    /**
     * WPGH_Upgrade constructor.
     */
    public function __construct()
    {

        $this->db_version = get_option( 'wpgh_last_upgrade_version' );

        if ( ! $this->db_version ){
            $this->db_version = '1.0';
            update_option( 'wpgh_last_upgrade_version', $this->db_version );
        }

        $this->current_version = WPGH()->version;
        add_action( 'admin_init', array( $this, 'do_upgrades' ) );

    }

    /**
     * Check whether upgrades should happen or not.
     */
    public function do_upgrades()
    {
        /**
         * Check if the current version is larger than the version last checked by the upgrader
         */
        if ( version_compare( $this->current_version, $this->db_version, '>' ) ){
            $this->upgrade_path();
            update_option( 'wpgh_last_upgrade_version', $this->current_version );
        }

    }

    /**
     * This function is nice and all you have to do is just enter the version you want to update to.
     */
    private function upgrade_path()
    {
        $this->update_to_version( '1.0.16' );
        $this->update_to_version( '1.0.18.1' );
        $this->update_to_version( '1.0.20' );
    }

    /**
     * Takes the current version number and converts it to a function which can be clled to perform the upgrade requirements.
     *
     * @param $version string
     * @return bool|string
     */
    private function convert_version_to_function( $version )
    {

        $nums = explode( '.', $version );
        $func = sprintf( 'version_%s', implode( '_', $nums ) );

        if ( method_exists( $this, $func ) ){
            return $func;
        }

        return false;

    }

    private function update_to_version( $version )
    {
        /**
         * Check if the version we want to update to is greater than that of the db_version
         */
        if ( version_compare( $this->db_version, $version, '<' ) ){

            $func = $this->convert_version_to_function( $version );

            if ( method_exists( $this, $func ) ){
                call_user_func( array( $this, $func ) );
            }

            $this->db_version = $version;
            update_option( 'wpgh_last_upgrade_version', $this->db_version );

        }

    }

    /**
     * Perform the upgrades for version 1.0.16
     * apply tags for all the roles a user had to the associated contact
     */
    public function version_1_0_16()
    {
        /* convert users to contacts */
        $args = array(
            'fields' => 'all_with_meta'
        );

        $users = get_users( $args );

        /* @var $wp_user WP_User */
        foreach ( $users as $wp_user ) {

            $contact = wpgh_get_contact( $wp_user->ID, true );

            if ( $contact && $contact->exists() ){
                $contact->add_tag( wpgh_get_roles_pretty_names( $wp_user->roles ) );
            }

        }

    }

    /**
     * Add New Reports Roles to Administrator and Marketer
     */
    public function version_1_0_18_1()
    {
        global $wp_roles;

        //add new roles for reports
        $wp_roles->add_cap( 'administrator', 'view_reports' );
        $wp_roles->add_cap( 'administrator', 'export_reports' );
        $wp_roles->add_cap( 'marketer', 'view_reports' );
        $wp_roles->add_cap( 'marketer', 'export_reports' );

        //clear the cron event from the old 10 minute schedule and put it on the 5 minute schedule.
        wp_clear_scheduled_hook( 'wpgh_cron_event' );

    }

    /**
     * Remove old cron job and make way for better naming.
     */
    public function version_1_0_19_5()
    {
        wp_clear_scheduled_hook( 'wpgh_cron_event' );
    }

    /**
     * Upgrade Activity DB to include Email ID for tracking purposes.
     */
    public function version_1_0_20()
    {

        WPGH()->activity->create_table();

    }


}