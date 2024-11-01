<?php

/**
 * This is the base class for all other form element classes. HTML generation
 * and data validation are handled by this class and its child classes.
 */
class WB_Form_Element {
	protected $_title;
	protected $_name;

	/**
	 * Constructor
	 *
	 * @param array $args Array of properties for this form element.
	 *     title - (string) label text for the element
	 *     name  - (string) base name for the element
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'title' => null,
			'name'  => null
		);

		$args = wp_parse_args( $args, $defaults );

		$this->_title = $args['title'];
		$this->_name  = $args['name'];
	}

	/**
	 * Returns the validated input for this element. Child classes SHOULD extend
	 * this method.
	 *
	 * @param mixed $value The user-submitted form data to be validated.
	 * @return mixed Returns the validated user data.
	 */
	public function validate( $value ) {
		return $value;
	}

	/**
	 * Renders the form element. Child classes MUST extend this method.
	 *
	 * @param array $instance Instance data for this specific field.
	 */
	public function element( $instance = array() ) {
		trigger_error( __CLASS__ . ' must override WB_Form_Element::element()', E_USER_ERROR );
	}

	/**
	 * Returns a properly formed element ID with the given prefix.
	 *
	 * @param string $prefix The prefix to be prepended to the element ID.
	 * @return string Returns the properly formed element ID.
	 */
	protected function _get_id( $prefix = '' ) {
		$name   = wordbench_sanitize( $this->_name );
		$prefix = wordbench_sanitize( $prefix );

		return empty( $prefix ) ? $name : $prefix . '-' . $name;
	}

	/**
	 * Returns a properly formed element name with the given prefix.
	 *
	 * @param string $prefix The prefix to be prepended to the element name.
	 * @return string Returns the properly formed element name.
	 */
	protected function _get_name( $prefix = '' ) {
		$name   = wordbench_sanitize( $this->_name );
		$prefix = wordbench_sanitize( $prefix );

		return empty( $prefix ) ? $name : $prefix . '[' . $name . ']';
	}

	/**
	 * Generates and returns an HTML tag.
	 *
	 * @param string $name The type of HTML tag to create.
	 * @param string $body The inner text or HTML, if any, for this tag.
	 * @param array $attr The attributes for this tag, in key-value pairs.
	 * @param bool $close Flag to indicate this tag requires a closing tag.
	 * @return string Returns the generated HTML tag.
	 */
	protected function _html_tag( $name, $body = null, $attr = array(), $close = false ) {
		foreach ( (array) $attr as $key => $value ) {
			if ( true === $value ) {
				$value = $name;
			} elseif ( false === $value ) {
				continue;
			}

			$attr_str = sprintf( '%s %s="%s"', $attr_str,
				$key, esc_attr( $value ) );
		}

		$html = sprintf( '<%s%s>', $name, $attr_str );

		if ( ! empty( $body ) || $close ) {
			$html .= sprintf( '%s</%s>', $body, $name );
		}

		return $html;
	}

	/**
	 * Generates and returns an HTML label tag to match an input tag.
	 *
	 * @param string $title The visible text for this label.
	 * @param string $for The ID attribute for the matching input tag.
	 * @param array $attr Other attributes for this label.
	 * @return string Returns the generated HTML label tag.
	 */
	protected function _html_label( $title, $for = '', $attr = array() ) {
		$attr['for'] = $for;

		return $this->_html_tag( 'label', $title, $attr );
	}

	/**
	 * Generates and returns an HTML input tag.
	 *
	 * @param string $type The value of the type attribute of this input.
	 * @param string $name The value of the name attribute of this input.
	 * @param string $value The value of the value attribute of this input.
	 * @param array $attr Other attributes for this input.
	 * @return string Returns the generated HTML input tag.
	 */
	protected function _html_input ( $type, $name, $value, $attr = array() ) {
		$attr['type']  = $type;
		$attr['name']  = $name;
		$attr['value'] = $value;

		return $this->_html_tag( 'input', null, $attr );
	}
}
