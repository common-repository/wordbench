<?php

class WB_Select_Element extends WB_Enum_Element {
	public function element( $instance = array() ) {
		$defaults = array(
			'show_label' => true,
			'prefix'     => '',
			'value'      => ''
		);

		extract( wp_parse_args( $instance, $defaults ), EXTR_SKIP );

		$id   = $this->_get_id( $prefix );
		$name = $this->_get_name( $prefix );

		$attr = array( 'id' => $id );

		if ( $show_label ) {
			echo $this->_html_label( $this->_title, $id );
		}

		echo $this->_html_select( $name, $value, $this->_opts, $attr );
	}

	protected function _html_select( $name, $value, $opts = array(), $attr = array() ) {
		$body = '';

		foreach ( $opts as $opt ) {
			$opt_attr = array( 'value' => $opt );

			if ( $opt == $value ) {
				$opt_attr['selected'] = 'selected';
			}

			$body .= $this->_html_tag( 'option', $opt, $opt_attr );
		}

		$attr['name'] = $name;

		return $this->_html_tag( 'select', $body, $attr );
	}
}
