<?php
/**
 * Welcome Page
 *
 * Introducing the welcome page, the user is directly brought here upon activating Groundhogg.
 * It includes links to tutorials and extensions.
 *
 * @package     Admin
 * @subpackage  Admin/Welcome
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Welcome_Page
{

    /**
     * @var WPGH_Notices
     */
    public $notices;

    function __construct()
    {

        add_action('admin_menu', array($this, 'register'));

        if ( isset( $_GET['page'] ) && $_GET[ 'page' ] === 'groundhogg' ){

            $this->notices = WPGH()->notices;

            $this->notices->add(
              'wip', __( 'This page is currently a work in progress! You should move on from it for now. Complete videos, links, and extensions coming soon.' ), 'info'
            );

            $this->notices->add(
              'affiliate', __( 'You can get our entire extension library for $1 if <a href="https://www.groundhogg.io/referral-partner-sign-up/" target="_blank">you refer a friend.</a>' ), 'info'
            );

            add_action( 'admin_init', array( $this, 'status_check' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

        }
    }

    public function status_check()
    {
        $this->check_settings();
        $this->check_funnels();
        $this->check_settings();
        $this->check_plugins();
    }

    /**
     * Check to see if some plugins are active.
     */
    public function check_plugins()
    {
        if ( ! is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ){
            $this->notices->add(
                'smtp', __( 'While we work on an SMTP solution for you that is builtin, there are other ones out there. We recommend you install <a href="https://en-ca.wordpress.org/plugins/wp-mail-smtp/">WP Mail SMTP</a> to increase your deliverability.' ), 'info'
            );
        }
    }


    /**
     * Check to see if the settings are complete
     */
    public function check_settings()
    {
        if ( ! wpgh_get_option( 'gh_business_name' ) ){
            $this->notices->add(
                'incomplete_settings', __( 'It appears you have incomplete settings! Go to <a href="?page=gh_settings">the settings page</a> and fill out all your business information.' ), 'warning'
            );
        }
    }

    /**
     * Check to see if there are any active funnels
     */
    public function check_funnels()
    {
        $funnels = WPGH()->funnels->get_funnels();

        if ( empty( $funnels ) ){
            $this->notices->add(
                'no_active_funnels', __( 'You have no active funnels! Go to <a href="?page=gh_funnels&action=add">the funnels page</a> and create your first funnel!' ), 'warning'
            );
        }

    }

    public function check_contacts()
    {
        $contacts = WPGH()->contacts->count();

        if ( $contacts < 10 ){
            $this->notices->add(
                'no_contacts', __( 'Seems like you need some more contacts. Go to the <a href="?page=gh_settings&tab=tools">tools area</a> and import your mailing list!' ), 'warning'
            );
        }

    }

    /**
     * Add the page
     */
    public function register()
    {

        $page = add_menu_page(
            'Welcome',
            'Groundhogg',
            'view_contacts',
            'groundhogg',
            array( $this, 'page' ),
            'dashicons-email-alt',
            2
        );

        add_action("load-" . $page, array($this, 'help'));

    }

    /**
     * Add the help bar
     */
    public function help()
    {
        //todo
    }


    /* Enque JS or CSS */
    public function scripts()
    {
        wp_enqueue_style( 'welcome-page', WPGH_ASSETS_FOLDER . 'css/admin/welcome.css', filemtime( WPGH_PLUGIN_DIR . 'assets/css/admin/welcome.css' ) );
    }

    /**
     * Returns an array of support articles...
     * Has a link to a video
     * has a link to an article on groundhogg.io
     * has a short description
     *
     * @return array
     */
    public function get_articles()
    {
        $articles = array(
            array(
                'title' => __( 'Full Walkthrough', 'groundhogg' ),
                'desc'  => __( 'Watch this full walkthrough of setting up Groundhogg and building your first funnel.', 'groundhogg' ),
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/2018/12/Full-Demo.png',
                'link'  => 'https://groundhogg.io/demo/'
            ),
            array(
                'title' => __( "Managing Users", 'groundhogg' ),
                'desc'  => __( "If you have sales people you need to read how you can give them only specific access to your CRM.", 'groundhogg' ),
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/2018/10/user-roles-722x361.png',
                'link'  => 'https://www.groundhogg.io/support/how-to-manage-user-roles/'
            ),
            array(
                'title' => __( "How To Remain Compliant ", 'groundhogg' ),
                'desc'  => __( "Learn about the tools Groundhogg provides so you can remain complaint in countries around the world.", 'groundhogg' ),
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/2018/10/compliance-722x361.png',
                'link'  => 'https://www.groundhogg.io/support/compliance/'
            ),
            array(
                'title' => __( "Send Marketing With Groundhogg", 'groundhogg' ),
                'desc'  => __( "Get your first 1000 credits free when you sign up! Send SMS and Email with Groundhogg.", 'groundhogg' ),
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/11/email-credits-722x361.png',
                'link'  => 'https://www.groundhogg.io/downloads/email-credits/'
            ),
        );

        $articles = apply_filters( 'wpgh_support_articles', $articles );

        return $articles;
    }

    /**
     * Convert array to html article
     *
     * @param $args array
     */
    public function article_to_html( $args=array() )
    {
        /* I'm lazy so just covert it to an object*/
        $article = (object) $args;

        ?>

        <div class="postbox">
            <?php if ( $article->title ): ?>
                <h2 class="hndle"><?php echo $article->title; ?></h2>
            <?php endif; ?>
            <div class="inside">
                <?php if ( $article->img ): ?>
                    <div class="img-container">
                        <img src="<?php echo $article->img; ?>" style="width: 100%;max-width: 100%;">
                    </div>
                    <hr/>
                <?php endif; ?>
                <?php if ( $article->desc ): ?>
                    <div class="article-description">
                        <?php echo $article->desc; ?>
                    </div>
                    <hr/>
                <?php endif; ?>
                <?php if ( $article->link ): ?>
                    <p>
                        <a class="button button-primary" href="<?php echo esc_url_raw( $article->link ); ?>" target="_blank"><?php _e( 'Read More...' ); ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php

    }

    /**
     * Get a list of extensions to promote on the welcome page
     *
     * @return array
     */
    public function get_extensions()
    {
        $extensions = array(
            array(
                'title' => 'All Access Pass',
                'desc'  => 'Get every Groundhogg extension at one low price!',
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/10/all-access.png',
                'link'  => 'https://www.groundhogg.io/downloads/all-access-pass/'
            ),
            array(
                'title' => 'Form Styling',
                'desc'  => 'Quickly style forms without having to code with CSS!',
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/11/form-styling.png',
                'link'  => 'https://www.groundhogg.io/downloads/form-styling/'
            ),
            array(
                'title' => 'Email Countdown Timer',
                'desc'  => 'Create more engagement from emails by adding countdown timers to your emails.',
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/11/countdown-timers-new.png',
                'link'  => 'https://www.groundhogg.io/downloads/countdown/'
            ),
            array(
                'title' => 'Contracts',
                'desc'  => 'Have your contacts sign legally binding contracts through Groundhogg. No third party apps required.',
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/10/contracts.png',
                'link'  => 'https://www.groundhogg.io/downloads/contracts/'
            ),
            array(
                'title' => 'Contact Form 7',
                'desc'  => 'Start collecting lead information through Contact Form 7, no setup required. Works instantly!',
                'img'   => 'https://www.groundhogg.io/wp-content/uploads/edd/2018/10/contact-form-7.png',
                'link'  => 'https://www.groundhogg.io/downloads/contact-form-7/'
            ),
        );

        $extensions = apply_filters( 'wpgh_extension_ads', $extensions );

        return $extensions;
    }

    /**
     * Convert array to html article
     *
     * @param $args array
     */
    public function extension_to_html( $args=array() )
    {
        /* I'm lazy so just covert it to an object*/
        $extension = (object) $args;

        ?>

        <div class="postbox">
            <?php if ( $extension->title ): ?>
                <h2 class="hndle"><?php echo $extension->title; ?></h2>
            <?php endif; ?>
            <div class="inside">
                <?php if ( $extension->img ): ?>
                    <div class="img-container">
                        <img src="<?php echo $extension->img; ?>" style="width: 100%;max-width: 100%;">
                    </div>
                <hr/>
                <?php endif; ?>
                <?php if ( $extension->desc ): ?>
                    <div class="article-description">
                        <?php echo $extension->desc; ?>
                    </div>
                <hr/>
                <?php endif; ?>
                <?php if ( $extension->link ): ?>
                    <p>
                        <a class="button button-primary" href="<?php echo esc_url_raw( $extension->link ); ?>" target="_blank"><?php _e( 'Buy Now!' ); ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php

    }

    /**
     * The main output
     */
    public function page()
    {

        $user = wp_get_current_user();

        ?>
        <img class="phil" src="<?php echo WPGH_ASSETS_FOLDER . 'images/phil-340x340.png'; ?>" width="340" height="340">
        <div id="welcome-page" class="welcome-page">
            <div id="poststuff">
                <div class="welcome-header">
                    <h1><?php echo sprintf( __( 'Welcome %s!', 'groundhogg' ), $user->first_name ); ?></h1>
                </div>
                <?php $this->notices->notices(); ?>
                <div class="left-col col">

                    <div id="support-articles">

                        <div class="postbox support">
                            <div class="inside">
                                <h3><?php _e( 'Support Articles', 'Groundhogg' ); ?></h3>
                                <p><?php _e( "Don't know where to start? Checkout these articles and learn how to make Groundhogg work for you.", 'groundhogg' ); ?></p>
                                <p style="text-align: center">
                                    <a class="button button-primary" href="https://www.groundhogg.io/category/support/" target="_blank"><?php _e( 'View All!' ); ?></a>
                                </p>
                            </div>
                        </div>

                        <?php

                        foreach ( $this->get_articles() as $article ):

                            $this->article_to_html( $article );

                        endforeach;

                        ?>

                    </div>
                </div>
                <div class="right-col col">

                    <div id="extensions">

                        <div class="postbox support">
                            <div class="inside">
                                <h3><?php _e( 'Awesome Extensions', 'Groundhogg' ); ?></h3>
                                <p><?php _e( "Need more functionality? Need to connect Groundhogg to your store? We have an extension for that!", 'groundhogg' ); ?></p>
                                <p style="text-align: center">
                                    <a class="button button-primary" href="https://groundhogg.io/downloads/" target="_blank"><?php _e( 'View All!' ); ?></a>
                                </p>
                            </div>
                        </div>

                        <?php

                        foreach ( $this->get_extensions() as $extension ):

                            $this->extension_to_html( $extension );

                        endforeach;

                        ?>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }

}