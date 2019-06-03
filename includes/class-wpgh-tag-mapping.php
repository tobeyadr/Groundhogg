<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2019-03-28
 * Time: 11:01 AM
 */
if ( ! class_exists( 'WPGH_Bulk_Job' ) ){
    include WPGH_PLUGIN_DIR. 'includes/class-wpgh-bulk-job.php';
}

class WPGH_Tag_Mapping extends WPGH_Bulk_Job
{

    const MARKETABLE = 'marketable';
    const NON_MARKETABLE = 'unmarketable';

    /**
     * the tag map.
     *
     * @var array
     */
    private $tag_map = [];

    public function __construct()
    {

        // Listen for an explicit status change.
        add_action( 'groundhogg/contact/preferences/updated', [ $this, 'optin_status_changed' ], 10, 3 );

        // Contact's marketability can expire with time, but it's too costly to setup a cronjob
        // So instead we'll listen for an event failed. #goodenough
        add_action( 'groundhogg/event/failed', [ $this, 'listen_for_non_marketable' ] );

        if ( wpgh_get_option( 'gh_optin_status_job', false ) ){
            add_action( 'admin_init', [ $this, 'add_upgrade_notice' ] );
        }

        parent::__construct();
    }

    /**
     * Add a notice promting the user to perform the retroactive bulk action.
     */
    public function add_upgrade_notice()
    {
        $notice = sprintf(
            __( "New features are now available, but we need to perform an upgrade process first! %s", 'groundhogg' ),
            sprintf( "&nbsp;&nbsp;<a href='%s' class='button button-secondary'>Start Upgrade</a>", $this->get_start_url() )
        );

        WPGH()->notices->add( 'status_tag_upgrade_notice', $notice, 'info' );
    }

    /**
     * Get the list of default tags and option names...
     *
     * @return array
     */
    private function get_default_tags()
    {
        $tags = [
            'gh_confirmed_tag' => [
               'tag_name' => 'Confirmed',
               'tag_description' => 'This tags is applied to anyone whose optin status is confirmed.',
            ],
            'gh_unconfirmed_tag' => [
               'tag_name' => 'Unconfirmed',
               'tag_description' => 'This tag is applied to anyone whose optin status is unconfirmed.',
            ],
            'gh_unsubscribed_tag' => [
               'tag_name' => 'Unsubscribed',
               'tag_description' => 'This tag is applied to anyone whose optin status is unsubscribed.',
            ],
            'gh_spammed_tag' => [
               'tag_name' => 'Spam',
               'tag_description' => 'This tag is applied to anyone whose optin status is spam.',
            ],
            'gh_bounced_tag' => [
               'tag_name' => 'Bounced',
               'tag_description' => 'This tag is applied to anyone whose optin status is bounced.',
            ],
            'gh_complained_tag' => [
               'tag_name' => 'Complained',
               'tag_description' => 'This tag is applied to anyone whose optin status is complained.',
            ],
            'gh_marketable_tag' => [
               'tag_name' => 'Marketable',
               'tag_description' => 'This tag is applied to anyone whose optin status is marketable.',
            ],
            'gh_non_marketable_tag' => [
               'tag_name' => 'Non-marketable',
               'tag_description' => 'This tag is applied to anyone whose optin status is non-marketable.',
            ],
        ];

        return $tags;
    }

    /**
     * Install the defaults.
     */
    public function install_default_tags()
    {
        $tags = $this->get_default_tags();
        foreach ( $tags as $option_name => $tag_args ){
            if ( ! wpgh_get_option( $option_name, false ) ){
                $tags_id = WPGH()->tags->add( $tag_args );
                if ( $tags_id ){
                    wpgh_update_option( $option_name, $tags_id );
                }
            }
        }
    }

    /**
     * get the map of optin status to tag
     *
     * @return array
     */
    public function get_tag_map()
    {

        if ( empty( $this->tag_map ) ){
            $this->tag_map = [
                WPGH_CONFIRMED       => wpgh_get_option( 'gh_confirmed_tag', false ),
                WPGH_UNCONFIRMED     => wpgh_get_option( 'gh_unconfirmed_tag', false ),
                WPGH_UNSUBSCRIBED    => wpgh_get_option( 'gh_unsubscribed_tag', false ),
                WPGH_SPAM            => wpgh_get_option( 'gh_spammed_tag', false ),
                WPGH_HARD_BOUNCE     => wpgh_get_option( 'gh_bounced_tag', false ),
                WPGH_COMPLAINED      => wpgh_get_option( 'gh_complained_tag', false ),
                self::MARKETABLE     => wpgh_get_option( 'gh_marketable_tag', false ),
                self::NON_MARKETABLE => wpgh_get_option( 'gh_non_marketable_tag', false ),
            ];
        }

        return $this->tag_map;

    }

    /**
     * Get the associated tag for an optin status.
     *
     * @param int $status
     * @return bool|mixed
     */
    public function get_status_tag( $status = 0 )
    {

        $map = $this->get_tag_map();

        if ( key_exists( $status, $map ) ){
            return $map[ $status ];
        }

        return false;

    }

    /**
     * Perform the tag mapping.
     *
     * @param $contact_id int the ID of the contact
     * @param int $status the status.
     * @param int $old_status the previous status.
     *
     * @return void
     */
    public function optin_status_changed( $contact_id=0, $status=0, $old_status=0 )
    {

        $contact = wpgh_get_contact( $contact_id );

        if ( ! $contact )
            return;

        $non_marketable_tag = $this->get_status_tag( self::NON_MARKETABLE );
        $marketable_tag = $this->get_status_tag( self::MARKETABLE );

        /* Tags to remove */
        $remove_tags = [
            $this->get_status_tag( $old_status ),
        ];

        /* Marketable decision */
        if ( $contact->is_marketable() && $contact->has_tag( $non_marketable_tag ) ){
            $remove_tags[] = $non_marketable_tag;
        } else if ( ! $contact->is_marketable() && $contact->has_tag( $marketable_tag ) ){
            $remove_tags[] = $marketable_tag;
        }

        /* Remove all the un-needed tags */
        $contact->remove_tag( $remove_tags );

        /* Tags to add */
        $add_tags = [
            $this->get_status_tag( $status ),
        ];

        if ( $contact->is_marketable() && ! $contact->has_tag( $marketable_tag ) ){
            $add_tags[] = $marketable_tag;
        } else if ( ! $contact->is_marketable() && ! $contact->has_tag( $non_marketable_tag ) ){
            $add_tags[] = $non_marketable_tag;
        }

        /* Add the tags */
        $contact->apply_tag( $add_tags );

    }

    /**
     * Listen for the event failed hook.
     *
     * What this will allow is to listen for a NON_MARKETABLE error code which will allow the adding of the non marketable tag.
     *
     * @param $event WPGH_Event
     */
    public function listen_for_non_marketable( $event )
    {

        $non_marketable_tag = $this->get_status_tag( self::NON_MARKETABLE );
        $marketable_tag = $this->get_status_tag( self::MARKETABLE );

        if ( $event->error->get_error_code() === 'NON_MARKETABLE' && $event->contact->has_tag( $marketable_tag ) ){
            $event->contact->remove_tag( $marketable_tag );
            $event->contact->apply_tag( $non_marketable_tag );
        }

    }

    /**
     * @param $items
     * @return int
     */
    public function max_items( $max, $items ){
        $max = intval( ini_get( 'max_input_vars' ) );
        return min( $max, 100 );
    }

    /**
     * Get the IDS of all contacts.
     *
     * @param $items
     * @return array
     */
    public function query( $items )
    {
        $query = new WPGH_Contact_Query();
        $items = $query->query([]);

        $ids = wp_list_pluck( $items, 'ID' );

        return $ids;
    }

    /**
     * Get the action reference.
     *
     * @return string
     */
    public function get_action()
    {
        return 'bulk_apply_status_tags';
    }

    /**
     * Do stuff before the loop
     *
     * @return void
     */
    protected function pre_loop(){}

    /**
     * do stuff after the loop
     *
     * @return void
     */
    protected function post_loop(){}

    /**
     * Process an item
     *
     * @param $item mixed
     * @param $args array
     * @return void
     */
    protected function process_item( $item )
    {
        $contact = wpgh_get_contact( absint( $item ) );

        if ( $contact ){

            $tags = [];

            $tags[] = $this->get_status_tag( $contact->optin_status );
            $tags[] = $contact->is_marketable() ? $this->get_status_tag( self::MARKETABLE ) : $this->get_status_tag( self::NON_MARKETABLE );

            $contact->apply_tag( $tags );

        }
    }

    /**
     * Cleanup any options/transients/notices after the bulk job has been processed.
     *
     * @return void
     */
    protected function clean_up()
    {
        WPGH()->notices->remove( 'status_tag_upgrade_notice' );
        wpgh_delete_option( 'gh_optin_status_job' );
    }

    /**
     * @return string
     */
    protected function get_finished_notice()
    {
        return _x('Job finished! Optin status tag mapping has now been enabled.', 'notice', 'groundhogg');
    }
}