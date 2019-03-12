<?php
/**
 * Page Visited
 *
 * This will run whenever a page is visited
 *
 * @package     Elements
 * @subpackage  Elements/Benchmarks
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Page_Visited extends WPGH_Funnel_Step
{

    /**
     * @var string
     */
    public $type    = 'page_visited';

    /**
     * @var string
     */
    public $group   = 'benchmark';

    /**
     * @var string
     */
    public $icon    = 'page-visited.png';

    /**
     * @var string
     */
    public $name    = 'Page Visited';

    /**
     * Add the completion action
     *
     * WPGH_Form_Filled constructor.
     */
    public function __construct()
    {
        $this->name         = _x( 'Page Visited', 'element_name', 'groundhogg' );
        $this->description  = _x( 'Runs whenever the specified page is visited.', 'element_description', 'groundhogg' );

        parent::__construct();

        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'groundhogg/api/v2/elements/page-view', array( $this, 'complete' ) );
    }

    /**
     * Enqueue the scripts for the event runner process.
     * Appears on front-end & backend as it will be run by traffic to the site.
     */
    public function scripts()
    {
        wp_enqueue_script( 'wpgh-page-view', WPGH_PLUGIN_URL . 'assets/js/frontend.min.js' , array('jquery'), filemtime( WPGH_PLUGIN_DIR . 'assets/js/frontend.min.js' ), true );
        wp_localize_script( 'wpgh-page-view', 'gh_frontent_object', array(
            'page_view_endpoint' => site_url( 'wp-json/gh/v2/elements/page-view/' ),
            'form_impression_endpoint' => site_url( 'wp-json/gh/v2/elements/form-impression/' )
        ));
    }

    /**
     * @param $step WPGH_Step
     */
    public function settings( $step )
    {
        $match_type = $step->get_meta( 'match_type' );
        $match_url = $step->get_meta(  'url_match' );

        ?>

        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <?php esc_attr_e( 'Enter URL', 'groundhogg' ); ?>
                </th>
                <td><?php

                    $match_types = array(
                        'partial'   => __( 'Partial Match' ),
                        'exact'     => __( 'Exact Match' )
                    );

                    $args = array(
                        'id'        => $step->prefix( 'match_type' ),
                        'name'      => $step->prefix( 'match_type' ),
                        'options'   => $match_types,
                        'selected'  => $match_type
                    );

                    echo WPGH()->html->dropdown( $args );

                    $args = array(
                        'id'        => $step->prefix( 'url_match' ),
                        'name'      => $step->prefix( 'url_match' ),
                        'title'     => __( 'Match Url' ),
                        'value'     => $match_url
                    );

                    echo WPGH()->html->input( $args );

                    ?>
                    <p class="description">
                        <a href="#" data-target="<?php echo $step->prefix( 'url_match' ) ?>" id="<?php echo $step->prefix( 'add_link' ); ?>">
                            <?php _e( 'Insert Link' , 'groundhogg' ); ?>
                        </a>
                    </p>
                    <script>
                        jQuery(function($){
                            $('#<?php echo $step->prefix('add_link' ); ?>').linkPicker();
                        });
                    </script>
                </td>
            </tr>
        </table>

        <?php
    }

    /**
     * Save the step settings
     *
     * @param $step WPGH_Step
     */
    public function save( $step )
    {
        if ( isset( $_POST[ $step->prefix( 'match_type' ) ] ) )
            $step->update_meta( 'match_type', sanitize_key( $_POST[ $step->prefix(  'match_type' ) ] ) );

        if ( isset( $_POST[ $step->prefix( 'url_match' ) ] ) )
            $step->update_meta(  'url_match', esc_url_raw( $_POST[ $step->prefix(  'url_match' ) ] ) );
    }

    /**
     * Perform the complete action
     *
     * @param $ref string
     * @param $contact WPGH_Contact
     */
    public function complete( $ref, $contact )
    {
        $steps = WPGH()->steps->get_steps( array( 'step_type' => $this->type, 'step_group' => $this->group ) );

        if ( empty( $steps ) )
            return;

        $s = false;

        foreach ( $steps as $step ) {

            $step = new WPGH_Step( $step->ID );

            if ( $step->can_complete( $contact ) ){

                $match_type = $step->get_meta( 'match_type' );
                $match_url  = $step->get_meta( 'url_match' );

                if ( $match_type === 'exact' ){
                    $is_page = $ref === $match_url;
                } else {
                    $is_page = strpos( $ref, $match_url ) !== false;
                }

                if ( $is_page ){

                   $s = $step->enqueue( $contact );

                }
            }
        }
    }

    /**
     * Process the tag applied step...
     *
     * @param $contact WPGH_Contact
     * @param $event WPGH_Event
     *
     * @return true
     */
    public function run( $contact, $event )
    {
        //do nothing...

        return true;
    }

}