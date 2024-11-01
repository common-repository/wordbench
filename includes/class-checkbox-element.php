<?php

class WB_Checkbox_Element extends WB_Enum_Element {
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->_opts    = array( 'on', 'off' );
		$this->_default = 'off';
	}

	public function element( $instance = array() ) {
		$defaults = array(
			'prefix' => '',
			'value'  => ''
		);

		extract( wp_parse_args( $instance, $defaults ), EXTR_SKIP );

		$id   = $this->_get_id( $prefix );
		$name = $this->_get_name( $prefix );

		$attr = array( 'id' => $id );

		if ( 'on' == $value )
			$attr['checked'] = 'checked';

		echo $this->_html_input( 'checkbox', $name, 'on', $attr );

		echo $this->_html_label( $this->_title, $id );
	}
}
