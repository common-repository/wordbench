<?php

function wordbench_taxonomy_init() {
	$taxonomies = get_option( 'wordbench_taxonomies', array() );

	foreach ( $taxonomies as $slug => $args ) {
		$name        = wordbench_sanitize( $slug );
		$object_type = $args['object_type'];

		register_taxonomy( $name, $object_type, $args );
	}
}

function wordbench_taxonomy_menu_page() {
	add_submenu_page( 'edit.php?post_type=post_type', 'Taxonomies', 'Taxonomies',
		'administrator', 'taxonomies', 'wordbench_taxonomy_menu_callback' );
}

function wordbench_taxonomy_menu_callback() {
	$nonce = @$_REQUEST['_wpnonce'];
	$url   = @$_REQUEST['_wp_http_referer'];
	$key   = 'single-taxonomy';

	if ( isset( $_REQUEST['action'] ) && wp_verify_nonce( $nonce, $key ) ) {
		switch ( $_REQUEST['action'] ) {
			case 'save':
				$taxonomy = $_REQUEST['tax'];
				$settings = wordbench_taxonomy_setting_keys();

				foreach ( $settings as $key => $descrip )
					$taxonomy[ $key ] = isset( $taxonomy[ $key ] );

				if ( ! isset( $taxonomy['object_type'] ) )
					$taxonomy['object_type'] = array();

				$slug = wordbench_sanitize( $taxonomy['labels']['name'] );

				if ( ! empty( $slug ) ) {
					$option = get_option( 'wordbench_taxonomies', array() );
					$option[ $slug ] = $taxonomy;

					update_option( 'wordbench_taxonomies', $option );
				}

				break;
			case 'delete':
				$slug = wordbench_sanitize( @$_REQUEST['slug'] );

				if ( ! empty( $slug ) ) {
					$option = get_option( 'wordbench_taxonomies', array() );

					unset( $option[ $slug ] );

					update_option( 'wordbench_taxonomies', $option );
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

	$callback = 'wordbench_taxonomy_' . $_REQUEST['view'] . '_view';

	if ( function_exists( $callback ) )
		call_user_func( $callback );
	else
		wordbench_taxonomy_list_view();
}

function wordbench_taxonomy_list_view() {
	$table = new WB_Taxonomy_List_Table();
?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2>Taxonomies <a href="edit.php?post_type=post_type&page=taxonomies&view=edit" class="add-new-h2">Add New</a></h2>
	<div id="ajax-response"></div>
	<form action="edit.php?post_type=post_type&page=taxonomies" method="post">
		<input type="hidden" name="post_type" value="post_type">
		<input type="hidden" name="page" value="taxonomies">
		<?php $table->display(); ?>
	</form>
</div>
<?php
}

function wordbench_taxonomy_edit_view() {
	$post_types = WB_Post_Type::fetch_all();
	$taxonomies = get_option( 'wordbench_taxonomies', array() );

	$slug = wordbench_sanitize( @$_REQUEST['slug'] );

	if ( isset( $taxonomies[ $slug ] ) ) {
		$taxonomy = $taxonomies[ $slug ];
	} else {
		$taxonomy = array(
			'object_type'       => array(),
			'labels'            => array(),
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => false
		);
	}

	$label_keys   = wordbench_taxonomy_label_keys();
	$setting_keys = wordbench_taxonomy_setting_keys();

	$label_defaults = array(
		'name'                       => '%P',
		'menu_name'                  => '%P',
		'singular_name'              => '%S',
		'search_items'               => 'Search %P',
		'popular_items'              => 'Popular %P',
		'all_items'                  => 'All %P',
		'parent_item'                => 'Parent %S',
		'parent_item_colon'          => 'Parent %S:',
		'edit_item'                  => 'Edit %S',
		'view_item'                  => 'View %S',
		'update_item'                => 'Update %S',
		'new_item_name'              => 'New %S',
		'add_new_item'               => 'Add New %S',
		'separate_items_with_commas' => 'Separate %p with commas',
		'add_or_remove_items'        => 'Add or remove %p',
		'choose_from_most_used'      => 'Choose from most used %p',
		'name_admin_bar'             => '%P'
	);
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#wb-gen-labels').click(function(event) {
			var title = $('#title').val();

			var plural_title = title;
			var single_title = title;

			if ('s' == title.charAt(title.length - 1)) {
				single_title = title.substr(0, title.length - 1);
			} else {
				plural_title = title + 's';
			}

			$('#wb-labels-table input[type="text"]').each(function() {
				$(this).val($(this).data('default')
					.replace('%S', single_title)
					.replace('%s', single_title.toLowerCase())
					.replace('%P', plural_title)
					.replace('%p', plural_title.toLowerCase())
				);
			});
		});
	});
</script>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2>Edit Taxonomy</h2>
	<form action="edit.php?post_type=post_type&page=taxonomies" method="post">
		<input type="hidden" name="post_type" value="post_type">
		<input type="hidden" name="page" value="taxonomies">
		<input type="hidden" name="action" value="save">
		<?php wp_nonce_field( 'single-taxonomy' ); ?>
		<div class="metabox-holder has-right-sidebar">
			<div id="side-info-column" class="inner-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>Save Taxonomy</span></h3>
						<div class="inside">
							<input type="submit" value="Save Changes" class="button-primary">
						</div>
					</div>
					<div class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>Object Type(s)</span></h3>
						<div class="inside">
							<ul>
								<li>
									<input id="object-type-post" type="checkbox"
										name="tax[object_type][]" value="post"<?php
										echo in_array( 'post', $taxonomy['object_type'] )
										   ? ' checked="checked"' : ''; ?>>
									<label for="object-type-post">Post</label>
								</li>
								<li>
									<input id="object-type-page" type="checkbox"
										name="tax[object_type][]" value="page"<?php
										echo in_array( 'page', $taxonomy['object_type'] )
										   ? ' checked="checked"' : ''; ?>>
									<label for="object-type-page">Page</label>
								</li>
								<?php foreach ( $post_types as $post_type ) :
									$name    = $post_type->get_name();
									$title   = $post_type->get_title();
									$checked = in_array( $name, $taxonomy['object_type'] )
											 ? ' checked="checked"' : '';
								?>
								<li>
									<input id="object-type-<?php echo $name; ?>" type="checkbox"
										name="tax[object_type][]" value="<?php echo $name; ?>"<?php echo $checked; ?>>
									<label for="object-type-<?php echo $name; ?>"><?php echo $title; ?></label>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<div class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>Settings</span></h3>
						<div class="inside">
							<ul>
								<?php foreach ( $setting_keys as $key => $descrip ) :
									$title   = wordbench_labelize( $key );
									$checked = $taxonomy[ $key ] ? ' checked="checked"' : '';
								?>
								<li>
									<input id="setting-<?php echo $key; ?>" type="checkbox"
										name="tax[<?php echo $key; ?>]"<?php echo $checked; ?>>
									<label for="setting-<?php echo $key; ?>" title="<?php echo esc_attr( $descrip ); ?>"><?php echo $title; ?></label>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div id="post-body">
				<div id="post-body-content">
					<div id="titlediv">
						<div id="titlewrap">
							<label for="title" id="title-prompt-text">Enter title here</label>
							<input id="title" type="text" name="title" value="<?php echo esc_attr( $taxonomy['labels']['name'] ); ?>">
						</div>
					</div>
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span>Labels</span></h3>
							<div class="inside">
								<span class="description">Labels can be generated from title.</span>
								<input id="wb-gen-labels" type="button" value="Generate" class="button">
								<table id="wb-labels-table" class="wb-form-table">
									<thead>
										<tr>
											<th class="snap">Label Name</th>
											<th class="snap">Label</th>
											<th>Description</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $label_keys as $key => $descrip ) :
											$title = wordbench_labelize( $key );
											$value = @$taxonomy['labels'][ $key ];
										?>
										<tr>
											<td class="snap">
												<label for="labels-<?php echo $key; ?>"><?php echo $title; ?></label>
											</td>
											<td class="snap">
												<input id="labels-<?php echo $key; ?>" type="text"
													name="tax[labels][<?php echo $key; ?>]"
													value="<?php echo esc_attr( $value ); ?>"
													data-default="<?php echo $label_defaults[ $key ]; ?>">
											</td>
											<td class="description"><?php echo $descrip; ?></td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>
	</form>
</div>
<?php
}

function wordbench_taxonomy_label_keys() {
	return array(
		'name'                       => 'Caption used for this taxonomy, usually plural',
		'menu_name'                  => 'Caption used for this taxonomy in admin menu',
		'singular_name'              => 'Caption used for a single term of this taxonomy',
		'search_items'               => 'Caption used for search box',
		'popular_items'              => 'Caption used for popular terms',
		'all_items'                  => 'Caption used for all terms',
		'parent_item'                => 'Caption used for parent term',
		'parent_item_colon'          => 'Caption used for parent term with colon',
		'edit_item'                  => 'Caption used for editing a single term',
		'view_item'                  => 'Caption used for viewing a single term',
		'update_item'                => 'Caption used for saving a single term',
		'new_item_name'              => 'Caption used for a new term',
		'add_new_item'               => 'Caption used for adding a new term',
		'separate_items_with_commas' => '',
		'add_or_remove_items'        => 'Caption used for adding/removing a term from a post',
		'choose_from_most_used'      => 'Caption for the most commonly used terms',
		'name_admin_bar'             => ''
	);
}

function wordbench_taxonomy_setting_keys() {
	return array(
		'show_ui'           => 'This taxonomy will have an admin interface',
		'show_in_nav_menus' => 'This taxonomy can be used in nav menus',
		'show_tagcloud'     => 'This taxonomy can be used in the tag cloud widget',
		'hierarchical'      => 'This taxonomy is hierarchical'
	);
}

class WB_Taxonomy_List_Table extends WP_List_Table {
	var $_post_type = 'post_type';
	var $_menu_slug = 'taxonomies';

	function __construct() {
		parent::__construct( array(
			'singular' => 'taxonomy',
			'plural'   => 'taxonomies'
		) );
	}

	function display() {
		$this->process_bulk_action();
		$this->prepare_items();

		parent::display();
	}

	function prepare_items() {
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		$this->items = array();

		$option = get_option( 'wordbench_taxonomies', array() );

		foreach ( $option as $slug => $args ) {
			$item = array(
				'slug'        => $slug,
				'name'        => $args['labels']['name'],
				'object_type' => $args['object_type'],
				'settings'    => array()
			);

			foreach ( wordbench_taxonomy_setting_keys() as $key => $descrip ) {
				if ( $args[ $key ] ) {
					$item['settings'][] = $key;
				}
			}

			$this->items[] = $item;
		}

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
			'cb'          => '<input type="checkbox">',
			'name'        => 'Name',
			'object_type' => 'Object Type(s)',
			'settings'    => 'Settings'
		);
	}

	function get_sortable_columns() {
		return array(
			array( 'name', true )
		);
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="taxonomy[%s]" value="%s">',
			$item['slug'],
			esc_attr( $item['name'] )
		);
	}

	function column_name( $item ) {
		$refurl = sprintf( 'edit.php?post_type=%s&page=%s',
			$this->_post_type,
			$this->_menu_slug
		);

		$editurl   = $refurl . '&view=edit&slug=' . $item['slug'];
		$deleteurl = $refurl . '&action=delete&slug=' . $item['slug'];

		$actions = array(
			'edit' => sprintf( '<a href="%s">Edit</a>',
				$editurl
			),
			'delete' => sprintf( '<a href="%s&_wp_http_referer=%s">Delete</a>',
				wp_nonce_url( $deleteurl, 'single-taxonomy' ),
				urlencode( $refurl )
			)
		);

		return sprintf( '<a href="%s">%s</a> %s',
			$editurl,
			$item['name'],
			$this->row_actions( $actions )
		);
	}

	function column_object_type( $item ) {
		$object_types = array();

		foreach ( $item['object_type'] as $object_type ) {
			$post_type = get_post_type_object( $object_type );
			$object_types[] = $post_type->label;
		}

		return implode( ', ', $object_types );
	}

	function column_settings( $item ) {
		$settings = array();

		foreach ( $item['settings'] as $setting ) {
			$settings[] = wordbench_labelize( $setting );
		}

		return implode( ', ', $settings );
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
					$option = get_option( 'wordbench_taxonomies', array() );

					foreach ( (array) @$_REQUEST['taxonomy'] as $slug => $name ) {
						unset( $option[ $slug ] );
					}

					update_option( 'wordbench_taxonomies', $option );

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
