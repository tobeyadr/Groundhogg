<?php
/**
 * Form Filled
 *
 * This will run whenever a form is completed
 *
 * @package     Elements
 * @subpackage  Elements/Benchmarks
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Form_Filled extends WPGH_Funnel_Step
{

    /**
     * @var string
     */
    public $type    = 'form_fill';

    /**
     * @var string
     */
    public $group   = 'benchmark';

    /**
     * @var string
     */
    public $icon    = 'form-filled.png';

    /**
     * @var string
     */
    public $name    = 'Web Form';

    /**
     * Add the completion action
     *
     * WPGH_Form_Filled constructor.
     */
    public function __construct()
    {
        $this->name         = _x( 'Web Form', 'step_name', 'groundhogg' );
        $this->description  = _x( 'Use this form builder to create forms and display them on your site with shortcodes.', 'step_description', 'groundhogg' );

        parent::__construct();

        add_action( 'wpgh_form_submit', array( $this, 'complete' ), 10, 3 );

        if ( is_admin() && isset( $_GET['page'] ) && $_GET[ 'page' ] === 'gh_funnels' && isset($_REQUEST[ 'action' ]) && $_REQUEST[ 'action' ] === 'edit' ){
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ));
            add_action( 'admin_footer', array( $this, 'modal_form' ) );
        }

        /* Backwards compat */
        if ( wpgh_is_option_enabled( 'gh_disable_api' ) ){
            add_action( 'wp_ajax_gh_form_impression', array( $this, 'track_impression' ) );
            add_action( 'wp_ajax_nopriv_gh_form_impression', array( $this, 'track_impression' ) );
        }

    }

    /**
     * Enqueue the form builder JS in the admin area
     */
    public function scripts()
    {
        wp_enqueue_script( 'groundhogg-admin-form-builder' );
    }

    /**
     * @param $step WPGH_Step
     */
    public function settings( $step )
    {
        $shortcode = sprintf('[gh_form id="%d" title="%s"]', $step->ID, $step->title );
        $script    = sprintf('<script id="%s" type="text/javascript" src="%s?ghFormIframeJS=1&formId=%s"></script>', 'ghFrame' . $step->ID, site_url(), $step->ID );

        $form = $step->get_meta( 'form' );

        if ( empty( $form ) ){
            $form = "[row]\n[col size=\"1/2\"]\n[first required=\"true\" label=\"First Name *\" placeholder=\"John\"]\n[/col]\n[col size=\"1/2\"]\n[last required=\"true\" label=\"Last Name *\" placeholder=\"Doe\"]\n[/col]\n[/row]\n[row]\n[email required=\"true\" label=\"Email *\" placeholder=\"email@example.com\"]\n[/row]\n[submit text=\"Submit\"]";
        }

        $ty_page = $step->get_meta( 'success_page' );

        if ( empty( $ty_page ) ){
            $ty_page = site_url( '/thank-you/' );
        }

        ?>

        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <?php esc_attr_e( 'Shortcode:', 'groundhogg' ); ?>
                    <br/>
                    <br/>
                    <?php esc_attr_e( 'JS Script:', 'groundhogg' ); ?>
                </th>
                <td>

                    <strong>
                    <input
                            type="text"
                            onfocus="this.select()"
                            class="regular-text code"
                            value="<?php echo esc_attr( $shortcode ); ?>"
                            readonly>
                    </strong>
                    <input
                            type="text"
                            onfocus="this.select()"
                            class="regular-text code"
                            value="<?php echo esc_attr( $script ); ?>"
                            readonly>
                    </strong>
                    <p>
                        <?php echo WPGH()->html->modal_link( array(
                            'title'     => __( 'Preview' ),
                            'text'      => __( 'Preview' ),
                            'footer_button_text' => __( 'Close' ),
                            'id'        => '',
                            'class'     => 'button button-secondary',
                            'source'    => $step->prefix( 'preview' ),
                            'height'    => 700,
                            'width'     => 600,
                            'footer'    => 'true',
                            'preventSave'    => 'true',
                        ) );
                        ?>

                        <!-- COPY IFRAME LINK BUTTON GOES HERE -->

                    </p>
                    <div class="hidden" id="<?php echo $step->prefix( 'preview' ); ?>" >
                        <div style="padding-top: 30px;">
                            <div class="notice notice-warning">
                                <p><?php _e( 'Not all CSS rules are loaded in the admin area. Frontend results may differ.', 'groundhogg' ); ?></p>
                            </div>
                            <?php $preview = new WPGH_Form( array( 'id' => $step->ID ) );
                            echo $preview->preview(); ?>
                        </div>
                    </div>
                </td>
            </tr><tr>
                <th>
                    <?php esc_attr_e( 'Thank You Page:', 'groundhogg' ); ?>
                </th>
                <td>
                    <?php

                    $args = array(
                        'type'      => 'text',
                        'id'        => $step->prefix( 'success_page' ),
                        'name'      => $step->prefix( 'success_page' ),
                        'title'     => __( 'Thank You Page' ),
                        'value'     => $ty_page
                    );

                    echo WPGH()->html->link_picker( $args ); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <table>
            <tbody>
            <tr>
                <td>
                    <div class="form-editor">
                        <div class="form-buttons">
                            <?php

                            $buttons = array(
                                array(
                                    'text' => __( 'First', 'groundhogg' ),
                                    'class' => 'button button-secondary first'
                                ),
                                array(
                                    'text' => __( 'Last', 'groundhogg' ),
                                    'class' => 'button button-secondary last'
                                ),
                                array(
                                    'text' => __( 'Email', 'groundhogg' ),
                                    'class' => 'button button-secondary email'
                                ),
                                array(
                                    'text' => __( 'Phone', 'groundhogg' ),
                                    'class' => 'button button-secondary phone'
                                ),
                                array(
                                    'text' => __( 'Address', 'groundhogg' ),
                                    'class' => 'button button-secondary address'
                                ),
                                array(
                                    'text' => __( 'Submit', 'groundhogg' ),
                                    'class' => 'button button-secondary submit'
                                ),
                                array(
                                    'text' => __( 'Row', 'groundhogg' ),
                                    'class' => 'button button-secondary row'
                                ),
                                array(
                                    'text' => __( 'Col', 'groundhogg' ),
                                    'class' => 'button button-secondary col'
                                ),
                                array(
                                    'text' => __( 'ReCaptcha', 'groundhogg' ),
                                    'class' => 'button button-secondary recaptcha'
                                ),
                                array(
                                    'text' => __( 'GDPR', 'groundhogg' ),
                                    'class' => 'button button-secondary gdpr'
                                ),
                                array(
                                    'text' => __( 'Terms', 'groundhogg' ),
                                    'class' => 'button button-secondary terms'
                                ),
                                array(
                                    'text' => __( 'Text', 'groundhogg' ),
                                    'class' => 'button button-secondary text'
                                ),
                                array(
                                    'text' => __( 'Textarea', 'groundhogg' ),
                                    'class' => 'button button-secondary textarea'
                                ),
                                array(
                                    'text' => __( 'Number', 'groundhogg' ),
                                    'class' => 'button button-secondary number'
                                ),
                                array(
                                    'text' => __( 'Dropdown', 'groundhogg' ),
                                    'class' => 'button button-secondary dropdown'
                                ),
                                array(
                                    'text' => __( 'Radio', 'groundhogg' ),
                                    'class' => 'button button-secondary radio'
                                ),
                                array(
                                    'text' => __( 'Checkbox', 'groundhogg' ),
                                    'class' => 'button button-secondary checkbox'
                                ),
                                array(
                                    'text' => __( 'Date', 'groundhogg' ),
                                    'class' => 'button button-secondary date'
                                ),
                                array(
                                    'text' => __( 'Time', 'groundhogg' ),
                                    'class' => 'button button-secondary time'
                                ),
                                array(
                                    'text' => __( 'File', 'groundhogg' ),
                                    'class' => 'button button-secondary file'
                                ),
                            );

                            $buttons = apply_filters( 'wpgh_form_builder_buttons', $buttons );

                            foreach ( $buttons as $button ){

                                $args = wp_parse_args( $button, array(
                                    'text'      => __( 'Field', 'groundhogg' ),
                                    'title'      => sprintf( __( 'Insert Field: %s', 'groundhogg' ), $button[ 'text' ] ),
                                    'class'     => 'button button-secondary column',
                                    'source'    => 'form-field-editor',
                                    'footer_button_text'    => __( 'Insert Field', 'groundhogg' ),
                                    'width' => 600,
                                    'height' => 600
                                ) );

                                echo WPGH()->html->modal_link( $args );
                            } ?>
                        </div>

                        <?php

                        $code = $this->prettify( $form );
                        $rows = min( substr_count( $code, "\n" ) + 1, 15 );

                        $args = array(
                            'id'    => $step->prefix( 'form' ),
                            'name'  => $step->prefix( 'form' ),
                            'value' => $code,
                            'class' => 'code form-html',
                            'cols'  => 64,
                            'rows'  => $rows,
                            'attributes' => " style='white-space: nowrap;'"
                        ); ?>

                        <?php echo WPGH()->html->textarea( $args ) ?>
                    </div>
                </td>
            </tr>
        </table>

        <?php
    }


    /**
     * Prettifies the shortcode text to make it easier to identify and read
     *
     * @param $code string of shortcode
     * @return string
     */
    private function prettify( $code )
    {

        $pretty = $code;

        /* Remove all newlines & whitespace */
        $code = trim( $code, " \t\n\r" );
        $code = preg_replace( '/(\])\s*(\[)/', "$1$2", $code );
        $code = preg_replace( '/(\])/', "$1" . PHP_EOL, $code );
        $codes = explode( PHP_EOL, $code );

//        var_dump( $codes );

        $depth = 0;
        $pretty = '';

        foreach ( $codes as $i => $shortcode ){

            $shortcode = trim( $shortcode, " \t\n\r" );
            if ( empty( $shortcode ) ){
                continue;
            }

            /* Opening tag */
            if ( preg_match( '/\[(col|row)\b[^\]]*\]/', $shortcode ) ) {
                $pretty .= str_repeat( str_repeat( " ", 4 ), $depth ) . $shortcode;
                $depth++;
                /* Closing tag */
            } else if ( preg_match( '/\[\/(col|row)\]/', $shortcode ) ){
//                var_dump( $shortcode) ;
                $depth--;
                $pretty .= str_repeat( str_repeat( " ", 4 ), $depth ) . $shortcode;
                /* Other stuff */
            } else {
                $pretty .= str_repeat( str_repeat( " ", 4 ), $depth ) . $shortcode;
            }

            $pretty .= PHP_EOL;

        }

        return $pretty;

    }

    public function modal_form()
    {
        ?>
        <div id="form-field-editor" class="form-field-editor hidden">
            <form class="form-field-form" id="form-field-form" method="post" action="">
                <table class="form-table">
                    <tbody>
                    <tr id="gh-field-required">
                        <th><?php _e( 'Required Field', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->checkbox( array( 'id' => 'field-required', 'name' => 'required', 'label' => __( 'Yes' ), 'value' => 'true' ) );
                            ?></td>
                    </tr>
                    <tr id="gh-field-label">
                        <th><?php _e( 'Label', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-label', 'name' => 'label' ) );
                            ?><p class="description"><?php _e( 'The field label.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <tr id="gh-field-text">
                        <th><?php _e( 'Text', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-text', 'name' => 'text' ) );
                            ?><p class="description"><?php _e( 'The button text.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <tr id="gh-field-placeholder">
                        <th><?php _e( 'Placeholder', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-placeholder', 'name' => 'placeholder' ) );
                            ?><p class="description"><?php _e( 'The ghost text within the field.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <tr id="gh-field-name">
                        <th><?php _e( 'Name', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-name', 'name' => 'name' ) );
                            ?><p class="description"><?php _e( 'This will be the custom field name. I.E. {meta.name}', 'groundhogg' ) ?></p></td>
                    </tr>

                    <!--BEGIN NUMBER OPTIONS -->
                    <tr id="gh-field-min">
                        <th><?php _e( 'Min', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->number( array( 'id' => 'field-min', 'name' => 'min', 'class' => 'input' ) );
                            ?><p class="description"><?php _e( 'The minimum number a user can enter.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <tr id="gh-field-max">
                        <th><?php _e( 'Max', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->number( array( 'id' => 'field-max', 'name' => 'max', 'class' => 'input' ) );
                            ?><p class="description"><?php _e( 'The max number a user can enter.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <!-- END NUMBER OPTIONS -->

                    <tr id="gh-field-value">
                        <th><?php _e( 'Value', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-value', 'name' => 'value' ) );
                            ?><p class="description"><?php _e( 'The default value of the field.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <tr id="gh-field-tag">
                        <th><?php _e( 'Add Tag', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->tag_picker( array( 'id' => 'field-tag', 'name' => 'tag', 'class' => 'gh-single-tag-picker', 'multiple' => false ) );
                            ?><p class="description"><?php _e( 'Add a tag when this checkbox is selected.', 'groundhogg' ); ?></p></td>
                    </tr>

                    <tr id="gh-field-options">
                        <th><?php _e( 'Options', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->textarea( array( 'id' => 'field-options', 'name' => 'options', 'cols' => 50, 'rows' => '5', 'class' => 'hidden' ) );
                            ?>
                            <div id='gh-option-table'>
                                <div class='option-wrapper' style='margin-bottom:10px;'>
                                    <div style='display: inline-block;width: 170px;vertical-align: top;'>
                                        <input type='text' class='input' style='float: left' name='option[]' placeholder='Option Text'>
                                    </div>
                                    <div style='display: inline-block;width: 220px;vertical-align: top;'>
                                        <select class='gh-single-tag-picker' name='tags[]' style='max-width: 140px;'></select>
                                    </div>
                                    <div style='display: inline-block;width: 20px;vertical-align: top;'>
                                        <span  class="row-actions"><span class="delete"><a style="text-decoration: none" href="javascript:void(0)" class="deleteOption"><span class="dashicons dashicons-trash"></span></a></span></span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="button-secondary addoption"><?php _ex( 'Add Option', 'action', 'groundhogg' ); ?></button>
<!--                            <button type="button" id="btn-saveoption" class="button-primary">--><?php //_ex( 'Save Options', 'action', 'groundhogg' ); ?><!--</button>-->
                            <p class="description"><?php _e( 'Enter option name to add option. Tags are optional.You need to save options when you make changes.', 'groundhogg' ) ?></p>
                        </td>
                    </tr>
                    <tr id="gh-field-multiple">
                        <th><?php _e( 'Allow Multiple Selections', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->checkbox( array( 'id' => 'field-multiple', 'name' => 'multiple', 'label' => __( 'Yes' ) ) );
                            ?></td>
                    </tr>
                    <tr id="gh-field-default">
                        <th><?php _e( 'Default', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-default', 'name' => 'default', 'cols' => 50, 'rows' => '5' ) );
                            ?><p class="description"><?php _e( 'The blank option which appears at the top of the list.', 'groundhogg' ) ?></p></td>
                    </tr>

                    <!-- BEGIN COLUMN OPTIONS -->
                    <tr id="gh-field-width">
                        <th><?php _e( 'Width', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->dropdown( array(
                                'id' => 'field-width',
                                'name' => 'width',
                                'options' => array(
                                    '1/2' => '1/2',
                                    '1/3' => '1/3',
                                    '1/4' => '1/4',
                                    '2/3' => '2/3',
                                    '3/4' => '3/4',
                                    '1/1' => '1/1'
                                ) ) );
                            ?><p class="description"><?php _e( 'The width of the column.', 'groundhogg' ); ?></p></td>
                    </tr>
                    <!-- END COLUMN OPTIONS -->

                    <!-- BEGIN CAPTCHA OPTIONS -->
                    <tr id="gh-field-captcha-theme">
                        <th><?php _e( 'Theme', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->dropdown( array( 'id' => 'field-theme', 'name' => 'captcha-theme', 'options' => array(
                                    'light' => 'Light',
                                    'dark' => 'Dark',
                                ) ) );
                            ?><p class="description"><?php _e( 'The Captcha Theme.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <tr id="gh-field-captcha-size">
                        <th><?php _e( 'Theme', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->dropdown( array( 'id' => 'field-captcha-size', 'name' => 'captcha-size', 'options' => array(
                                    'normal' => 'Normal',
                                    'compact' => 'Compact',
                                ) ) );
                            ?><p class="description"><?php _e( 'The Captcha Size.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <!-- END CAPTCHA OPTIONS -->

                    <!-- BEGIN DATE OPTIONS -->
                    <tr id="gh-field-min_date">
                        <th><?php _e( 'Min Date', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'type' => 'date', 'id' => 'field-min_date', 'name' => 'min_date', 'placeholder' => 'YYY-MM-DD or +3 days or -1 days' ) );
                            ?><p class="description"><?php _e( 'The minimum date a user can enter. You can enter a dynamic date or static date.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <tr id="gh-field-max_date">
                        <th><?php _e( 'Max Date', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'type' => 'date', 'id' => 'field-max_date', 'name' => 'max_date', 'placeholder' => 'YYY-MM-DD or +3 days or -1 days' ) );
                            ?><p class="description"><?php _e( 'The maximum date a user can enter. You can enter a dynamic date or static date.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <!-- END DATE OPTIONS -->

                    <!-- BEGIN TIME OPTIONS -->
                    <tr id="gh-field-min_time">
                        <th><?php _e( 'Min Time', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'type' => 'time', 'id' => 'field-min_time', 'name' => 'min_time' ) );
                            ?><p class="description"><?php _e( 'The minimum time a user can enter. You can enter a dynamic time or static time.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <tr id="gh-field-max_time">
                        <th><?php _e( 'Max Time', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'type' => 'time', 'id' => 'field-max_time', 'name' => 'max_time' ) );
                            ?><p class="description"><?php _e( 'The maximum time a user can enter. You can enter a dynamic time or static time.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <!-- END TIME OPTIONS -->

                    <!-- BEGIN FILE OPTIONS -->
                    <tr id="gh-field-max_file_size">
                        <th><?php _e( 'Max File Size', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->number( array( 'id' => 'field-max_file_size', 'name' => 'max_file_size', 'placeholder' => '1000000', 'min' => 0, 'max' => wp_max_upload_size() * 1000000 ) );
                            ?><p class="description"><?php printf( __( 'Maximum size a file can be <b>in Bytes</b>. Your max upload size is %d Bytes.', 'groundhogg' ), wp_max_upload_size() );?></p></td>
                    </tr>
                    <tr id="gh-field-file_types">
                        <th><?php _e( 'Accepted File Types', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-file_types', 'name' => 'file_types', 'placeholder' => '.pdf,.txt,.doc,.docx' ) );
                            ?><p class="description"><?php _e( 'The types of files a user may upload (comma separated). Leave empty to not specify.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <!-- END FILE OPTIONS -->

                    <!-- BEGIN EXTENSION PLUGIN CUSTOM OPTIONS -->
                    <?php do_action(  'wpgh_extra_form_settings' ); ?>
                    <!-- END EXTENSION PLUGIN CUSTOM OPTIONS -->

                    <!-- BEGIN CSS OPTIONS -->
                    <tr id="gh-field-id">
                        <th><?php _e( 'CSS ID', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-id', 'name' => 'id' ) );
                            ?><p class="description"><?php _e( 'Use to apply CSS.' , 'groundhogg' ) ?></p></td>
                    </tr>
                    <tr id="gh-field-class">
                        <th><?php _e( 'CSS Class', 'groundhogg' ) ?></th>
                        <td><?php
                            echo WPGH()->html->input( array( 'id' => 'field-class', 'name' => 'class' ) );
                            ?><p class="description"><?php _e( 'Use to apply CSS.', 'groundhogg' ) ?></p></td>
                    </tr>
                    <!-- END CSS OPTIONS -->

                    </tbody>
                </table>
            </form>
        </div>
        <?php
    }


    /**
     * Extend the Form reporting VIEW with impressions vs. submissions...
     *
     * @param $step WPGH_Step
     */
    public function reporting($step)
    {
        $start_time = WPGH()->menu->funnels_page->reporting_start_time;
        $end_time   = WPGH()->menu->funnels_page->reporting_end_time;

        $cquery = new WPGH_Contact_Query();

        $num_events_completed = $cquery->query( array(
            'count' => true,
            'report' => array(
                'start' => $start_time,
                'end'   => $end_time,
                'step'  => $step->ID,
                'funnel'=> $step->funnel_id,
                'status'=> 'complete'
            )
        ) );

        $num_impressions = WPGH()->activity->count(array(
            'start'     => $start_time,
            'end'       => $end_time,
            'step_id'   => $step->ID,
            'activity_type' => 'form_impression'
        ));

        ?>
        <p class="report">
            <span class="impressions"><?php _e( 'Views: '); ?>
                <strong>
                    <?php echo $num_impressions; ?>
                </strong>
            </span> |
                <span class="submissions"><?php _e( 'Fills: ' ); ?><strong><a target="_blank" href="<?php echo admin_url( 'admin.php?page=gh_contacts&view=report&status=complete&funnel=' . $step->funnel_id . '&step=' . $step->ID . '&start=' . $start_time . '&end=' . $end_time ); ?>"><?php echo $num_events_completed; ?></a></strong></span> |
            <span class="cvr" title="<?php _e( 'Conversion Rate' ); ?>"><?php _e( 'CVR: '); ?><strong><?php echo round( ( $num_events_completed / ( ( $num_impressions > 0 )? $num_impressions : 1 ) * 100 ), 2 ); ?></strong>%</span>
        </p>
        <?php
    }

    /**
     * Save the step settings
     *
     * @param $step WPGH_Step
     */
    public function save( $step )
    {
        if ( isset( $_POST[ $step->prefix( 'success_page' ) ] ) ){

            $step->update_meta( 'success_page', esc_url_raw( $_POST[  $step->prefix( 'success_page' ) ] ) );

        }

        if ( isset( $_POST[ $step->prefix( 'form' ) ] ) ){

            $step->update_meta( 'form', wp_kses_post( $_POST[  $step->prefix( 'form' ) ] ) );

        }
    }

    /**
     * Whenever a form is filled complete the benchmark.
     *
     * @param $step_id
     * @param $contact WPGH_Contact
     * @param $submission int
     *
     * @return bool
     */
    public function complete( $step_id, $contact, $submission )
    {

	    $step = wpgh_get_funnel_step( $step_id );

	    /* Double check that the wpgh_form_submit action isn't being fired by another benchmark */
	    if ( $step->type !== $this->type )
	        return false;

	    $success = false;

	    if ( $step->can_complete( $contact ) ){

		    $success = $step->enqueue( $contact );
            /* Process the queue immediately */
//            do_action( 'wpgh_process_queue' );
	    }

	    /*var_dump( $success );
	    wp_die( 'made-it-here' );*/

	    return $success;

    }

    /**
     * Process the tag applied step...
     *
     * @param $contact WPGH_Contact
     * @param $event WPGH_Event
     *
     * @return true
     */
    public function run( $contact, $event )
    {
        //do nothing...

        return true;
    }

    /**
     * Track a form impression from the frontend.
     * @deprecated since 1.2.4
     */
    public function track_impression()
    {

        if( !class_exists( 'Browser' ) )
            require_once WPGH_PLUGIN_DIR . 'includes/lib/browser.php';

        $browser = new Browser();
        if ( $browser->isRobot() || $browser->isAol() ){
            wp_die( json_encode( array( 'error' => 'No Track Robots.' ) ) );
        }

        $ID = intval( $_POST[ 'id' ] );

        if ( ! WPGH()->steps->exists( $ID ) ){
            wp_die( json_encode( array( 'error' => 'Form DNE.' ) ) );
        }

        $step = wpgh_get_funnel_step( $ID );

        $response = array();

        /*
         * Is Contact
         */
        if ( $contact = WPGH()->tracking->get_contact() ) {

            $db = WPGH()->activity;

            /* Check if impression for contact exists... */
            $args = array(
                'funnel_id'     => $step->funnel_id,
                'step_id'       => $step->ID,
                'contact_id'    => $contact->ID,
                'activity_type' => 'form_impression',
            );

            $response[ 'cid' ] = $contact->ID;

        } else {
            /*
            * Not a Contact
            */

            /* validate against viewers IP? Cookie? TBD */
            $db = WPGH()->activity;

            /* Check if impression for contact exists... */
            if ( isset( $_COOKIE[ 'gh_ref_id' ] ) ){
                $ref_id = sanitize_key( $_COOKIE[ 'gh_ref_id' ] );
            } else {
                $ref_id = uniqid( 'g' );
            }

            $args = array(
                'funnel_id'     => $step->funnel_id,
                'step_id'       => $step->ID,
                'activity_type' => 'form_impression',
                'ref'           => $ref_id
            );

            $response[ 'ref_id' ] = $ref_id;

        }

        if ( ! $db->activity_exists( $args ) ){

            $args[ 'timestamp' ] = time();
            $db->add( $args );

            $response[ 'result' ] = 'success';

        }

        wp_die( json_encode( $response ) );

    }
}