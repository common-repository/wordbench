<?php

function wordbench_register_post_format( $slug, $title = null ) {
	global $_wp_theme_features, $wordbench_post_formats;

	if ( empty( $wordbench_post_formats ) )
		$wordbench_post_formats = array();

	$slug = wordbench_sanitize( $slug );

	if ( empty( $title ) )
		$title = wordbench_labelize( $slug );

	if ( is_array( $_wp_theme_features['post-formats'][0] ) ) {
		$_wp_theme_features['post-formats'][0][] = $slug;
		$wordbench_post_formats[ $slug ] = $title;
	}
}

function wordbench_post_format_init() {
	$post_formats = get_option( 'wordbench_post_formats', array() );
	foreach ( $post_formats as $slug => $title )
		wordbench_register_post_format( $slug, $title );
}

function wordbench_post_format_titles() {
	global $wordbench_post_formats;
	return array_merge( get_post_format_strings(), $wordbench_post_formats );
}

function wordbench_post_format_title( $slug ) {
	$titles = wordbench_post_format_titles();
	return isset( $titles[ $slug ] ) ? $titles[ $slug ] : '';
}

function wordbench_post_format_slugs() {
	$slugs = array_keys( wordbench_post_format_titles() );
	return array_combine( $slugs, $slugs );
}

function wordbench_post_format_menu_page() {
	if ( current_theme_supports( 'post-formats' ) ) {
		add_submenu_page( 'edit.php?post_type=post_type', 'Formats', 'Formats',
			'administrator', 'post-formats', 'wordbench_post_format_menu_callback' );
	}
}

function wordbench_post_format_menu_callback() {
	$nonce = @$_REQUEST['_wpnonce'];
	$url   = @$_REQUEST['_wp_http_referer'];
	$key   = 'single-post-format';

	if ( isset( $_REQUEST['action'] ) && wp_verify_nonce( $nonce, $key ) ) {
		switch ( $_REQUEST['action'] ) {
			case 'save':
				$slug = @$_REQUEST['slug'];
				$name = @$_REQUEST['name'];

				if ( empty( $slug ) ) $slug = $name;

				$slug = wordbench_sanitize( $slug );

				if ( ! empty( $slug ) ) {
					$post_formats = get_option( 'wordbench_post_formats', array() );
					$post_formats[ $slug ] = $name;

					update_option( 'wordbench_post_formats', $post_formats );
				}

				break;
			case 'delete':
				$slug = wordbench_sanitize( @$_REQUEST['slug'] );

				if ( ! empty( $slug ) ) {
					$post_formats = get_option( 'wordbench_post_formats', array() );

					unset( $post_formats[ $slug ] );

					update_option( 'wordbench_post_formats', $post_formats );
				}

				break;
		}

		if ( ! empty( $url ) ) {
			echo '<script type="text/javascript">'
			   . 'document.location = "' . $url . '";'
			   . '</script>';

			exit;
		}
	}

	wordbench_post_format_edit_page();
}

function wordbench_post_format_edit_page() {
	$table = new WB_Post_Format_List_Table();
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#add-new').click(function() {
			$(this.href.match(/#.*$/)[0]).toggle('blind', null, 'fast');
			return false;
		});
	});
</script>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2>Formats <a href="#add-new-form" id="add-new" class="add-new-h2 hide-if-no-js">Add New</a></h2>
	<div id="ajax-response"></div>
	<div id="add-new-form" class="form-wrap hide-if-js">
		<form action="edit.php?post_type=post_type&page=post-formats" method="post">
			<input type="hidden" name="action" value="save">
			<?php wp_nonce_field( 'single-post-format' ); ?>
			<label for="format-name" style="display: inline;">Name</label>
			<input id="format-name" type="text" size="40" name="name">
			<input id="submit" class="button" type="submit" name="submit" value="Add New Format">
		</form>
	</div>
	<form action="edit.php?post_type=post_type&page=post-formats" method="post">
		<?php $table->display(); ?>
	</form>
</div>
<?php
}

function wordbench_post_format_save_post( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) return;

	$post_type = get_post_type( $post_id );

	if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post_type , 'post-formats' ) ) {
		$post_format = sanitize_title( @$_POST['post_format'] );

		if ( ! empty( $post_format ) && $post_format != get_post_format( $post_id ) )
			wp_set_post_terms( $post_id, 'post-format-' . $post_format, 'post_format' );
	}
}

function wordbench_post_format_select() {
	global $post, $wordbench_post_formats;

	if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) ) {
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php foreach ( $wordbench_post_formats as $slug => $title ) : ?>
		$('label[for="post-format-<?php echo $slug; ?>"]').text('<?php echo esc_attr( $title ); ?>');
		<?php endforeach; ?>
	});
</script>
<?php
	}
}

class WB_Post_Format_List_Table extends WP_List_Table {
	var $_post_type = 'post_type';
	var $_menu_slug = 'post-formats';

	function __construct() {
		parent::__construct( array(
			'singular' => 'post-format',
			'plural'   => 'post-formats'
		) );
	}

	function display() {
		$this->process_bulk_action();
		$this->prepare_items();

		parent::display();
	}

	function prepare_items() {
		global $wordbench_post_formats;

		if ( ! is_array( $wordbench_post_formats ) )
			$wordbench_post_formats = array();

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		$this->items = array();

		foreach ( $wordbench_post_formats as $slug => $name )
			$this->items[] = array( 'slug' => $slug, 'name' => $name );

		$per_page = 20;

		$total_items = count( $this->items );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'    => $per_page
		) );
	}

	function get_columns() {
		return array(
			'cb'   => '<input type="checkbox">',
			'name' => 'Name',
			'slug' => 'Slug'
		);
	}

	function get_sortable_columns() {
		return array(
			array( 'name', true ),
			array( 'slug', true )
		);
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="post_format[%s]" value="%s">',
			$item['slug'],
			esc_attr( $item['name'] )
		);
	}

	function column_name( $item ) {
		$refurl = sprintf( 'edit.php?post_type=%s&page=%s',
			$this->_post_type,
			$this->_menu_slug
		);

		$actionurl = $refurl . '&action=delete&slug=' . $item['slug'];

		$actions = array(
			'delete' => sprintf( '<a href="%s&_wp_http_referer=%s">Delete</a>',
				wp_nonce_url( $actionurl, 'single-post-format' ),
				urlencode( $refurl )
			)
		);

		return sprintf( '<a href="#">%s</a> %s',
			$item['name'],
			$this->row_actions( $actions )
		);
	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	function get_bulk_actions() {
		return array(
			'delete' => 'Delete'
		);
	}

	function process_bulk_action() {
		$nonce = @$_REQUEST['_wpnonce'];
		$url   = @$_REQUEST['_wp_http_referer'];

		if ( '' != $this->current_action() && wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
			switch ( $this->current_action() ) {
				case 'delete':
					$option = get_option( 'wordbench_post_formats', array() );

					foreach ( (array) @$_REQUEST['post_format'] as $slug => $name ) {
						unset( $option[ $slug ] );
					}

					update_option( 'wordbench_post_formats', $option );

					break;
			}

			if ( ! empty( $url ) ) {
				echo '<script type="text/javascript">'
				   . 'document.location = "' . $url . '";'
				   . '</script>';

				exit;
			}
		}
	}
}
