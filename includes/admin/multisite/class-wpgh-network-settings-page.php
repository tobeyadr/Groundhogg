<?php
/**
 * Plugin Settings
 *
 * This  is your fairly typical settigns page.
 * It's a BIT of a mess, but I digress.
 *
 * @package     Admin
 * @subpackage  Admin/Settings
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Network_Settings_Page
{

    public $notices;

	public function __construct()
    {

        $this->notices = WPGH()->notices;

		add_action( 'network_admin_menu', array( $this, 'register' ) );

    }

    /* Register the page */
    public function register()
    {
        $page = add_menu_page(
            'Groundhogg',
            'Groundhogg',
            'manage_options',
            'groundhogg_network',
            array( $this, 'settings_options' )
        );

        add_action( "load-" . $page, array( $this, 'help' ) );

        add_action( 'network_admin_edit_groundhogg', array( $this, 'save_options' ) );

    }

    /* Display the help bar */
    public function help()
    {
        //todo
    }

	public function settings_options()
    {


        ?>
		<div class="wrap">
			<h1>Groundhogg <?php _e( 'Settings' ); ?></h1>
			<?php $this->notices->notices(); ?>
			<form method="POST" enctype="multipart/form-data" action="edit.php?action=groundhogg">
                <?php wp_nonce_field( 'groundhogg-validate' ); ?>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Enable Multisite Database', 'groundhogg' ); ?>
                        </th>
                        <td>
                            <?php echo WPGH()->html->checkbox( array(
                                'label' => 'Enable.',
                                'name'  => 'enable_global_db',
                                'value' => 1,
                                'checked' => get_site_option( 'gh_global_db_enabled' )
                            )); ?>
                            <p class="description"><?php _e( 'This will enable a global database for all your sites in this multisite installation.' ) ?></p>
                            <p class="description"><?php _e( 'You will have to manage all plugin settings from your MAIN blog, and any Groundhogg extensions should be made network active as well.' ) ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <?php submit_button( __('Save Changes') ); ?>
                </p>

			</form>
		</div> <?php
	}

	public function save_options()
    {
        check_admin_referer( 'groundhogg-validate' ); // Nonce security check

        if ( isset( $_POST[ 'enable_global_db' ] ) ){
            update_site_option( 'gh_global_db_enabled', $_POST['enable_global_db'] );
        } else {
            delete_site_option( 'gh_global_db_enabled' );
        }

        $this->notices->add( 'updated', __( 'Settings Updated!' ) );

        wp_redirect( add_query_arg( array(
            'page' => 'groundhogg_network',
            'updated' => true ), network_admin_url('admin.php')
        ));

        exit;
    }

}