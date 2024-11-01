<?php

/**
 * Creates and returns a WB_Form_Element object.
 *
 * @see wordbench_the_form_element()
 * @uses wordbench_labelize()
 * @param array $args Associative array of properties for the form element.
 * @return mixed Returns the created object on success, FALSE on failure.
 */
function wordbench_get_form_element( $args = array() ) {
	$defaults = array(
		'type' => 'text'
	);

	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	require_once WORDBENCH_INC . 'class-form-element.php';
	require_once WORDBENCH_INC . 'class-enum-element.php';

	$file  = WORDBENCH_INC . 'class-' . $type . '-element.php';
	$class = 'WB_' . str_replace( ' ', '_', wordbench_labelize( $type ) ) . '_Element';

	if ( is_file( $file ) ) {
		require_once $file;
	}

	if ( class_exists( $class ) ) {
		return new $class( $args );
	}

	return false;
}

/**
 * Renders a form element.
 *
 * @uses wordbench_get_form_element()
 * @uses WB_Form_Element::element()
 * @param array $args Associative array of properties for the form element.
 * @param array $instance Instance data for the specific field.
 */
function wordbench_the_form_element( $args = array(), $instance = array() ) {
	if ( $form_element = wordbench_get_form_element( $args ) ) {
		$form_element->element( $instance );
	}
}
