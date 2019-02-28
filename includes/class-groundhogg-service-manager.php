<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2019-02-27
 * Time: 3:28 PM
 */

class Groundhogg_Service_Manager
{

    /**
     * API endpoint for Email & SMS service domain verification
     */
    const ENDPOINT = 'https://www.groundhogg.io/wp-json/gh/aws/v1/domain';

    public function __construct()
    {
        $should_listen = get_transient( 'gh_listen_for_connect' );
        if ( $should_listen && is_admin() ){
        	add_action( 'init', array( $this, 'connect_email_api' ) );
        }

        $should_verify = wpgh_is_option_enabled( 'gh_email_api_check_verify_status' );
        if ( $should_verify ){
        	add_action( 'admin_init', array( $this, 'setup_cron' ) );
        	add_action( 'groundhogg/service/verify_domain', array( $this, 'check_verification_status' ) );
        }
    }

	/**
	 * Setup a job to check the domain verification status.
	 */
	public function setup_cron()
	{
		if ( ! wp_next_scheduled( 'groundhogg/service/verify_domain' )  ){
			wp_schedule_event( time(), 'hourly' , 'groundhogg/service/verify_domain' );
		}
	}

    /**
     * Sends a request to Groundhogg.io to add this domain
     * Request returns a text record and a list of DKIM records
     */
    public function connect_email_api()
    {

        if ( ! is_admin()
             || $this->email_api_is_active()
             || ! key_exists( 'action', $_GET )
             || 'connect_to_gh' !== $_GET['action']
             || ! key_exists( 'token',  $_GET )
             || ! current_user_can( 'manage_options' )
        ){
            return;
        }

        $token  = sanitize_text_field( urldecode( $_GET[ 'token' ] ) );
        $gh_uid = intval( $_GET[ 'user_id' ] );

        $post = [
            'domain'    => site_url(),
            'user_id'   => $gh_uid,
            'token'     => $token
        ];

        $response = wp_remote_post( self::ENDPOINT, array( 'body' => $post ) );

        if ( is_wp_error( $response ) ){
            WPGH()->notices->add( $response->get_error_code(), $response->get_error_message(), 'error' );
            return;
        }

        $json = json_decode( wp_remote_retrieve_body( $response ) );

        if ( $this->is_json_error( $json ) ){
            WPGH()->notices->add( $this->is_json_error( $json ) );
            return;
        }

        //todo check if is correct params
        if ( ! isset( $json->dns_records ) ){
            WPGH()->notices->add( 'NO_DNS', 'Could not retrieve DNS records.', 'error' );
            return;
        }

	    /* Don't listen for connect anymore */
        delete_transient( 'gh_listen_for_connect' );

        /* Let WP know we should check for verification stats */
	    wpgh_update_option( 'gh_email_api_check_verify_status', 1 );

	    /* Update relevant options for further requests */
	    wpgh_update_option( 'gh_email_api_user_id', $gh_uid );
	    wpgh_update_option( 'gh_email_api_oauth_token', $token );

	    /* @type $json->dns_records array */
	    /*
	     * [
	     *   [
	     *    'name' => 'abc.'
	     *    'type' => 'CNAME'
	     *    'value' => 'aws.com'
	     *   ],
	     * ]
	     * */
	    wpgh_update_option( 'gh_email_api_dns_records', $json->dns_records );

    }

    public function is_json_error( $json ){
        if ( isset( $json->code ) && isset( $json->message ) & isset( $json->data ) ){
            return new WP_Error( $json->code, $json->message, $json->data );
        }

        return false;
    }

    /**
     * Send a request to Groundhogg.io to verify this domains status
     * Request provides domain status, and if verified an email token to use for sending
     */
    public function check_verification_status()
    {
        $token  = wpgh_get_option( 'gh_email_api_oauth_token' );
        $gh_uid = wpgh_get_option( 'gh_email_api_user_id' );

        $post = [
            'domain'    => site_url(),
            'user_id'   => $gh_uid,
            'token'     => $token
        ];

        $response = wp_remote_post( self::ENDPOINT, array( 'body' => $post ) );

        if ( is_wp_error( $response ) ){
            return;
        }

        $json = json_decode( wp_remote_retrieve_body( $response ) );

        /* If we got the token, set it and auto enable */
        if ( isset( $json->token ) ){
        	wpgh_update_option( 'gh_email_token', sanitize_text_field( $json->token ) );
        	wpgh_update_option( 'gh_send_with_gh_api', [ 'on' ] );

        	/* Domain is verified, no longer need to check verification */
        	wpgh_delete_option( 'gh_email_api_check_verify_status' );
	        wp_clear_scheduled_hook( 'groundhogg/service/verify_domain' );
        }
    }

	/**
	 * Returns whether or not the API is currently active.
	 *
	 * @return bool
	 */
    public function email_api_is_active()
    {
        return wpgh_is_option_enabled( 'gh_send_with_gh_api' );
    }

}