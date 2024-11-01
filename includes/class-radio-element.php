<?php

class WB_Radio_Element extends WB_Enum_Element {
	public function element( $instance = array() ) {
		$defaults = array(
			'prefix' => '',
			'value'  => ''
		);

		extract( wp_parse_args( $instance, $defaults ), EXTR_SKIP );

		$html = '';

		foreach ( $this->_opts as $opt ) {
			$key = wordbench_sanitize( $opt );

			$id   = $this->_get_id( $prefix ) . '-' . $key;
			$name = $this->_get_name( $prefix );

			$attr = array( 'id' => $id );

			if ( $opt == $value ) {
				$attr['checked'] = 'checked';
			}

			$input = $this->_html_input( 'radio', $name, $value, $attr );
			$label = $this->_html_label( $opt, $id );

			$html .= '<li>' . $input . $label . '</li>';
		}

		echo $this->_html_tag( 'ul', $html, array(
			'class' => 'form-element radio-element'
		) );
	}
}
