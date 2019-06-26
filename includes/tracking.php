<?php
namespace Groundhogg;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tracking
 *
 * Maintain information about the contact, events, funnels, etc...
 * Uses cookies.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */
class Tracking
{
    /**
     * This is a cookie that will be in the contact's browser
     */
    const TRACKING_COOKIE = 'groundhogg-tracking';
    const LEAD_SOURCE_COOKIE = 'groundhogg-lead-source';

    /**
     * Cookie expiry time in days
     *
     * @var int
     */
    const COOKIE_EXPIRY = 14;

    /**
     * Array representing the various elements of the tracking cookie.
     *
     * @var array
     */
    protected $cookie = [];


    /**
     * @var string the referring url
     */
    protected $lead_source = '';

    /**
     * Holds any UTM params.
     *
     * @var string[]
     */
    protected $utm = [];

    /**
     * Two vars to tell which is the current action being taken by the contact
     *
     * @var bool
     * @var bool
     */
    private $doing_open = false;
    private $doing_click = false;
    private $doing_confirmation = false;

    /**
     * WPGH_Tracking constructor.
     *
     *
     * Look at the current URL and depending on that setup the vars and enqueue the appropriate elements if any
     */
    public function __construct()
    {
        //Actions when cookie should be destroyed
        add_action( 'groundhogg/preferences/erase_profile', [ $this, 'stop_tracking' ] );

        // Actions which build the tracking cookie.
        add_action( 'wp_login', [ $this, 'wp_login' ], 10, 2 );

        add_action( 'after_setup_theme', [ $this, 'deconstruct_tracking_cookie' ], 1 );
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_action( 'init', [ $this, 'parse_utm' ] );

        add_filter( 'request', [ $this, 'parse_request' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );

//        add_action( 'template_redirect', [ $this, 'fix_tracking_ssl' ] );
        add_action( 'template_redirect', [ $this, 'template_redirect'] );

        add_action( 'groundhogg/after_form_submit', [ $this, 'form_filled' ], 10, 1 );

    }

    /**
     * Adds the rewrite rules for tracking.
     */
    public function add_rewrite_rules()
    {
        // No Ref attribute
        // OLD STRUCTURE
        // TODO implement backwards compatibility.
        add_rewrite_rule(
            '^gh-tracking/([^/]*)/([^/]*)/?$',
            'index.php?pagenow=tracking&tracking_via=$matches[1]&tracking_action=$matches[2]$backwards_compat=true',
            'bottom'
        );

        // New tracking structure.
        // With Ref attribute
        add_rewrite_rule(
            '^gh/tracking/([^/]*)/([^/]*)/u/([^/]*)/e/([^/]*)/i/([^/]*)/ref/([^/]*)/?$',
            'index.php?pagenow=tracking&tracking_via=$matches[1]&tracking_action=$matches[2]&contact_id=$matches[3]&event_id=$matches[4]&email_id=$matches[5]&target_url=$matches[6]',
            'top'
        );

        // New tracking structure.
        // No Ref attribute
        add_rewrite_rule(
            '^gh/tracking/([^/]*)/([^/]*)/u/([^/]*)/e/([^/]*)/i/([^/]*)/?$',
            'index.php?pagenow=tracking&tracking_via=$matches[1]&tracking_action=$matches[2]&contact_id=$matches[3]&event_id=$matches[4]&email_id=$matches[5]',
            'top'
        );
    }

    /**
     * Add the query vars.
     *
     * @param $vars
     * @return array
     */
    public function add_query_vars( $vars )
    {
        // Tracking vars
        $vars[] = 'pagenow';
        $vars[] = 'tracking_via';
        $vars[] = 'tracking_action';
        $vars[] = 'contact_id';
        $vars[] = 'event_id';
        $vars[] = 'email_id';
        $vars[] = 'target_url';

        return $vars;
    }

    /**
     * @param $array
     * @param $key
     * @param $func
     */
    public function map_query_var(&$array, $key, $func )
    {
        if ( ! function_exists( $func ) ){
            return;
        }

        if ( isset_not_empty( $array, $key ) ){
            $array[ $key ] = call_user_func( $func, $array[ $key ] );
        }
    }

    /**
     * Parse the Ids from hex to int.
     *
     * @param $vars array
     * @return array
     */
    public function parse_request( $vars )
    {

//        var_dump( $vars );
//        wp_die();

        if ( get_array_var( $vars, 'pagenow' ) === 'tracking' ){
            $this->map_query_var( $vars, 'contact_id', 'hexdec' );
            $this->map_query_var( $vars, 'event_id', 'hexdec' );
            $this->map_query_var( $vars, 'email_id', 'hexdec' );

            //Decode & Decode
            $this->map_query_var( $vars, 'target_url', 'urldecode' );
            $this->map_query_var( $vars, 'target_url', 'base64_decode' );
        }

        return $vars;
    }

    /**
     * Do a tracking redirect during the template_redirect hook
     */
    public function template_redirect()
    {

        $pagenow = get_query_var( 'pagenow' );

        if ( $pagenow !== 'tracking' ){
            return;
        }

        $tracking_via = get_query_var( 'tracking_via' );
        $tracking_action = get_query_var( 'tracking_action' );

        $contact_id = absint( get_query_var( 'contact_id' ) );
        $email_id   = absint( get_query_var( 'email_id' ) );
        $event_id   = absint( get_query_var( 'event_id' ) );
        $target_url = get_query_var( 'target_url' );

        // Add the tracking cookie params.
        $this->add_tracking_cookie_param( 'contact_id', $contact_id );
        $this->add_tracking_cookie_param( 'email_id', $email_id );
        $this->add_tracking_cookie_param( 'event_id', $event_id );
        $this->add_tracking_cookie_param( 'source', $tracking_via );
        $this->add_tracking_cookie_param( 'action', $tracking_action );

        switch ( $tracking_via ){
            case 'email':
                switch ( $tracking_action ){
                    case 'open':
                        $this->doing_open = true;
                        $this->email_opened();
                        break;
                    case 'click':
                        $this->doing_click = true;
                        $this->email_link_clicked( $target_url );
                        break;
                }

                break;
        }

        $this->build_tracking_cookie();
        die();
    }

    /**
     * Add a param to te tracking cookie.
     *
     * @param $key
     * @param $value
     */
    public function add_tracking_cookie_param( $key, $value )
    {
        $this->cookie[ $key ] = $value;
    }

    /**
     * Remove a param from the tracking cookie.
     *
     * @param $key
     * @return void
     */
    public function remove_tracking_cookie_param( $key )
    {
        unset( $this->cookie[ $key ] );
    }

    /**
     * Get a param from the tracking cookie.
     *
     * @param $key
     * @param bool $default
     * @return bool|mixed
     */
    public function get_tracking_cookie_param( $key, $default=false )
    {
        if ( isset_not_empty( $this->cookie, $key ) ){
            return $this->cookie[ $key ];
        }

        return $default;
    }

    /**
     * @return string
     */
    public function get_leadsource()
    {
        return '';
    }

    /**
     * Get the contact which is currently being tracked.
     *
     * @return Contact|false
     */
    public function get_current_contact()
    {
        $id = absint( $this->get_tracking_cookie_param( 'contact_id' ) );

        $by_uid = false;

        // Override if the user is logged in.
        if ( function_exists( 'is_user_logged_in' ) ){
            if ( is_user_logged_in() ){
                $id = get_current_user_id();
                $by_uid = true;
            }
        }

        return Plugin::$instance->utils->get_contact( $id, $by_uid );
    }

    /**
     * @return int
     */
    public function get_current_contact_id()
    {
        $id = absint( $this->get_tracking_cookie_param( 'contact_id' ) );
        return $id;
    }

    /**
     * Get the contact which is currently being tracked.
     *
     * @return Event|false
     */
    public function get_current_event()
    {
        $id = absint( $this->get_tracking_cookie_param( 'event_id' ) );
        return Plugin::$instance->utils->get_event( $id );
    }

    /**
     * For some reason emails are being sent out with http instead of https...
     * Redirect to ssl if https is in the url.
     */
    public function fix_tracking_ssl()
    {
        $site = get_option( 'siteurl' );
        if ( strpos( $site, 'https://' ) !== false && ! is_ssl() ){
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            wp_safe_redirect( $actual_link );
            die();
        }
    }

    /**
     * If the tracking cookie exists, deconstruct it into parts
     */
    public function deconstruct_tracking_cookie()
    {
        if ( ! isset_not_empty( $_COOKIE, self::TRACKING_COOKIE ) ){
            return;
        }

        $enc_cookie = $_COOKIE[ self::TRACKING_COOKIE ];
        $dec_cookie = Plugin::$instance->utils->encrypt_decrypt( $enc_cookie, 'd' );
        $cookie_vars = json_decode( $dec_cookie, true );
        $cookie_vars = apply_filters( 'groundhogg/tracking/get_cookie_vars', $cookie_vars );
        $this->cookie = $cookie_vars;
    }

    /**
     * Build a tracking cookie based on the available information.
     */
    protected function build_tracking_cookie()
    {
        $cookie_vars = apply_filters( 'groundhogg/tracking/set_cookie_vars', $this->cookie );

        $cookie = wp_json_encode( $cookie_vars );
        $cookie = Plugin::$instance->utils->encrypt_decrypt( $cookie, 'e' );

        $expiry = apply_filters( 'groundhogg/tracking/cookie_expiry', self::COOKIE_EXPIRY ) * DAY_IN_SECONDS;

        return setcookie(
            self::TRACKING_COOKIE,
            $cookie,
            time() + $expiry,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl()
        );
    }

    /**
     * If we want to start tracking a new contact we can overwrite any current cookie
     * or just start with a new cookie by calling this function.
     *
     * @param $contact Contact
     * @param $source string
     */
    public function start_tracking( $contact, $source = 'manual' )
    {
        if ( ! $contact ){
            return;
        }

        // Remove any previous tracking...
        $this->cookie = [];

        $this->add_tracking_cookie_param( 'contact_id', $contact->get_id() );
        $this->add_tracking_cookie_param( 'source', $source );

        // Rebuild the cookie.
        $this->build_tracking_cookie();
    }

    /**
     * Delete the current tracking cookie.
     */
    public function stop_tracking()
    {
        if (isset($_COOKIE[ self::TRACKING_COOKIE ] ) ) {
            unset($_COOKIE[ self::TRACKING_COOKIE ]);
            setcookie(self::TRACKING_COOKIE, null, time() - DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        }
    }

    /**
     * Setup the tracking cookie vars for when a user logs in.
     *
     * @param $user_login string
     * @param $user \WP_User
     */
    public function wp_login( $user_login, $user )
    {
        $this->add_tracking_cookie_param( 'user_login', $user_login );
        $this->add_tracking_cookie_param( 'user_id', $user->ID );
        $this->add_tracking_cookie_param( 'source', 'login' );

        $contact = Plugin::$instance->utils->get_contact( $user->user_email );

        if ( $contact ){
            $this->add_tracking_cookie_param( 'contact_id', $contact->get_id() );
        }

        $this->build_tracking_cookie();
    }

    /**
     * IF the URL contains UTM variables save them to meta.
     *
     * @return void
     */
    public function parse_utm()
    {
        if ( ! $this->get_current_contact() ){
            return;
        }

        $utm_defaults = array(
            'utm_campaign' => '',
            'utm_content'  => '',
            'utm_source'   => '',
            'utm_medium'   => '',
            'utm_term'     => '',
        );

        $utm = array_intersect_key( $_GET, $utm_defaults );

        foreach ( $utm as $utm_var => $utm_val ){
            if ( ! empty( $utm_val ) ){
                $this->get_current_contact()->update_meta(
                    $utm_var,
                    sanitize_text_field( $utm_val )
                );
            }
        }
    }

    /**
     * When an email is opened this function will be called at the INIT stage
     */
    public function email_opened()
    {
        $event_id = $this->get_tracking_cookie_param( 'event_id' );
        $event = Plugin::$instance->utils->get_event( $event_id );

        if ( ! $event || ! $event->exists() ){
            if ( $this->doing_open ){
                /* thanks for coming! */
                wp_redirect( GROUNDHOGG_ASSETS_URL . 'images/email-open.png' );
                die();
            } else {
                return;
            }
        }

        $args = array(
            'timestamp'     => time(),
            'contact_id'    => $event->get_contact_id(),
            'funnel_id'     => $event->get_funnel_id(),
            'step_id'       => $event->get_step_id(),
            'email_id'      => $this->get_tracking_cookie_param( 'email_id', 0 ),
            'activity_type' => 'email_opened',
            'event_id'      => $event->get_id(),
            'referer'       => ''
        );

        if ( Plugin::$instance->dbs->get_db( 'activity' )->add( $args ) ){
            do_action( 'groundhogg/tracking/email/opened', $this );
        }

	    /* only fire if actually doing an open as this may be called by the email_link_clicked method */
	    if ( $this->doing_open ){
		    /* thanks for coming! */
		    wp_redirect( GROUNDHOGG_ASSETS_URL . 'images/email-open.png' );
		    die();
	    }
    }

    /**
     * When tracking a link click redirect the user to the destination after performing the necessary tracking
     *
     * @param $target string where to send the subscriber
     */
    protected function email_link_clicked( $target = '' )
    {
        /* track every click as an open */
        $this->email_opened();

        $event_id = $this->get_tracking_cookie_param( 'event_id' );
        $event = Plugin::$instance->utils->get_event( $event_id );

        if ( ! $event ){

            // Assume testing...
            if ( is_user_logged_in() ){
                wp_redirect( wp_nonce_url( $target,  -1, 'key' ) );
                return;
            }

            wp_die( 'Oops... You may have clicked an expired link, or your cookies may not be enabled.' );
        }

        $args = array(
            'timestamp'     => time(),
            'contact_id'    => $event->get_contact_id(),
            'funnel_id'     => $event->get_funnel_id(),
            'step_id'       => $event->get_step_id(),
            'email_id'      => $this->get_tracking_cookie_param( 'email_id', 0 ),
            'activity_type' => 'email_link_click',
            'event_id'      => $event->get_id(),
            'referer'       => $target
        );

        if ( get_db( 'activity' )->add( $args ) ){
            do_action( 'groundhogg/tracking/email/click', $this );

            wp_redirect( wp_nonce_url( $target,  -1, 'key' ) );
            return;
        }

        // Tracking not available.
        wp_die( 'Oops... You may have clicked an expired link, or your cookies may not be enabled.' );
    }

    /**
     * Sets the cookie upon a form fill.
     *
     * @param $contact Contact
     */
    public function form_filled( $contact )
    {
        if ( ! isset_not_empty( $_COOKIE, self::TRACKING_COOKIE ) ){
            $this->add_tracking_cookie_param( 'contact_id', $contact->get_id() );
            $this->build_tracking_cookie();
        }
    }

}