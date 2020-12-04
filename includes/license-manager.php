<?php

namespace Groundhogg;


/**
 * Module Manager
 *
 * This class is a helper class for the settigns page. it essentially provides an api with Groundhogg.io for managing premium extension licenses.
 *
 * @since       File available since Release 0.1
 * @subpackage  Admin/Settings
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @package     Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class License_Manager {

	static $extensions = array(); // array( item_id => array( license, status ) )
	static $storeUrl = "https://www.groundhogg.io";
	static $user_agent = 'Groundhogg/' . GROUNDHOGG_VERSION . ' license-manager';

	/**
	 * Maybe setup the licenses unless the haven't been already
	 */
	public static function init_licenses() {
		if ( empty( static::$extensions ) ) {
			static::$extensions = get_option( "gh_extensions", [] );
		}
	}

	/**
	 * Get all the stored licenses
	 *
	 * @return array
	 */
	public static function get_extension_licenses() {
		self::init_licenses();

		return static::$extensions;
	}

	/**
	 * Get a unique array of the licenses
	 *
	 * @return array
	 */
	public static function get_licenses() {
		self::init_licenses();

		return array_unique( wp_list_pluck( static::$extensions, 'license' ) );
	}

	/**
	 * Get a list of the expired licenses
	 *
	 * @return array
	 */
	public static function get_expired_licenses() {
		self::init_licenses();

		return array_unique( wp_list_pluck( array_filter( self::$extensions, function ( $license ) {
			return $license['status'] === 'invalid';
		} ), 'license' ) );
	}

	/**
	 * Add an extension to the extensions options.
	 *
	 * @param $item_id int
	 * @param $license string
	 * @param $status  string
	 * @param $expiry  string
	 *
	 * @return bool
	 */
	public static function add_extension( $item_id, $license, $status, $expiry ) {
		self::init_licenses();

		static::$extensions[ $item_id ] = array(
			'license' => $license,
			'status'  => $status,
			'expiry'  => $expiry
		);

		return update_option( "gh_extensions", static::$extensions );
	}

	/**
	 * Remove an extension
	 *
	 * @param $item_id int
	 *
	 * @return bool
	 */
	public static function delete_extension( $item_id ) {
		self::init_licenses();

		unset( static::$extensions[ $item_id ] );

		return update_option( "gh_extensions", static::$extensions );
	}

	/**
	 * Whether the current install has extensions installed.
	 *
	 * @return bool
	 */
	public static function has_extensions() {
		self::init_licenses();

		return ! empty( static::$extensions );
	}

	/**
	 * Will get a specific license for a given item
	 * If no item is specified, will return the first license
	 * If item is specific but no license exists, return false
	 *
	 * @param bool $item_id
	 *
	 * @return bool|mixed
	 */
	public static function get_license( $item_id = false ) {
		self::init_licenses();

		if ( empty( static::$extensions ) ) {
			return false;
		}

		if ( $item_id && isset_not_empty( static::$extensions, $item_id ) ) {
			return static::$extensions[ $item_id ]['license'];
		}

		if ( ! $item_id ) {

			$licenses = array_filter( wp_list_pluck( static::$extensions, 'license' ) );

			if ( ! empty( $licenses ) ) {
				return $licenses[0];
			}

		}

		return false;
	}

	/**
	 * Get the status of a specific license
	 *
	 * @param $item_id
	 *
	 * @return false|mixed
	 */
	public static function get_license_status( $item_id ) {
		self::init_licenses();

		if ( isset( static::$extensions[ $item_id ] ) ) {
			return static::$extensions[ $item_id ]['status'];
		}

		return false;

	}

	/**
	 * Update the status of a license
	 *
	 * @param int         $item_id
	 * @param string      $status
	 *
	 * @param string|bool $expiry Maybe update the expiry
	 *
	 * @return bool
	 */
	public static function update_license_status( $item_id, $status, $expiry = false ) {
	    self::init_licenses();

		static::$extensions[ $item_id ]['status'] = $status;

		if ( $expiry ) {
			static::$extensions[ $item_id ]['expiry'] = $expiry;
		}

		return update_option( "gh_extensions", static::$extensions );
	}

	/**
	 * Activate a license
	 */
	public static function perform_activation() {
		if ( isset( $_POST['gh_activate_license'] ) ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( "Cannot access this functionality" );
			}

			$licenses = map_deep( get_request_var( 'licenses' ), 'sanitize_text_field' );

			if ( ! is_array( $licenses ) ) {
				wp_die( _x( 'Invalid license format', 'notice', 'groundhogg' ) );
			}

			foreach ( $licenses as $item_id => $license ) {
				$license = trim( $license );
				$item_id = intval( trim( $item_id ) );

				if ( ! empty( $license ) && ! self::get_license_status( $license ) ) {
					self::activate_license( $license, $item_id );
				}
			}
		}
	}

	/**
	 * Activate a license quietly
	 *
	 * @param $license
	 * @param $item_id
	 *
	 * @return bool|\WP_Error
	 */
	public static function activate_license_quietly( $license, $item_id ) {

		$existing_license = self::get_license( $item_id );

		// If there is no change in the license...
		if ( $existing_license === $license ) {
			return true;
		}

		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_id'    => $item_id,// The ID of the item in EDD,
			// 'item_name'  => $item_name,
			'url'        => home_url(),
			'beta'       => false
		);

		$request = [
			'timeout'    => 15,
			'sslverify'  => true,
			'body'       => $api_params,
			'user-agent' => self::$user_agent,
		];

		// Call the custom api.
		$response = wp_remote_post( static::$storeUrl, $request );
		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = ( is_wp_error( $response ) && $response->get_error_message() ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired' :
						$message = sprintf(
							_x( 'Your license key expired on %s.', 'notice', 'groundhogg' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked' :
						$message = _x( 'Your license key has been disabled.', 'notice', 'groundhogg' );
						break;
					case 'missing' :
						$message = _x( 'Invalid license.', 'notice', 'groundhogg' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = _x( 'Your license is not active for this URL.', 'notice', 'groundhogg' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( _x( 'This appears to be an invalid license key', 'notice', 'groundhogg' ) );
						break;
					case 'no_activations_left':
						$message = _x( 'Your license key has reached its activation limit.', 'notice', 'groundhogg' );
						break;
					default :
						$message = _x( 'An error occurred, please try again.', 'notice', 'groundhogg' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			return new \WP_Error( 'license_failed', __( $message ), $license_data );
		}

		$status = 'valid';
		$expiry = $license_data->expires;

		self::add_extension( $item_id, $license, $status, $expiry );

		return true;
	}

	public static function activate_license( $license, $item_id ) {

		$existing_license = self::get_license( $item_id );

		// If there is no change in the license...
		if ( $existing_license === $license ) {
			return true;
		}

		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_id'    => $item_id,// The ID of the item in EDD,
			// 'item_name'  => $item_name,
			'url'        => home_url(),
			'beta'       => false
		);
		// Call the custom api.
		$response = wp_remote_post( static::$storeUrl, array(
			'timeout'    => 15,
			'sslverify'  => true,
			'body'       => $api_params,
			'user-agent' => self::$user_agent,
		) );
		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = ( is_wp_error( $response ) && $response->get_error_message() ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired' :
						$message = sprintf(
							_x( 'Your license key expired on %s.', 'notice', 'groundhogg' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'disabled' :
						$message = _x( 'Your license key has been disabled.', 'notice', 'groundhogg' );
						break;
					case 'missing' :
						$message = _x( 'Invalid license.', 'notice', 'groundhogg' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = _x( 'Your license is not active for this URL.', 'notice', 'groundhogg' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( _x( 'This appears to be an invalid license key', 'notice', 'groundhogg' ) );
						break;
					case 'no_activations_left':
						$message = _x( 'Your license key has reached its activation limit.', 'notice', 'groundhogg' );
						break;
					default :
						$message = _x( 'An error occurred, please try again.', 'notice', 'groundhogg' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			Plugin::$instance->notices->add( esc_attr( 'license_failed' ), __( $message ), 'error' );
		} else {
			$status = 'valid';
			$expiry = $license_data->expires;

			Plugin::$instance->notices->add( esc_attr( 'license_activated' ), _x( 'License activated', 'notice', 'groundhogg' ), 'success' );

			self::add_extension( $item_id, $license, $status, $expiry );

		}

		return $license_data->success;
	}

	/**
	 * Deactivate a license
	 *
	 * @param $license string
	 *
	 * @return bool
	 */
	public static function deactivate_license( $item_id = 0 ) {

		$license = self::get_license( $item_id );

		$api_params = array(
			'edd_action' => 'deactivate_license',
			'item_id'    => $item_id,
			'license'    => $license,
			'url'        => home_url(),
		);

		$response = wp_remote_post( self::$storeUrl, array(
			'body'       => $api_params,
			'timeout'    => 15,
			'sslverify'  => false,
			'user-agent' => self::$user_agent,
		) );

		if ( is_wp_error( $response ) ) {
			$success = false;
			$message = _x( 'Something went wrong.', 'notice', 'groundhogg' );
		} else {
			$response = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $response->success === false ) {
				$success = false;
				$message = _x( 'Something went wrong.', 'notice', 'groundhogg' );
			} else {
				$success = true;
				$message = _x( 'License deactivated.', 'notice', 'groundhogg' );
			}
		}

		self::delete_extension( $item_id );

		$type = $success ? 'success' : 'error';
		Plugin::$instance->notices->add( 'license_outcome', $message, $type );

		return $success;
	}

	/**
	 * Verify that a license is in good standing.
	 *
	 * @param $item_id
	 * @param $item_name
	 * @param $license
	 *
	 * @return bool
	 */
	public static function verify_license( $item_id, $license ) {
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_id'    => $item_id,
			'url'        => home_url()
		);

		$response = wp_remote_post( static::$storeUrl, array(
			'body'       => $api_params,
			'timeout'    => 15,
			'sslverify'  => true,
			'user-agent' => self::$user_agent,
		) );

		if ( is_wp_error( $response ) ) {
			// return true in the event of an error. Check again later...
			return true;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->success === true && $license_data->license === 'valid') {

			self::update_license_status( $item_id, 'valid', $license_data->expires );
		} else if ( $license_data->success === false && $license_data->license === 'invalid' ) {

			self::update_license_status( $item_id, 'invalid', $license_data->expires );
		}

		return true;
	}

	/**
	 * Get download package details of a plugin
	 *
	 * @param $item_id
	 * @param $license
	 *
	 * @return bool
	 */
	public static function get_version( $item_id, $license ) {
		$api_params = array(
			'edd_action' => 'get_version',
			'license'    => $license,
			'item_id'    => $item_id,
			'url'        => home_url()
		);

		$response = wp_remote_post( static::$storeUrl, array(
			'body'       => $api_params,
			'timeout'    => 15,
			'sslverify'  => true,
			'user-agent' => self::$user_agent,
		) );

		if ( is_wp_error( $response ) ) {
			// return true in the event of an error. Check again later...
			return true;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Todo parse license data?

		return $license_data;
	}

	/**
	 * @return Extension[]
	 */
	public static function get_installed() {
		return Extension::get_extensions();
	}

	/**
	 * @param array $args
	 *
	 * @return array|mixed|object
	 */
	public static function get_store_products( $args = array() ) {
		$key = md5( serialize( $args ) );

		if ( get_transient( "gh_store_products_{$key}" ) ) {
			return get_transient( "gh_store_products_{$key}" );
		}

		$args = wp_parse_args( $args, array(
			//'category' => 'templates',
			'category' => '',
			'tag'      => '',
			's'        => '',
			'page'     => '',
			'number'   => '-1'
		) );

		$url = 'https://www.groundhogg.io/edd-api/v2/products/';

		$response = wp_remote_get( add_query_arg( $args, $url ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$products = json_decode( wp_remote_retrieve_body( $response ) );

		set_transient( "gh_store_products_{$key}", $products, WEEK_IN_SECONDS );

		return $products;
	}

	/**
	 * Get a list of extensions to promote on the welcome page
	 *
	 * @return array
	 */
	public static function get_extensions( $num = 4 ) {
		$products = self::get_store_products( array(
			'category' => [ 16, 9 ],
		) );

		if ( is_wp_error( $products ) ) {
			notices()->add( $products );

			return [];
		}

		$products = $products->products;

		$installed = self::get_installed();

		if ( ! empty( $installed ) ) {
			$keep = [];

			foreach ( $products as $i => $product ) {
				foreach ( $installed as $extension ) {
					if ( absint( $product->info->id ) !== $extension->get_download_id() ) {
						$keep[] = $product;
					}
				}
			}

			// Switch out.
			$products = $keep;
		}

		shuffle( $products );

		if ( $num > count( $products ) ) {
			$num = count( $products );
		}

		$rands      = array_rand( $products, $num );
		$extensions = [];

		foreach ( $rands as $rand ) {
			$extensions[] = $products[ $rand ];
		}

		$extensions = apply_filters( 'groundhogg/license_manager/get_extensions', $extensions );

		return $extensions;
	}

	/**
	 * Convert array to html article
	 *
	 * @param $args array
	 */
	public static function extension_to_html( $args = array() ) {
		/* I'm lazy so just covert it to an object*/
		$extension = (object) $args;

		$extension->info->link = add_query_arg( [
			'utm_source'   => get_bloginfo(),
			'utm_medium'   => 'extension-ad',
			'utm_campaign' => 'admin-links',
			'utm_content'  => sanitize_key( $extension->info->title ),
		], $extension->info->link );

		?>
        <div class="postbox">
			<?php if ( $extension->info->title ): ?>
                <h2 class="hndle"><b><?php echo $extension->info->title; ?></b></h2>
			<?php endif; ?>
            <div class="inside" style="padding: 0;margin: 0">
				<?php if ( $extension->info->thumbnail ): ?>
                    <div class="img-container">
                        <a href="<?php echo $extension->info->link; ?>" target="_blank">
                            <img src="<?php echo $extension->info->thumbnail; ?>"
                                 style="width: 100%;max-width: 100%;border-bottom: 1px solid #ddd">
                        </a>
                    </div>
				<?php endif; ?>
				<?php if ( $extension->info->excerpt ): ?>
                    <div class="article-description" style="padding: 10px;">
						<?php echo $extension->info->excerpt; ?>
                    </div>
                    <hr/>
				<?php endif; ?>
				<?php if ( $extension->info->link ): ?>
                    <div class="buy" style="padding: 10px">
						<?php $pricing = (array) $extension->pricing;
						if ( count( $pricing ) > 1 ) {

							$price1 = min( $pricing );
							$price2 = max( $pricing );

							?>
                            <a class="button-secondary" target="_blank"
                               href="<?php echo $extension->info->link; ?>"> <?php printf( _x( 'Buy Now ($%s - $%s)', 'action', 'groundhogg' ), $price1, $price2 ); ?></a>
							<?php
						} else {

							$price = array_pop( $pricing );

							if ( $price > 0.00 ) {
								?>
                                <a class="button-secondary" target="_blank"
                                   href="<?php echo $extension->info->link; ?>"> <?php printf( _x( 'Buy Now ($%s)', 'action', 'groundhogg' ), $price ); ?></a>
								<?php
							} else {
								?>
                                <a class="button-secondary" target="_blank"
                                   href="<?php echo $extension->info->link; ?>"> <?php _ex( 'Download', 'action', 'groundhogg' ); ?></a>
								<?php
							}
						}

						?>
                    </div>
				<?php endif; ?>
            </div>
        </div>

		<?php

	}
}