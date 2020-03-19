<?php

namespace Groundhogg\Reporting\New_Reports;

use Groundhogg\Classes\Activity;
use Groundhogg\Contact_Query;
use Groundhogg\DB\DB;
use Groundhogg\Event;
use Groundhogg\Funnel;
use Groundhogg\Plugin;
use Groundhogg\Preferences;
use function Groundhogg\get_db;
use function Groundhogg\get_request_var;
use function Groundhogg\isset_not_empty;

class Chart_Contacts_By_Country extends Base_Chart_Report {

	protected function get_type() {
		return 'doughnut';
	}

	protected function get_datasets() {

		$data = $this->get_optin_status();

		return [
			'labels'   => $data[ 'label' ],
			'datasets' => [
				[
					'data'            => $data[ 'data' ],
					'backgroundColor' => $data[ 'color' ]
				]
			]
		];
	}

	protected function get_options() {
		return $this->get_pie_chart_options();
	}


	protected function get_optin_status() {

		$contacts = get_db( 'contacts' )->query( [
			'date_query' => [
				'after'  => date( 'Y-m-d H:i:s', $this->start ),
				'before' => date( 'Y-m-d H:i:s', $this->end ),
			]
		] );

		$contacts = wp_parse_id_list( wp_list_pluck( $contacts, 'ID' ) );


		$rows = get_db( 'contactmeta' )->query( [
			'contact_id' =>$contacts,
			'meta_key' => 'country'
		], false );



		$values = wp_list_pluck( $rows, 'meta_value'  );

		$counts = array_count_values( $values );

		$data  = [];
		$label = [];
		$color = [];

		// normalize data
		foreach ( $counts as $key => $datum ) {
			$normalized = $this->normalize_datum( $key, $datum );
			$label []   = $normalized [ 'label' ];
			$data[]     = $normalized [ 'data' ];
			$color[]    = $normalized [ 'color' ];

		}

		return [
			'label' => $label,
			'data'  => $data,
			'color' => $color
		];

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
		$label = ! empty( $item_key ) ? Plugin::$instance->utils->location->get_countries_list( $item_key ): __( 'Unknown' );
		$data  = $item_data;
		$url   = ! empty( $item_key ) ? admin_url( sprintf( 'admin.php?page=gh_contacts&meta_key=country&meta_value=%s', $item_key ) ) : '#';


		return [
			'label' => $label,
			'data'  => $data,
//			'url'  =>  $url
			'color' => $this->get_random_color()
		];
	}

}