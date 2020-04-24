<?php

namespace Groundhogg\Reporting\New_Reports;


use Groundhogg\Classes\Activity;
use Groundhogg\Email;
use Groundhogg\Plugin;
use Groundhogg\Step;
use function Groundhogg\get_db;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\percentage;

class Table_Worst_Performing_Emails extends Base_Table_Report {

	public function get_label() {
		return [
			__( 'Emails', 'groundhogg' ),
			__( 'Open Rate', 'groundhogg' ),
			__( 'Click Thorough Rate', 'groundhogg' )
		];
	}

	protected function get_funnel_id() {
		return get_request_var( 'data' )['funnel_id'];
	}

	protected function get_table_data() {

		$funnel_id = absint( $this->get_funnel_id() );

		if ( $this->get_funnel_id() ) {

			$steps = get_db( 'steps' )->query( [
				'funnel_id' => $funnel_id,
				'step_type' => 'send_email'
			] );


			if ( empty( $steps ) ) {
				return [];
			}


			$email_ids = [];

			foreach ( $steps as $step ) {
				$email_ids[] = absint( get_db( 'stepmeta' )->get_meta( $step->ID, 'email_id', true ) );
			}

			$emails = get_db( 'emails' )->query( [
				'status' => 'ready',
				'ID'     => $email_ids
			] );


		} else {
			$emails = get_db( 'emails' )->query( [
				'status' => 'ready'
			] );
		}


		$list = [];

		foreach ( $emails as $email ) {

			$email  = new Email( $email->ID );
			$report = $email->get_email_stats( $this->start, $this->end );

			$title = $email->get_title();


			if ( $report['total'] > 0 ) {

				if ( ( percentage( $report['total'], $report['opened'] ) < 20.0 ) || ( percentage( $report ['opened'], $report ['clicked'] ) < 20.0 ) ) {

					$list[] = [
						'data'    => percentage( $report['total'], $report['opened'] ),
						'label'   => $title,
						'url'     => admin_url( sprintf( 'admin.php?page=gh_emails&action=edit&email=%s', $email->ID ) ),
						'clicked' => percentage( $report ['opened'], $report ['clicked'] )
					];
				}

			}

		}

		$list = $this->normalize_data( $list );

		foreach ( $list as $i => $datum ) {

			$datum['label']   = html()->wrap( $datum['label'], 'a', [
				'href'  => $datum['url'],
				'class' => 'number-total'
			] );
			$datum['data']    = $datum['data'] . '%';
			$datum['clicked'] = $datum['clicked'] . '%';

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
	protected
	function normalize_datum(
		$item_key, $item_data
	) {
		return [
			'label'   => $item_data ['label'],
			'data'    => $item_data ['data'],
			'url'     => $item_data ['url'],
			'clicked' => $item_data ['clicked'],

		];
	}


	/**
	 * Sort stuff
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	public function sort( $a, $b ) {
		return $a['data'] - $b['data'];
	}

}