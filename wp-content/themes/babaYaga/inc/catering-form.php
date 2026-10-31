<?php
/**
 * Catering enquiry form.
 *
 * Built in the theme rather than as an Elementor Pro form so it travels with
 * a theme-only deploy, which is how this site ships. Delivery is left to
 * wp_mail(), which FluentSMTP takes over on live, so nothing here needs to
 * know about transport.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Where enquiries go.
 *
 * Defaults to the catering address already published on the catering page
 * under Marco's name. Change the string here, or filter it, once the client
 * confirms the address they want.
 *
 * @return string
 */
function babayaga_child_catering_email() {
	return (string) apply_filters( 'babayaga_catering_email', 'bbq101catering@yahoo.com' );
}

/**
 * The fields, in the order the client listed them.
 *
 * Every field is posted under a t101_ prefix. WordPress reads its public
 * query vars out of $_POST as well as $_GET, so a field literally called
 * "name", "date" or "type" is taken as a post-slug / date / post-type query,
 * which breaks the main query and makes the page answer 404 on submit.
 *
 * @return array
 */
function babayaga_child_catering_fields() {
	return array(
		'name'    => array(
			'label'        => 'Full name',
			'type'         => 'text',
			'required'     => true,
			'autocomplete' => 'name',
		),
		'email'   => array(
			'label'        => 'Email address',
			'type'         => 'email',
			'required'     => true,
			'autocomplete' => 'email',
		),
		'phone'   => array(
			'label'        => 'Phone number',
			'type'         => 'tel',
			'required'     => false,
			'autocomplete' => 'tel',
		),
		'date'    => array(
			'label'    => 'Event date',
			'type'     => 'date',
			'required' => true,
		),
		'type'    => array(
			'label'    => 'Event type',
			'type'     => 'select',
			'required' => false,
			'options'  => array( 'Office lunch', 'Game day party', 'Birthday', 'Private event', 'Holiday party', 'Wedding', 'Something else' ),
		),
		'guests'  => array(
			'label'    => 'Estimated guests',
			'type'     => 'number',
			'required' => true,
		),
		'details' => array(
			'label'    => 'Additional details or special requests',
			'type'     => 'textarea',
			'required' => false,
		),
	);
}

/**
 * Validation errors for the current request.
 *
 * @param array|null $set Errors to store.
 * @return array
 */
function babayaga_child_catering_errors( $set = null ) {
	static $errors = array();

	if ( is_array( $set ) ) {
		$errors = $set;
	}

	return $errors;
}

/**
 * Submitted values, so a failed submit does not empty the form.
 *
 * @param array|null $set Values to store.
 * @return array
 */
function babayaga_child_catering_values( $set = null ) {
	static $values = array();

	if ( is_array( $set ) ) {
		$values = $set;
	}

	return $values;
}

/**
 * Handle the POST before anything is rendered.
 *
 * @return void
 */
function babayaga_child_catering_submit() {
	if ( empty( $_POST['babayaga_catering'] ) ) {
		return;
	}

	if ( ! isset( $_POST['babayaga_catering_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['babayaga_catering_nonce'] ) ), 'babayaga_catering' ) ) {
		babayaga_child_catering_errors( array( '_form' => 'That form had expired. Please try again.' ) );

		return;
	}

	// Honeypot: a real person never fills this in.
	if ( ! empty( $_POST['babayaga_website'] ) ) {
		return;
	}

	$values = array();
	$errors = array();

	foreach ( babayaga_child_catering_fields() as $key => $field ) {
		$post_key = 't101_' . $key;
		$raw      = isset( $_POST[ $post_key ] ) ? wp_unslash( $_POST[ $post_key ] ) : '';
		$val = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );

		if ( 'email' === $field['type'] ) {
			$val = sanitize_email( $val );
		}

		$values[ $key ] = $val;

		if ( ! empty( $field['required'] ) && '' === $val ) {
			$errors[ $key ] = sprintf( 'Please add your %s.', strtolower( $field['label'] ) );
		}
	}

	if ( '' !== $values['email'] && ! is_email( $values['email'] ) ) {
		$errors['email'] = 'That email address does not look right.';
	}

	if ( '' !== $values['guests'] && (int) $values['guests'] < 1 ) {
		$errors['guests'] = 'Please give a guest count of at least one.';
	}

	babayaga_child_catering_values( $values );

	if ( $errors ) {
		babayaga_child_catering_errors( $errors );

		return;
	}

	$lines = array( 'New catering enquiry from the 101 Grill website.', '' );

	foreach ( babayaga_child_catering_fields() as $key => $field ) {
		$lines[] = $field['label'] . ': ' . ( '' !== $values[ $key ] ? $values[ $key ] : '-' );
	}

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( is_email( $values['email'] ) ) {
		$headers[] = 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>';
	}

	$sent = wp_mail(
		babayaga_child_catering_email(),
		'Catering enquiry - ' . $values['name'],
		implode( "\n", $lines ),
		$headers
	);

	if ( ! $sent ) {
		babayaga_child_catering_errors( array( '_form' => 'Sorry, that could not be sent just now. Please call us on (818) 991-7227.' ) );

		return;
	}

	// Post/redirect/get, so a refresh cannot send the same enquiry twice.
	wp_safe_redirect( add_query_arg( 'catering', 'sent', get_permalink() ) . '#catering-enquiry' );
	exit;
}
add_action( 'template_redirect', 'babayaga_child_catering_submit', 0 );

/**
 * Render one field.
 *
 * @param string $key   Field key.
 * @param array  $field Field definition.
 * @return string
 */
function babayaga_child_catering_field( $key, $field ) {
	$errors  = babayaga_child_catering_errors();
	$values  = babayaga_child_catering_values();
	$id      = 'cat-' . $key;
	$val     = isset( $values[ $key ] ) ? $values[ $key ] : '';
	$err     = isset( $errors[ $key ] ) ? $errors[ $key ] : '';
	$req     = ! empty( $field['required'] );
	$descby  = $err ? ' aria-describedby="' . esc_attr( $id ) . '-err"' : '';
	$invalid = $err ? ' aria-invalid="true"' : '';
	$auto    = ! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : '';
	$wide    = 'details' === $field['type'] || 'textarea' === $field['type'];

	$out  = '<p class="t101-field' . ( $wide ? ' t101-field--wide' : '' ) . '">';
	$out .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] );
	$out .= $req ? ' <span class="t101-req">required</span>' : ' <span class="t101-opt">optional</span>';
	$out .= '</label>';

	if ( 'textarea' === $field['type'] ) {
		$out .= '<textarea id="' . esc_attr( $id ) . '" name="t101_' . esc_attr( $key ) . '" rows="4"' . $descby . $invalid . '>' . esc_textarea( $val ) . '</textarea>';
	} elseif ( 'select' === $field['type'] ) {
		$out .= '<select id="' . esc_attr( $id ) . '" name="t101_' . esc_attr( $key ) . '"' . $descby . $invalid . '>';
		$out .= '<option value="">Please choose</option>';

		foreach ( $field['options'] as $opt ) {
			$out .= '<option value="' . esc_attr( $opt ) . '"' . selected( $val, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
		}

		$out .= '</select>';
	} else {
		$extra = 'number' === $field['type'] ? ' min="1" inputmode="numeric"' : '';
		$out  .= '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $id ) . '" name="t101_' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '"' . ( $req ? ' required' : '' ) . $auto . $extra . $descby . $invalid . '>';
	}

	if ( $err ) {
		$out .= '<span class="t101-field-err" id="' . esc_attr( $id ) . '-err">' . esc_html( $err ) . '</span>';
	}

	return $out . '</p>';
}

/**
 * The whole enquiry section.
 *
 * @return string
 */
function babayaga_child_catering_form() {
	$errors = babayaga_child_catering_errors();
	$sent   = isset( $_GET['catering'] ) && 'sent' === $_GET['catering']; // phpcs:ignore WordPress.Security.NonceVerification

	$out  = '<section class="t101-enquiry" id="catering-enquiry">';
	$out .= '<div class="t101-enquiry-inner">';
	$out .= '<p class="t101-enquiry-label">Catering</p>';
	$out .= '<h2 class="t101-enquiry-title">Tell us about your event</h2>';
	$out .= '<p class="t101-enquiry-intro">Send over the date, the headcount and what you are thinking. We will come back to you with a quote.</p>';

	if ( $sent ) {
		$out .= '<p class="t101-enquiry-ok" role="status">Thank you. Your enquiry is on its way and we will be in touch shortly.</p>';
	}

	if ( ! empty( $errors['_form'] ) ) {
		$out .= '<p class="t101-enquiry-err" role="alert">' . esc_html( $errors['_form'] ) . '</p>';
	} elseif ( $errors ) {
		$out .= '<p class="t101-enquiry-err" role="alert">Please check the highlighted fields below.</p>';
	}

	$out .= '<form class="t101-enquiry-form" method="post" action="#catering-enquiry" novalidate>';
	$out .= wp_nonce_field( 'babayaga_catering', 'babayaga_catering_nonce', true, false );
	$out .= '<input type="hidden" name="babayaga_catering" value="1">';
	$out .= '<p class="t101-hp"><label>Leave this field empty<input type="text" name="babayaga_website" tabindex="-1" autocomplete="off"></label></p>';

	foreach ( babayaga_child_catering_fields() as $key => $field ) {
		$out .= babayaga_child_catering_field( $key, $field );
	}

	$out .= '<p class="t101-field t101-field--wide t101-enquiry-actions">';
	$out .= '<button type="submit" class="t101-cta t101-cta--solid">Send enquiry</button>';
	$out .= '<span class="t101-enquiry-note">Or call us on <a href="tel:+18189917227">(818) 991-7227</a>.</span>';
	$out .= '</p>';

	return $out . '</form></div></section>';
}
