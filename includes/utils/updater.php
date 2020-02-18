<?php

namespace Groundhogg;


/**
 * Updater
 *
 * @since       File available since Release 1.0.16
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @package     Includes
 */
abstract class Updater {


	/**
	 * WPGH_Upgrade constructor.
	 */
	public function __construct() {
		add_action( 'upgrader_process_complete', [ $this, 'schedule_updates' ], 99, 2 ); // DO LAST
		add_action( "groundhogg/{$this->get_updater_name()}/do_updates", [ $this, 'do_updates' ] );
		add_action( 'groundhogg/admin/tools/updates', [ $this, 'show_manual_updates' ] ); // DO LAST
		add_action( 'admin_init', [ $this, 'do_manual_updates' ], 99 ); // DO LAST
	}

	/**
	 * Get the display name of the updater for the tools page
	 *
	 * @return string
	 */
	public function get_display_name() {
		return key_to_words( $this->get_updater_name() );
	}

	/**
	 * Get the plugin file for this extension
	 *
	 * @return string
	 */
	protected function get_plugin_file() {
		return GROUNDHOGG__FILE__;
	}

	/**
	 * A unique name for the updater to avoid conflicts
	 *
	 * @return string
	 */
	abstract protected function get_updater_name();

	/**
	 * Get a list of updates which are available.
	 *
	 * @return string[]
	 */
	abstract protected function get_available_updates();

	/**
	 * Get a list of updates that do not update automatically, but will show on the updates page
	 *
	 * @return string[]
	 */
	protected function get_optional_updates() {
		return [];
	}

	/**
	 * Get the previous version which the plugin was updated to.
	 *
	 * @return string[]
	 */
	protected function get_previous_versions() {
		return Plugin::$instance->settings->get_option( $this->get_version_option_name(), [] );
	}

	/**
	 * Whether a certain update was performed or not.
	 *
	 * @param $version
	 *
	 * @return bool
	 */
	public function did_update( $version ) {
		return in_array( $version, $this->get_previous_versions() );
	}

	/**
	 * Set the last updated to version in the DB
	 *
	 * @param $version
	 */
	protected function remember_version_update( $version ) {
		$versions = $this->get_previous_versions();

		if ( ! in_array( $version, $versions ) ) {
			$versions[] = $version;
		}

		Plugin::$instance->settings->update_option( $this->get_version_option_name(), $versions );
	}

	/**
	 * Gets the DB option name to retrieve the previous version.
	 *
	 * @return string
	 */
	protected function get_version_option_name() {
		return sanitize_key( sprintf( '%s_version_updates', $this->get_updater_name() ) );
	}

	/**
	 * Show the manual updates in the tools area
	 */
	public function show_manual_updates() {
		?><h3><?php echo apply_filters( 'groundhogg/updater/display_name', $this->get_display_name() ); ?></h3><?php

		$updates = array_merge( $this->get_available_updates(), $this->get_optional_updates() );

		usort( $updates, 'version_compare' );

		foreach ( $updates as $update ):

			?><p><?php

			echo html()->e( 'a', [
				'href' => add_query_arg( [
					'updater'       => $this->get_updater_name(),
					'manual_update' => $update,
					'confirm'       => 'yes',
				], $_SERVER['REQUEST_URI'] )
			], sprintf( __( 'Version %s', 'groundhogg' ), $update ) );

			?></p><?php

		endforeach;
	}

	/**
	 * Manually perform a selected update routine.
	 */
	public function do_manual_updates() {

		if ( get_request_var( 'updater' ) !== $this->get_updater_name() || ! get_request_var( 'manual_update' ) || ! wp_verify_nonce( get_request_var( 'manual_update_nonce' ), 'gh_manual_update' ) || ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$update = get_url_var( 'manual_update' );

		$updates = array_merge( $this->get_available_updates(), $this->get_optional_updates() );

		if ( ! in_array( $update, $updates ) ) {
			return;
		}

		if ( $this->update_to_version( $update ) ) {
			Plugin::$instance->notices->add( 'updated', sprintf( __( 'Update to version %s successful!', 'groundhogg' ), $update ) );
		} else {
			Plugin::$instance->notices->add( new \WP_Error( 'update_failed', __( 'Update failed.', 'groundhogg' ) ) );
		}

		wp_safe_redirect( admin_page_url( 'gh_tools', [ 'tab' => 'updates' ] ) );
		die();
	}

	/**
	 * Schedule the automatic updates based on the file
	 *
	 * @param $upgrader
	 * @param $options
	 */
	public function schedule_updates( $upgrader, $options ) {
		// If an update has taken place and the updated type is plugins and the plugins element exists
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // Schedule a cron event to run right away that will run the automatic updates
            if ( ! wp_next_scheduled( "groundhogg/{$this->get_updater_name()}/do_updates" ) ){
                wp_schedule_single_event( time(), "groundhogg/{$this->get_updater_name()}/do_updates" );
            }
		}
	}

	/**
	 * Check whether upgrades should happen or not.
	 */
	public function do_updates() {

		$update_lock = 'gh_' . $this->get_updater_name() . '_doing_updates';

		// Check if an update lock is present.
		if ( get_transient( $update_lock ) ) {
			return;
		}

		// Set lock so second update process cannot be run before this one is complete.
		set_transient( $update_lock, time(), MINUTE_IN_SECONDS );

		$previous_updates = $this->get_previous_versions();

		// installing...
		if ( ! $previous_updates || empty( $previous_updates ) ) {
			Plugin::$instance->settings->update_option( $this->get_version_option_name(), $this->get_available_updates() );

			return;
		}

		$available_updates = $this->get_available_updates();
		$missing_updates   = array_diff( $available_updates, $previous_updates );

		if ( empty( $missing_updates ) ) {
			return;
		}

		foreach ( $missing_updates as $update ) {
			$this->update_to_version( $update );
		}

		do_action( "groundhogg/updater/{$this->get_updater_name()}/finished" );
	}

	/**
	 * Takes the current version number and converts it to a function which can be clled to perform the upgrade requirements.
	 *
	 * @param $version string
	 *
	 * @return bool|string
	 */
	private function convert_version_to_function( $version ) {

		$nums = explode( '.', $version );
		$func = sprintf( 'version_%s', implode( '_', $nums ) );

		if ( method_exists( $this, $func ) ) {
			return $func;
		}

		return false;

	}

	/**
	 * Given a version number call the related function
	 *
	 * @param $version
	 *
	 * @return bool
	 */
	private function update_to_version( $version ) {
		/**
		 * Check if the version we want to update to is greater than that of the db_version
		 */
		$func = $this->convert_version_to_function( $version );

		if ( $func && method_exists( $this, $func ) ) {

			call_user_func( array( $this, $func ) );

			$this->remember_version_update( $version );

			do_action( "groundhogg/updater/{$this->get_updater_name()}/{$func}" );

			return true;
		}

		return false;
	}
}