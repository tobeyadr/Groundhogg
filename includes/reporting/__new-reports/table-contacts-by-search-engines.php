<?php

namespace Groundhogg\Reporting\New_Reports;


use Groundhogg\Plugin;
use function Groundhogg\admin_page_url;
use function Groundhogg\get_db;
use function Groundhogg\html;
use function Groundhogg\percentage;

class Table_Contacts_By_Search_Engines extends Base_Table_Report {

	function column_title() {
		// TODO: Implement column_title() method.
	}

	public function get_label() {
		return [
			__( 'Search Engines', 'groundhogg' ),
			__( 'Contacts', 'groundhogg' ),
		];
	}

	protected function get_table_data() {

		$rows = get_db( 'contactmeta' )->query( [
			'contact_id' => $this->get_new_contact_ids_in_time_period(),
			'meta_key'   => 'lead_source'
		], false );

		$values         = wp_list_pluck( $rows, 'meta_value' );
		$counts         = array_count_values( $values );
		$search_engines = $this->get_search_engines();
		$return         = [];

		foreach ( $counts as $datum => $num_contacts ) {
			if ( filter_var( $datum, FILTER_VALIDATE_URL ) ) {
				$test_lead_source = wp_parse_url( $datum, PHP_URL_HOST );
				$test_lead_source = str_replace( 'www.', '', $test_lead_source );
				foreach ( $search_engines as $engine_name => $atts ) {
					$urls = $atts[0]['urls'];
					if ( $this->in_urls( $test_lead_source, $urls ) ) {
						if ( isset( $return[ $engine_name ] ) ){
							$return[ $engine_name ] += $num_contacts;
						} else {
							$return[ $engine_name ] = $num_contacts;
						}
					}
				}
			}
		}

//		$this->parse_table_data();

		$data  = $this->normalize_data( $return );
		$total = array_sum( wp_list_pluck( $data, 'data' ) );

		foreach ( $data as $i => $datum ) {

			$sub_tal    = $datum['data'];
			$percentage = ' (' . percentage( $total, $sub_tal ) . '%)';

			$datum['data'] = html()->wrap( $datum['data'] . $percentage, 'a', [
				'href'  => $datum['url'],
				'class' => 'number-total'
			] );
			unset( $datum['url'] );
			$data[ $i ] = $datum;
		}

		return $data;
	}

	/**
	 * Normalize a datum
	 *
	 * @param $item_key
	 * @param $item_data
	 *
	 * @return array
	 */
	protected function normalize_datum( $item_key, $item_data ) {
		return [
			'label' => $item_key,
			'data'  => $item_data,
			'url'   => admin_page_url( 'gh_contacts', [
				'meta_key'     => 'lead_source',
				'meta_value'   => strtolower( $item_key ),
				'meta_compare' => 'RLIKE'
			] ),
		];
	}

	/**
	 * Setup the search_engines array from the yaml file in lib
	 */
	public function get_search_engines() {
		if ( ! class_exists( 'Spyc' ) ) {
			include_once GROUNDHOGG_PATH . 'includes/lib/yaml/Spyc.php';
		}

		Return \Spyc::YAMLLoad( GROUNDHOGG_PATH . 'includes/lib/potential-known-leadsources/SearchEngines.yml' );
	}

	/**
	 * Special search function for comparing lead sources to potential search engine matches.
	 *
	 * @param $search string the URL in question
	 * @param $urls array list of string potential matches...
	 *
	 * @return bool
	 */
	private function in_urls( $search, $urls ) {

		foreach ( $urls as $url ) {

			/* Given YAML dataset uses .{} as sequence for match all expression, convert into regex friendly */
			$url     = str_replace( '.{}', '\.{1,3}', $url );
			$url     = str_replace( '{}.', '.{1,}?\.?', $url );
			$pattern = '#' . $url . '#';
			if ( preg_match( $pattern, $search ) ) {
				return true;
			}
		}

		return false;
	}
}