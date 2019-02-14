<?php
/**
 * Replacements
 *
 * The inspiration for this class came from EDD_Email_Tags by easy digital downloads.
 * But ours is better because it allows for dynamic arguments passed with the replacements code.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Replacements
{

    /**
     * Array of replacement codes and their callback functions
     *
     * @var array
     */
    var $replacements = array();

    /**
     * The contact ID
     *
     * @var int
     */
    var $contact_id;


    public function __construct()
    {

        $this->setup_defaults();

        if ( isset( $_GET['page'] ) && strpos( $_GET[ 'page' ],'gh_' ) !== false ){

            add_action( 'admin_footer' , array( $this, 'replacements_in_footer' )  );

        }

    }

    /**
     * Setup the default replacement codes
     */
    private function setup_defaults()
    {

        $replacements = array(
            array(
                'code'        => 'first',
                'callback'    => 'wpgh_replacement_first_name',
                'description' => _x( 'The contact\'s first name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'first_name',
                'callback'    => 'wpgh_replacement_first_name',
                'description' => _x( 'The contact\'s first name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'last',
                'callback'    => 'wpgh_replacement_last_name',
                'description' => _x( 'The contact\'s last name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'last_name',
                'callback'    => 'wpgh_replacement_last_name',
                'description' => _x( 'The contact\'s last name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'   => 'username',
                'callback'    => 'wpgh_replacement_username',
                'description' => _x( 'The contact\'s last name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'email',
                'callback'    => 'wpgh_replacement_email',
                'description' => _x( 'The contact\'s email address.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'phone',
                'callback'    => 'wpgh_replacement_phone',
                'description' => _x( 'The contact\'s phone number.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'phone_ext',
                'callback'    => 'wpgh_replacement_phone_ext',
                'description' => _x( 'The contact\'s phone number extension.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'address',
                'callback'    => 'wpgh_replacement_address',
                'description' => _x( 'The contact\'s full address.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'company_name',
                'callback'    => 'wpgh_replacement_company_name',
                'description' => _x( 'The contact\'s company name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'job_title',
                'callback'    => 'wpgh_replacement_job_title',
                'description' => _x( 'The contact\'s job title.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'company_address',
                'callback'    => 'wpgh_replacement_company_address',
                'description' => _x( 'The contact\'s company address.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'meta',
                'callback'    => 'wpgh_replacement_meta',
                'description' => _x( 'Any meta data related to the contact. Usage: {meta.attribute}', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'business_name',
                'callback'    => 'wpgh_replacement_business_name',
                'description' => _x( 'The business name as defined in the settings.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'business_phone',
                'callback'    => 'wpgh_replacement_business_phone',
                'description' => _x( 'The business phone number as defined in the settings.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'business_address',
                'callback'    => 'wpgh_replacement_business_address',
                'description' => _x( 'The business address as defined in the settings.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'owner_first_name',
                'callback'    => 'wpgh_replacement_owner_first_name',
                'description' => _x( 'The contact owner\'s name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'owner_last_name',
                'callback'    => 'wpgh_replacement_owner_last_name',
                'description' => _x( 'The contact owner\'s name.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'owner_email',
                'callback'    => 'wpgh_replacement_owner_email',
                'description' => _x( 'The contact owner\'s email address.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'owner_phone',
                'callback'    => 'wpgh_replacement_owner_phone',
                'description' => _x( 'The contact owner\'s phone number.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'confirmation_link',
                'callback'    => 'wpgh_replacement_confirmation_link',
                'description' => _x( 'A link to confirm the email address of a contact.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'confirmation_link_raw',
                'callback'    => 'wpgh_replacement_confirmation_link_raw',
                'description' => _x( 'A link to confirm the email address of a contact which can be placed in a button or link.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'superlink',
                'callback'    => 'wpgh_replacement_superlink',
                'description' => _x( 'A superlink code. Usage: {superlink.id}', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'date',
                'callback'    => 'wpgh_replacement_date',
                'description' => _x( 'Insert a dynamic date. Usage {date.format|time}. Example: {date.Y-m-d|+2 days}', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'files',
                'callback'    => 'wpgh_replacement_files',
                'description' => _x( 'Insert a download link for a file. Usage {files.key}. Example: {files.custom_files}. Do find the key for a file see the contact record and copy the relevant replacement code.', 'replacement', 'groundhogg' ),
            ),
            array(
                'code'        => 'groundhogg_day_quote',
                'callback'    => 'wpgh_get_random_groundhogday_quote',
                'description' => _x( 'Inserts a random quote from the movie Groundhogg Day featuring Bill Murray', 'replacement', 'groundhogg' ),
            )
        );

        $replacements = apply_filters( 'wpgh_replacement_defaults', $replacements );

        foreach ( $replacements as $replacement )
        {
            $this->add( $replacement['code'], $replacement[ 'callback' ], $replacement[ 'description' ] );
        }

    }

    /**
     * Add a replacement code
     *
     * @param $code string the code
     * @param $callback string|array the callback function
     * @param string $description string description of the code
     *
     * @return bool
     */
    function add( $code, $callback, $description='' )
    {
        if ( ! $code || ! $callback )
            return false;

        if ( is_callable( $callback ) )
        {
            $this->replacements[ $code ] = array(
                'code' => $code,
                'callback' => $callback,
                'description' => $description
            );

            return true;
        }

        return false;

    }

    /**
     * Remove a replacement code
     *
     * @since 1.9
     *
     * @param string $code to remove
     */
    public function remove( $code )
    {
        unset( $this->replacements[$code] );
    }

    /**
     * See if the replacement code exists already
     *
     * @param $code
     *
     * @return bool
     */
    function has_replacement( $code )
    {
        return array_key_exists( $code, $this->replacements );
    }

    /**
     * Returns a list of all replacement codes
     *
     * @since 1.9
     *
     * @return array
     */
    public function get_replacements()
    {
        return $this->replacements;
    }

    /**
     * Process the codes based on the given contact ID
     *
     * @param $contact_id int ID of the contact
     * @param $content
     *
     * @return string
     */
    public function process( $content, $contact_id=null )
    {

        if ( empty( $contact_id ) )
            $contact_id = WPGH()->tracking->get_contact()->ID;

        if ( ! $contact_id || ! is_int( $contact_id ) )
            return $content;

        // Check if there is at least one tag added
        if ( empty( $this->replacements ) || ! is_array( $this->replacements ) ) {
            return $content;
        }

        $this->contact_id = $contact_id;
        $new_content = preg_replace_callback( "/{([^{}]+)}/s", array( $this, 'do_replacement' ), $content );
        $this->contact_id = null;

        return $new_content;

    }

    /**
     * Process the given replacement code
     *
     * @param $m
     *
     * @return mixed
     */
    private function do_replacement( $m )
    {
        // Get tag
        $code = $m[1];

        /* make sure that if it's a dynamic code to remove anything after the period */
        if ( strpos( $code, '.' ) > 0 ) {
            $parts = explode( '.', $code );
            $code = $parts[0];
        }

        // Return tag if tag not set
        if ( ! $this->has_replacement( $code ) && substr( $code, 0, 1 ) !== '_' ) {
            return $m[0];
        }

        /* reset code */
        $code = $m[1];

        if ( substr( $code, 0, 1) === '_' ) {

            $text = WPGH()->contact_meta->get_meta( $this->contact_id, substr( $code, 1 ), true );

        } else if ( strpos( $code, '.' ) > 0 ) {

            $parts = explode( '.', $code );
            $code = $parts[0];

            if ( ! isset( $parts[1] ) ) {
                $arg = false;
            } else {
                $arg = $parts[1];
            }

            $text = call_user_func( $this->replacements[ $code ]['callback'], $arg, $this->contact_id, $code );

        } else {

            $text = call_user_func( $this->replacements[ $code ]['callback'], $this->contact_id, $code );

        }

        return apply_filters( 'wpgh_filter_replacement_' . $code, $text );

    }

    public function get_table()
    {
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th><?php _e( 'Replacement Code' ); ?></th>
                <th><?php _e( 'Description' ); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ( WPGH()->replacements->get_replacements() as $replacement ): ?>
                <tr>
                    <td>
                        <input style="border: none;outline: none;background: transparent;width: 100%;" onfocus="this.select();" value="{<?php echo $replacement[ 'code' ]; ?>}" readonly>
                    </td>
                    <td>
                        <span><?php echo $replacement[ 'description' ]; ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    public function replacements_in_footer()
    {
        ?>
        <div id="footer-replacement-codes" class="hidden">
            <?php $this->get_table(); ?>
        </div>
        <?php
    }

    public function show_replacements_button()
    {

        echo WPGH()->html->modal_link( array(
            'title'     => 'Replacements',
            'text'      => _x( 'Insert Replacement', 'replacement', 'groundhogg' ),
            'footer_button_text' => __( 'Close' ),
            'id'        => '',
            'class'     => 'button button-secondary no-padding',
            'source'    => 'footer-replacement-codes',
            'height'    => 900,
            'width'     => 700,
//            'footer'    => 'false',
        ) );

    }


}

/**
 * Return the contact meta
 *
 * @param $contact_id int
 * @param $arg string the meta key
 * @return mixed|string
 */
function wpgh_replacement_meta( $arg, $contact_id )
{
    if ( empty( $arg ) )
        return '';

    return print_r( WPGH()->contact_meta->get_meta( $contact_id, $arg, true ) , true );
}

/**
 * Return back the first name ot the contact.
 *
 * @param $contact_id int the contact_id
 * @return string the first name
 */
function wpgh_replacement_first_name( $contact_id )
{
    return WPGH()->contacts->get_column_by( 'first_name', 'ID', $contact_id );
}

/**
 * Return back the last name ot the contact.
 *
 * @param $contact_id int the contact_id
 * @return string the last name
 */
function wpgh_replacement_last_name( $contact_id )
{
    return WPGH()->contacts->get_column_by( 'last_name', 'ID', $contact_id );
}

/**
 * Return the username of the contact if one exists.
 *
 * @param $contact_id int the contact's id
 * @return string
 */
function wpgh_replacement_username( $contact_id )
{
    $uid = WPGH()->contacts->get_column_by( 'user_id', 'ID', $contact_id );
    if ( $uid ){
        $user = get_userdata( $uid );
        return $user->user_login;
    }

    return '';
}

/**
 * Return back the email of the contact.
 *
 * @param $contact_id int the contact ID
 * @return string the email
 */
function wpgh_replacement_email( $contact_id )
{
    return WPGH()->contacts->get_column_by( 'email', 'ID', $contact_id );
}

/**
 * Return back the phone # ot the contact.
 *
 * @param $contact_id int the contact_id
 * @return string the first name
 */
function wpgh_replacement_phone( $contact_id )
{
    return WPGH()->contact_meta->get_meta( $contact_id, 'primary_phone', true );
}

/**
 * Return back the phone # ext the contact.
 *
 * @param $contact_id int the contact_id
 * @return string the first name
 */
function wpgh_replacement_phone_ext( $contact_id )
{
    return WPGH()->contact_meta->get_meta( $contact_id, 'primary_phone_extension', true );
}

/**
 * Return back the address of the contact.
 *
 * @param $contact_id int the contact_id
 * @return string the first name
 */
function wpgh_replacement_address( $contact_id )
{

    $contact = new WPGH_Contact( $contact_id );

    $address = array();

    if ( $contact->get_meta( 'gh_street_address_1' ) )
        $address[] = $contact->get_meta( 'gh_street_address_1' );
    if ( $contact->get_meta( 'gh_street_address_2' ) )
        $address[] = ' ' . $contact->get_meta( 'gh_street_address_2' );
    if ( $contact->get_meta( 'city' ) )
        $address[] = $contact->get_meta( 'city' );
    if ( $contact->get_meta( 'region' ) )
        $address[] = $contact->get_meta( 'region' );

    if ( $contact->get_meta( 'country' ) ){
        $countries  = wpgh_get_countries_list();
        $address[] = $countries[ $contact->get_meta( 'country' ) ];
    }

    if ( $contact->get_meta( 'zip_postal' ) )
        $address[] = strtoupper( $contact->get_meta( 'zip_postal' ) );

    $address = implode( ', ', $address );

    return $address;

}

/**
 * Get the company name of a contact
 *
 * @param $contact_id
 * @return mixed
 */
function wpgh_replacement_company_name( $contact_id )
{
    return WPGH()->contact_meta->get_meta( $contact_id, 'company_name', true );
}

/**
 * Get the company address of a contact
 *
 * @param $contact_id
 * @return mixed
 */
function wpgh_replacement_company_address( $contact_id )
{
    return WPGH()->contact_meta->get_meta( $contact_id, 'address', true );
}

/**
 * Get the job title of a contact
 *
 * @param $contact_id
 * @return mixed
 */
function wpgh_replacement_job_title( $contact_id )
{
    return WPGH()->contact_meta->get_meta( $contact_id, 'job_title', true );
}

/**
 * Return the contact's owner
 *
 * @param $contact_id int the contact ID
 *
 * @return false|string|WP_User
 */
function wpgh_get_contact_owner( $contact_id )
{
    $owner = (int) WPGH()->contacts->get_column_by( 'owner_id', 'ID', $contact_id );

    if ( ! $owner )
        return get_bloginfo( 'admin_email' );

    return get_userdata( $owner );
}

/**
 * Return back the email address of the contact owner.
 *
 * @param $contact_id int the contact ID
 * @return string the owner's email
 */
function wpgh_replacement_owner_email( $contact_id )
{
    $user = wpgh_get_contact_owner( $contact_id );

    if ( ! $user )
        return get_bloginfo( 'admin_email' );

    return $user->user_email;
}

/**
 * Return back the first name of the contact owner.
 *
 * @param $contact_id int the contact
 * @return string the owner's name
 */
function wpgh_replacement_owner_first_name( $contact_id )
{
    $user = wpgh_get_contact_owner( $contact_id );

    if ( ! $user )
        return get_bloginfo( 'admin_email' );

    return $user->first_name;
}

/**
 * Return back the first name of the contact owner.
 *
 * @param $contact_id int the contact
 * @return string the owner's name
 */
function wpgh_replacement_owner_last_name( $contact_id )
{
    $user = wpgh_get_contact_owner( $contact_id );

    if ( ! $user )
        return get_bloginfo( 'admin_email' );

    return $user->last_name;
}

/**
 * Return a confirmation link for the contact
 * This just gets the Optin Page link for now.
 *
 * @return string the optin link
 */
function wpgh_replacement_confirmation_link()
{
    $link_text = wpgh_get_option( 'gh_confirmation_text', __( 'Confirm your email.', 'groundhogg' ) );
    $link_url = site_url( 'gh-confirmation/via/email/' );

    return sprintf( "<a href=\"%s\" target=\"_blank\">%s</a>", $link_url, $link_text );
}

/**
 * Return a raw confirmation link for the contact that can be placed in a button.
 * This just gets the Optin Page link for now.
 *
 * @return string the optin link
 */
function wpgh_replacement_confirmation_link_raw()
{
    $link_url = site_url( 'gh-confirmation/via/email/' );
    return $link_url;
}

/**
 * Do the link replacement...
 *
 * @param $linkId int the ID of the link
 *
 * @return string the superlink url
 */
function wpgh_replacement_superlink( $linkId )
{
    $linkId = absint( intval( $linkId ) );
    return site_url( 'superlinks/link/' . $linkId );
}

/**
 * Return a formatted date in local time.
 *
 * @param $time_string
 *
 * @return string
 */
function wpgh_replacement_date( $time_string )
{

    $parts =preg_split( "/(\||;)/", $time_string );

    if ( count( $parts ) === 1 ){
        $format = 'l jS \of F Y';
        $when = $parts[0];
    } else {
        $format = $parts[0];
        $when = $parts[1];
    }

    /* convert to local time */
    $time = strtotime( $when ) + ( wpgh_get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

    return date_i18n( $format, $time );

}

/**
 * Return the business name
 *
 * @return string
 */
function wpgh_replacement_business_name()
{
    return wpgh_get_option( 'gh_business_name' );
}

/**
 * Return eh business phone #
 *
 * @return string
 */
function wpgh_replacement_business_phone()
{
    return wpgh_get_option( 'gh_phone' );
}

/**
 * Return the business address
 *
 * @return array|string
 */
function wpgh_replacement_business_address()
{
    $address = array();

    if ( wpgh_get_option( 'gh_street_address_1' ) )
        $address[] = wpgh_get_option( 'gh_street_address_1' ) . ' ' . wpgh_get_option( 'gh_street_address_2' );
    if ( wpgh_get_option( 'gh_city' ) )
        $address[] = wpgh_get_option( 'gh_city' );
    if ( wpgh_get_option( 'gh_region' ) )
        $address[] = wpgh_get_option( 'gh_region' );
    if ( wpgh_get_option( 'gh_country' ) )
        $address[] = wpgh_get_option( 'gh_country' );
    if ( wpgh_get_option( 'gh_zip_or_postal' ) )
        $address[] = strtoupper( wpgh_get_option( 'gh_zip_or_postal' ) );

    $address = implode( ', ', $address );

    return $address;
}

/**
 * Get a file download link from a contact record.
 *
 * @param $key string|int the key for the file
 * @param $contact_id int
 *
 * @return string
 */
function wpgh_replacement_files( $key = '', $contact_id = null )
{

    /**
     * IF the key was not passed than the key will be gthe contact ID so we check if the contact_id is the code or null.
     */
    if ( ! $contact_id || WPGH()->replacements->has_replacement( $contact_id ) ){
        $contact_id = intval( $key );
        $key = false;
    } else {

        $key = is_numeric( $key ) ? intval( $key ) : sanitize_key( $key );

    }

    $contact = wpgh_get_contact( $contact_id );

    $files = $contact->get_meta( 'files' );

    if ( ! $files || ! is_array( $files ) ){
        return __( 'No files found.', 'groundhogg' );
    }

    /*Return all files*/
    if ( ! $key ){

        $html = '';

        foreach ( $files as $i => $file ){

            $info = pathinfo( $file[ 'file' ] );

            if ( ! file_exists( $file[ 'file' ] ) ){
                continue;
            }

            $html .= sprintf( '<li><a href="%s">%s</a></li>', $file[ 'url' ] , esc_html( $info[ 'basename' ] ) );
        }

        return sprintf( '<ul>%s</ul>', $html );

        /* Return 1 file */
    } else if ( isset( $files[ $key ] ) ) {
        $file = $files[ $key ];
        $info = pathinfo( $file[ 'file' ] );

        if ( ! file_exists( $file[ 'file' ] ) ){
            return __( 'No files found.', 'groundhogg' );
        }

        return sprintf( '<a href="%s">%s</a>', $file[ 'url' ] , esc_html( $info[ 'basename' ] ) );
    }

    return __( 'No files found.', 'groundhogg' );

}

/**
 * Return a random quote from the movie groundhog day staring bill murray.
 * Also the movie of which branding is based upon.
 *
 * @return mixed
 */
function wpgh_get_random_groundhogday_quote()
{
    $quotes = array();

    $quotes[] = "I'm not going to live by their rules anymore.";
    $quotes[] = "When Chekhov saw the long winter, he saw a winter bleak and dark and bereft of hope. Yet we know that winter is just another step in the cycle of life. But standing here among the people of Punxsutawney and basking in the warmth of their hearths and hearts, I couldn't imagine a better fate than a long and lustrous winter.";
    $quotes[] = "Hi, three cheeseburgers, two large fries, two milkshakes, and one large coke.";;
    $quotes[] = "It's the same thing every day, Clean up your room, stand up straight, pick up your feet, take it like a man, be nice to your sister, don't mix beer and wine ever, Oh yeah, don't drive on the railroad tracks.";
    $quotes[] = "I'm a god, I'm not the God. I don't think.";
    $quotes[] = "Don't drive angry! Don't drive angry!";
    $quotes[] = "I'm betting he's going to swerve first.";
    $quotes[] = "You want a prediction about the weather? You're asking the wrong Phil. I'm going to give you a prediction about this winter? It's going to be cold, it's going to be dark and it's going to last you for the rest of your lives!";
    $quotes[] = "We mustn't keep our audience waiting.";
    $quotes[] = "Okay campers, rise and shine, and don't forget your booties cause its cold out there...its cold out there every day.";
    $quotes[] = "I peg you as a glass half empty kinda guy.";
    $quotes[] = "Why would anybody steal a groundhog? I can probably think of a couple of reasons... pervert.";
    $quotes[] = "Well, what if there is no tomorrow? There wasn't one today.";
    $quotes[] = "Did he actually refer to himself as \"the talent\"?";
    $quotes[] = "Did you sleep well Mr. Connors?";

    $quotes = apply_filters( 'add_movie_quotes', $quotes );

    $quote = rand( 0, count( $quotes ) - 1 );

    return $quotes[ $quote ];
}