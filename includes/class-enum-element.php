<?php

class WB_Enum_Element extends WB_Form_Element {
	protected $_opts = array();
	protected $_default = null;

	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$defaults = array(
			'opts'    => array(),
			'default' => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$this->_opts = $args['opts'];
	}

	public function validate( $value ) {
		if ( ! in_array( $value, $this->_opts ) )
			$value = $this->_default;

		return $value;
	}
}
