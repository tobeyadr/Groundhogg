<?php

namespace Groundhogg\Reporting\New_Reports;


use Groundhogg\Classes\Activity;
use Groundhogg\Contact_Query;
use Groundhogg\Email;
use Groundhogg\Event;
use Groundhogg\Funnel;
use Groundhogg\Plugin;
use Groundhogg\Step;
use function Groundhogg\_nf;
use function Groundhogg\admin_page_url;
use function Groundhogg\get_db;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\percentage;

class Table_Email_Funnels_Used_In extends Base_Table_Report {

	public function get_label() {
		return [
			__( 'Funnel', 'groundhogg' ),
			__( 'Step', 'groundhogg' ),
			__( 'Sent', 'groundhogg' ),
			__( 'Open Rate', 'groundhogg' ),
			__( 'Click Thru Rate', 'groundhogg' ),
		];

	}

	protected function get_table_data() {

		$email_id = $this->get_email_id();

		$steps = get_db( 'stepmeta' )->query( [
			'meta_key'   => 'email_id',
			'meta_value' => $email_id
		] );

		$step_ids = wp_parse_id_list( wp_list_pluck( $steps, 'step_id' ) );

		$data = [];

		foreach ( $step_ids as $step_id ) {

			$step = new Step( $step_id );

			if ( ! $step->exists() ) {
				continue;
			}

			$sent = get_db( 'events' )->count( [
				'step_id'   => $step->get_id(),
				'funnel_id' => $step->get_funnel_id(),
				'status'    => Event::COMPLETE,
				'before'    => $this->end,
				'after'     => $this->start
			] );

			$opened = get_db( 'activity' )->count( [
				'select'        => 'DISTINCT contact_id',
				'activity_type' => Activity::EMAIL_OPENED,
				'email_id'      => $email_id,
				'step_id'       => $step->get_id(),
				'funnel_id'     => $step->get_funnel_id(),
				'before'        => $this->end,
				'after'         => $this->start
			] );

			$clicked = get_db( 'activity' )->count( [
				'select'        => 'DISTINCT contact_id',
				'activity_type' => Activity::EMAIL_CLICKED,
				'email_id'      => $email_id,
				'step_id'       => $step->get_id(),
				'funnel_id'     => $step->get_funnel_id(),
				'before'        => $this->end,
				'after'         => $this->start
			] );

			$data[] = [
				// Funnel
				 'funnel' => $step->get_funnel_title() ,
				// Step
				"step" => $step->get_step_title() ,
				// Sent
				'sent' => _nf( $sent ) ,
				// Opens
				 'opens' => percentage( $sent, $opened ) . '%' ,
				// Clicks
				'clicks' => percentage( $opened, $clicked ) . '%',
				'step_object' => $step->get_as_array()

			];

		}

		return $data;

	}

	protected function normalize_datum( $item_key, $item_data ) {
		// TODO: Implement normalize_datum() method.
	}
}