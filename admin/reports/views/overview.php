<?php

namespace Groundhogg\Admin\Reports\Views;

// Overview

function get_img_url( $img ) {
	echo esc_url( GROUNDHOGG_ASSETS_URL . 'images/reports/' . $img );
}

function quick_stat_report( $args = [] ) {

	$args = wp_parse_args( $args, [
		'id'    => uniqid( 'groundhogg_' ),
		'title' => 'Report',
		'info'  => 'Some interesting data...'
	] );

	?>

    <div class="groundhogg-quick-stat" id="<?php esc_attr_e( $args['id'] ); ?>">
        <div class="groundhogg-quick-stat-title"><?php esc_html_e( $args['title'] ) ?></div>
        <div class="groundhogg-quick-stat-info"></div>
        <div class="groundhogg-quick-stat-number">1234</div>
        <div class="groundhogg-quick-stat-previous green">
            <span class="groundhogg-quick-stat-arrow up"></span>
            <span class="groundhogg-quick-stat-prev-percent">25%</span>
        </div>
        <div class="groundhogg-quick-stat-compare">vs. Previous 30 Days</div>
    </div>
	<?php
}

?>
<div class="groundhogg-report">
    <h2 class="title"><?php _e( 'New Contacts', 'groundhogg' ); ?></h2>
    <div id="chart-new-contacts">
        <img src="<?php get_img_url( 'new-contacts.PNG' ); ?>">
    </div>
</div>

<div class="groundhogg-quick-stats">
    <div class="groundhogg-report">

		<?php quick_stat_report( [
		        'id' => 'total_new_contacts',
                'title' => __( 'New Contacts', 'groundhogg' )
        ] ); ?>

		<?php quick_stat_report( [
		        'id' => 'total_confirmed_contacts',
		        'title' => __( 'Confirmed Contacts', 'groundhogg' ),
        ] ); ?>

		<?php quick_stat_report( [
		        'id' => 'total_engaged_contacts',
		        'title' => __( 'Engaged Contacts', 'groundhogg' ),
        ] ); ?>

		<?php quick_stat_report( [
		        'id' => 'total_unsubscribes',
		        'title' => __( 'Unsubscribes', 'groundhogg' ),
		] ); ?>
    </div>
</div>

<div class="groundhogg-quick-stats">
    <div class="groundhogg-report">

		<?php quick_stat_report( [
			'id' => 'total_emails_sent',
			'title' => __( 'Emails Sent', 'groundhogg' )
		] ); ?>

		<?php quick_stat_report( [
			'id' => 'email_open_rate',
			'title' => __( 'Open Rate', 'groundhogg' ),
		] ); ?>

		<?php quick_stat_report( [
			'id' => 'email_click_rate',
			'title' => __( 'Click Rate', 'groundhogg' ),
		] ); ?>

<!--		--><?php //quick_stat_report( [
//			'id' => 'unsubscribes',
//			'title' => __( 'Unsubscribes', 'groundhogg' ),
//		] ); ?>
    </div>
</div>