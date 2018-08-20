<?php
/**
 * Benchmark Functions
 *
 * Functions to have users complete benchmarks within funnels...
 *
 * @package     groundhogg
 * @subpackage  Includes/Funnels
 * @copyright   Copyright (c) 2018, Adrian Tobey
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/**
 * Complete the benchmark and queue up the next action in the event queue
 * Also dequeues any previously enqueued events.
 *
 * @param $benchmark_id int the ID of the benchmark to be completed
 * @param $contact_id int the ID of the contact for which the benchmark is being completed
 */
function wpfn_complete_benchmark( $benchmark_id, $contact_id )
{
    do_action( 'wpfn_complete_benchmark_before', $benchmark_id );

    $funnel_id = wpfn_get_step_funnel( $benchmark_id );

    //do not run if the funnel is set to inactive.
    if ( ! wpfn_is_funnel_active( $funnel_id ) )
        return;

    /* stop previously queued events from running and set their status to skipped. */
    wpfn_dequeue_contact_funnel_events( $contact_id, $funnel_id );

    /* Rather than juist starting the next action, enter this benchmark into the queue for easy goal reporting */
    wpfn_enqueue_event( strtotime('now'), wpfn_get_step_funnel( $benchmark_id ), $benchmark_id, $contact_id );

    do_action( 'wpfn_complete_benchmark_after', $benchmark_id );
}

/**
 * Complete account_created benchmarks for the funnels.
 * Create a new contact record if one doesn't exist.
 * If one exists, use the existing contact record.
 *
 * @param $userId int the ID of the user which was created
 */
function wpfn_run_account_created_benchmark_action( $userId )
{
    //todo list of possible funnel steps.
    $user_info = get_userdata( $userId );

    if ( ! wpfn_get_contact_by_email( $user_info->user_email ) ){
        $contact_id = wpfn_quick_add_contact( $user_info->user_email, $_POST['first_name'], $_POST['last_name'] );
    } else {
        $contact = new WPFN_Contact( $user_info->user_email );
        $contact_id = $contact->getId();
    }

    $benchmarks = wpfn_get_funnel_steps_by_type( 'account_created' );

    foreach ( $benchmarks as $benchmark ) {

        $step_id = intval( $benchmark['ID'] );
        $step_order = intval( $benchmark['funnelstep_order'] );
        $funnel_id = intval( $benchmark['funnel_id'] );

        $role = wpfn_get_step_meta( $step_id, 'role', true );

        if ( ( 1 === $step_order || wpfn_contact_is_in_funnel( $contact_id,  $funnel_id ) ) && in_array( $role, $user_info->roles ) ){
            wpfn_complete_benchmark( $step_id, $contact_id );
        }
    }
}

add_action( 'user_register', 'wpfn_run_account_created_benchmark_action' );

/**
 * Run the benchmark for user role changes. Helpful for membership sites.
 *
 * @param $userId int the ID of a user.
 * @param $cur_role string the new role of the user
 * @param $old_roles array list of previous user roles.
 */
function wpfn_run_user_role_changed_benchmark( $userId, $cur_role, $old_roles )
{
    $user_info = get_userdata( $userId );

    $contact = new WPFN_Contact( $user_info->user_email );

    if ( ! $contact->getEmail() )
        return;

    $contact_id = $contact->getId();

    $benchmarks = wpfn_get_funnel_steps_by_type( 'role_changed' );

    foreach ( $benchmarks as $benchmark ) {

        $step_id = intval( $benchmark['ID'] );
        $step_order = intval( $benchmark['funnelstep_order'] );
        $funnel_id = intval( $benchmark['funnel_id'] );

        $role = wpfn_get_step_meta( $step_id, 'role', true );

        if ( ( 1 === $step_order || wpfn_contact_is_in_funnel( $contact_id,  $funnel_id ) ) && $cur_role === $role ){
            wpfn_complete_benchmark( $step_id, $contact_id );
        }
    }
}

add_action( 'set_user_role', 'wpfn_run_user_role_changed_benchmark', 10, 3 );

/**
 * Complete the Page View benchmark.
 * todo Review this goal. The hook needs to be changed probably...
 *
 * @param $post_object object post object goes unused.
 */
function wpfn_complete_page_view_benchmark( $post_object )
{
    if ( is_admin() )
        return;

    $contact = wpfn_get_the_contact();

    if ( ! $contact )
        return;

    $contact_id = $contact->getId();

    $benchmarks = wpfn_get_funnel_steps_by_type( 'page_view' );

    if ( ! $benchmarks )
        return;

    foreach ( $benchmarks as $benchmark ) {

        $step_id = intval( $benchmark['ID'] );
        $step_order = intval( $benchmark['funnelstep_order'] );
        $funnel_id = intval( $benchmarks['funnel_id'] );

        $page_id = wpfn_get_step_meta( $step_id, 'page_id', true );

        if ( ( 1 === $step_order || wpfn_contact_is_in_funnel( $contact_id,  $funnel_id ) ) && $page_id === get_the_ID() ){
            wpfn_complete_benchmark( $step_id, $contact_id );
        }
    }
}

add_action( 'the_post', 'wpfn_complete_page_view_benchmark' );

/**
 * Complete the tag removed benchmark
 *
 * @param $contact_id int the ID of the contact
 * @param $tag_id int the ID of the tag which was just removed
 */
function wpfn_complete_tag_removed_benchmark( $contact_id, $tag_id )
{
    $benchmarks = wpfn_get_funnel_steps_by_type( 'tag_removed' );

    if ( ! $benchmarks )
        return;

    foreach ( $benchmarks as $benchmark ) {

        $step_id = intval( $benchmark['ID'] );
        $step_order = intval( $benchmark['funnelstep_order'] );
        $funnel_id = intval( $benchmarks['funnel_id'] );

        $tags = wpfn_get_step_meta( $step_id, 'tags', true );

        if ( ( 1 === $step_order || wpfn_contact_is_in_funnel( $contact_id,  $funnel_id ) ) && in_array( $tag_id, $tags ) ){
            wpfn_complete_benchmark( $step_id, $contact_id );
        }
    }
}

add_action( 'wpfn_tag_removed' , 'wpfn_complete_tag_removed_benchmark' , 10, 2 );

/**
 * run the tag applied benchmark
 *
 * @param $contact_id int the ID of the contact
 * @param $tag_id int the ID of the tag
 */
function wpfn_complete_tag_applied_benchmark( $contact_id, $tag_id )
{
    $benchmarks = wpfn_get_funnel_steps_by_type( 'tag_applied' );

    if ( ! $benchmarks )
        return;

    foreach ( $benchmarks as $benchmark ) {

        $step_id = intval( $benchmark['ID'] );
        $step_order = intval( $benchmark['funnelstep_order'] );
        $funnel_id = intval( $benchmarks['funnel_id'] );

        $tags = wpfn_get_step_meta( $step_id, 'tags', true );

        if ( ( 1 === $step_order || wpfn_contact_is_in_funnel( $contact_id,  $funnel_id ) ) && in_array( $tag_id, $tags ) ){
            wpfn_complete_benchmark( $step_id, $contact_id );
        }
    }
}

add_action( 'wpfn_tag_applied' , 'wpfn_complete_tag_applied_benchmark' , 10, 2 );