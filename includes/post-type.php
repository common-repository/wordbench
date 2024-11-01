<?php

/**
 * Callback for init action. Initializes custom post types.
 *
 * @uses WB_Post_Type::register()
 */
function wordbench_post_type_init() {
	$labels = array(
		'name'               => 'Post Types',
		'menu_name'          => 'Post Types',
		'singular_name'      => 'Post Type',
		'name_admin_bar'     => 'Post Type',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Post Type',
		'new_item'           => 'New Post Type',
		'edit_item'          => 'Edit Post Type',
		'view_item'          => 'View Post Type',
		'all_items'          => 'All Post Types',
		'search_items'       => 'Search Post Types',
		'not_found'          => 'No post types found',
		'not_found_in_trash' => 'No post types found in Trash',
		'parent_item_colon'  => 'Parent Post Type:'
	);

	$args = array(
		'labels'              => $labels,
		'description'         => "A custom post type for managing your custom post types...There is no spoon.",
		'public'              => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => false,
		//'capability_type'     => 'post_type',
		'map_meta_cap'        => true,
		'hierarchical'        => true,
		'has_archive'         => false,
		'can_export'          => false,
		'rewrite'             => false,
		'query_var'           => false,
		'supports'            => array( 'title', 'editor', 'page-attributes' ),
		'menu_position'       => 5
	);

	register_post_type( 'post_type', $args );

	$role = get_role( 'administrator' );
	$cap  = get_post_type_object( 'post_type' )->cap;

	if ( ! $role->has_cap( $cap->edit_posts ) ) {
		$wp_roles = wp_roles();

		foreach ( $wp_roles->get_names() as $role_key => $role_name ) {
			$role = get_role( $role_key );

			foreach ( (array) $cap as $cap_key => $cap_value ) {
				if ( $role->has_cap( $cap_key ) ) {
					$role->add_cap( $cap_value );
				}
			}
		}
	}

	$post_types = get_posts( array(
		'post_type'      => 'post_type',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'ASC'
	) );

	foreach ( $post_types as $post ) {
		WB_Post_Type::register( $post );
	}
}

/**
 * Callback for add_meta_boxes action. Initializes meta boxes for post type
 * manager and custom post type fields.
 *
 * @uses WB_Post_Type::fetch_all()
 */
function wordbench_post_type_meta_boxes() {
	add_meta_box( 'wb-post-type-caps', 'Capabilities',
		'wordbench_post_type_meta_caps', 'post_type', 'advanced' );
	add_meta_box( 'wb-post-type-labels', 'Labels',
		'wordbench_post_type_meta_labels', 'post_type', 'normal' );
	add_meta_box( 'wb-post-type-fields', 'Fields',
		'wordbench_post_type_meta_fields', 'post_type', 'normal' );
	add_meta_box( 'wb-post-type-settings', 'Settings',
		'wordbench_post_type_meta_settings', 'post_type', 'side' );
	add_meta_box( 'wb-post-type-supports', 'Supports',
		'wordbench_post_type_meta_supports', 'post_type', 'side' );
	add_meta_box( 'wb-post-type-taxonomies', 'Taxonomies',
		'wordbench_post_type_meta_taxonomies', 'post_type', 'side' );

	$post_types = WB_Post_Type::fetch_all();

	foreach ( $post_types as $post_type ) {
		/* add_meta_box( 'wb-post-type-meta-box',
			$post_type->get_label( 'edit_item' ),
			'wordbench_post_type_meta_edit',
			$post_type->get_name(),
			'advanced', 'default',
			$post_type->get_fields() ); */

		foreach ( $post_type->get_fields() as $field ) {
			add_meta_box( 'wb-post-type-meta-box-' . $field['name'],
				$field['title'],
				'wordbench_post_type_meta_field_edit',
				$post_type->get_name(),
				'advanced', 'default', $field );
		}
	}
}

/**
 * Callback for add_meta_box(). Renders meta box for custom post type
 * capabilities.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_labelize()
 * @param object $post The post containing metadata for a custom post type.
 * @param array $args Unused.
 */
function wordbench_post_type_meta_caps( $post, $args = array() ) {
	global $wp_roles;

	$roles = $wp_roles->get_names();
	$caps  = (array) get_post_type_object( 'post_type' )->cap;

	$post_type_caps = get_post_meta( $post->ID, '_post_type_caps', true );

	if ( empty( $post_type_caps ) ) {
		foreach ( array_keys( $roles ) as $role_key ) {
			$role = get_role( $role_key );
			foreach ( array_keys( $caps ) as $cap_key ) {
				$post_type_caps[ $role_key ][ $cap_key ] = $role->has_cap( $cap_key );
			}
		}
	}
?>
<table class="wb-form-table">
	<thead>
		<tr>
			<th></th>
			<?php foreach ( $roles as $role_key => $role_title ) : ?>
			<th style="text-align: center;">
				<input id="role-<?php echo $role_key; ?>" type="checkbox">
				<label for="role-<?php echo $role_key; ?>"><?php echo $role_title; ?></label>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $caps as $cap_key => $cap_name ) :
			if ( ! in_array( $cap_key, array( 'read_post', 'edit_post', 'delete_post' ) ) ) :
				$cap_title = wordbench_labelize( $cap_key );
		?>
		<tr>
			<th scope="row">
				<input id="cap-<?php echo $cap_key; ?>" type="checkbox">
				<label for="cap-<?php echo $cap_key; ?>"><?php echo $cap_title; ?></label>
			</th>
			<?php foreach ( $roles as $role_key => $role_title ) :
				$name = "post_type_meta[caps][{$role_key}][{$cap_key}]";
			?>
			<td style="text-align: center;">
				<input type="checkbox" name="<?php echo $name; ?>"
					<?php checked( $post_type_caps[ $role_key ][ $cap_key ] ); ?>>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endif; endforeach; ?>
	</tbody>
</table>
<?php
}

/**
 * Callback for add_meta_box(). Renders meta box for custom post type labels.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_labelize()
 * @uses WB_Post_Type::get_static_labels()
 * @param object $post The post containing metadata for a custom post type.
 * @param array $args Unused.
 */
function wordbench_post_type_meta_labels( $post, $args = array() ) {
	$post_type_labels = get_post_meta( $post->ID, '_post_type_labels', true );

	if ( empty( $post_type_labels ) ) {
		$post_type_labels = array();
	}

	$labels = WB_Post_Type::get_static_labels();
?>
<script type="text/javascript">
	jQuery(window).ready(function($) {
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
<span class="description">Default labels can be generated from the post type title.</span>
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
		<?php foreach ( $labels as $key => $label ) :
			$title = wordbench_labelize( $key );
		?>
		<tr>
			<td class="snap">
				<label for="wb-label-<?php echo $key; ?>"><?php echo $title; ?></label>
			</td>
			<td class="snap">
				<input id="wb-label-<?php echo $key; ?>" type="text" size="40"
						name="post_type_meta[labels][<?php echo $key; ?>]"
						value="<?php esc_attr_e( $post_type_labels[ $key ] ); ?>"
						data-default="<?php esc_attr_e( $label['default'] ); ?>">
			</td>
			<td class="description"><?php echo $label['descrip']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
}

/**
 * Callback for add_meta_box(). Renders meta box for custom post type fields.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_post_type_field_types()
 * @param object $post The post containing metadata for a custom post type.
 * @param array $args Unused.
 */
function wordbench_post_type_meta_fields( $post, $args = array() ) {
	$post_type_fields = get_post_meta( $post->ID, '_post_type_fields', true );

	if ( empty( $post_type_fields ) ) {
		$post_type_fields = array();
	}
?>
<script type="text/javascript">
	jQuery(window).ready(function($) {
		$('.wb-add-field').click(function(event) {
			var nameCell = $('<td/>', {
				'class': 'snap'
			}).append($('<input/>', {
				'type': 'text',
				'size': '30',
				'name': 'post_type_meta[fields][title][]'
			}));

			var typeCell = $('<td/>', {
				'class': 'snap'
			}).append($('<select/>', {
				'name': 'post_type_meta[fields][type][]'
			})
			<?php foreach ( wordbench_post_type_field_types() as $type => $name ) : ?>
			.append($('<option/>', { 'value': '<?php echo $type; ?>' }).text('<?php echo $name; ?>'))
			<?php endforeach; ?>
			);

			var optionCell = $('<td/>').append($('<textarea/>', {
				'name': 'post_type_meta[fields][opts][]'
			}));

			var removeCell = $('<td/>', {
				'class': 'snap'
			}).append($('<a/>', {
				'href':  '#',
				'title': 'Remove Field',
				'class': 'wb-remove-field button'
			}).text('- Remove Field'));

			var row = $('<tr/>')
				.append(nameCell)
				.append(typeCell)
				.append(optionCell)
				.append(removeCell);

			$('#wb-fields-table tbody').append(row);

			return false;
		});

		$('.wb-remove-field').live('click', function(event) {
			if ( confirm( 'Are you sure you want to remove this field?' ) )
				$(event.target).parents('tr').first().remove();

			return false;
		});
	});
</script>
<table id="wb-fields-table" class="wb-form-table">
	<thead>
		<tr>
			<th class="snap">Field Name</th>
			<th class="snap">Type</th>
			<th>
				<span>Options</span>
				<span style="font-size: smaller; font-weight: normal; font-style: italic;">One per line</span>
			</th>
			<th class="snap"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $post_type_fields as $field ) :
			$opt_count = count( $field['opts'] );
			$row_count = max( min( $opt_count, 5 ), 2 );
		?>
		<tr>
			<td class="snap">
				<input type="text" size="30" name="post_type_meta[fields][title][]"
					value="<?php esc_attr_e( $field['title'] ); ?>">
			</td>
			<td class="snap">
				<select name="post_type_meta[fields][type][]">
					<?php foreach ( wordbench_post_type_field_types() as $type => $name ) : ?>
					<option value="<?php esc_attr_e( $type ); ?>"<?php if ( $type == $field['type'] )
						echo ' selected="selected"'; ?>><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<textarea name="post_type_meta[fields][opts][]" rows="<?php echo $row_count; ?>"><?php echo implode( PHP_EOL, $field['opts'] ); ?></textarea>
			</td>
			<td class="snap">
				<a href="#" title="Remove Field" class="wb-remove-field button">- Remove Field</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">
				<a href="#" title="Add Field" class="wb-add-field button">+ Add Field</a>
			</td>
		</tr>
	</tfoot>
</table>
<?php
}

/**
 * Callback for add_meta_box(). Renders meta box for custom post type settings.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_labelize()
 * @uses WB_Post_Type::get_default_settings()
 * @uses WB_Post_Type::get_static_settings()
 * @param object $post The post containing metadata for a custom post type.
 * @param array $args Unused.
 */
function wordbench_post_type_meta_settings( $post, $args = array() ) {
	$post_type_settings = get_post_meta( $post->ID, '_post_type_settings', true );

	if ( empty( $post_type_settings ) ) {
		$post_type_settings = WB_Post_Type::get_default_settings();
	}

	$settings = WB_Post_Type::get_static_settings();
?>
<ul>
	<?php foreach ( $settings as $key => $setting ) :
		$title = wordbench_labelize( $key );
	?>
	<li>
		<input id="wb-settings-<?php echo $key; ?>" type="checkbox"
			name="post_type_meta[settings][<?php echo $key; ?>]"
			<?php checked( $post_type_settings[ $key ] ); ?>>
		<label for="wb-settings-<?php echo $key; ?>"
			title="<?php esc_attr_e( $setting['descrip'] ); ?>">
			<?php echo $title; ?></label>
	</li>
	<?php endforeach; ?>
</ul>
<?php
}

/**
 * Callback for add_meta_box(). Renders meta box for custom post type supports.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_labelize()
 * @uses WB_Post_Type::get_default_settings()
 * @uses WB_Post_Type::get_static_settings()
 * @param object $post The post containing metadata for a custom post type.
 * @param array $args Unused.
 */
function wordbench_post_type_meta_supports( $post, $args = array() ) {
	$post_type_supports = get_post_meta( $post->ID, '_post_type_supports', true );

	if ( empty( $post_type_supports ) ) {
		$post_type_supports = WB_Post_Type::get_default_supports();
	}

	$supports = WB_Post_Type::get_static_supports();
?>
<ul>
	<?php foreach ( $supports as $key => $support ) :
		$title = wordbench_labelize( $key );
	?>
	<li>
		<input id="wb-supports-<?php echo $key; ?>" type="checkbox"
			name="post_type_meta[supports][<?php echo $key; ?>]"
			<?php checked( $post_type_supports[ $key ] ); ?>>
		<label for="wb-supports-<?php echo $key; ?>"
			title="<?php esc_attr_e( $support['descrip'] ); ?>">
			<?php echo $title; ?></label>
	</li>
	<?php endforeach; ?>
</ul>
<?php
}

function wordbench_post_type_meta_taxonomies( $post, $args = array() ) {
	$post_type_taxonomies = get_post_meta( $post->ID, '_post_type_taxonomies', true );

	if ( empty( $post_type_taxonomies ) ) {
		$post_type_taxonomies = array();
	}

	$taxonomies = array();

	foreach ( get_taxonomies() as $tax_slug ) {
		$taxonomy = get_taxonomy( $tax_slug );

		if ( $taxonomy->show_ui ) {
			$taxonomies[] = $taxonomy;
		}
	}
?>
<ul>
	<?php foreach ( $taxonomies as $taxonomy ) :
		$slug = $taxonomy->name;
	?>
	<li>
		<input id="wb-taxonomies-<?php echo $slug; ?>" type="checkbox"
			name="post_type_meta[taxonomies][<?php echo $slug; ?>]"
			<?php checked( in_array( $slug, $post_type_taxonomies ) ); ?>>
		<label for="wb-taxonomies-<?php echo $slug; ?>">
			<?php echo $taxonomy->label; ?></label>
	</li>
	<?php endforeach; ?>
</ul>
<?php
}

/**
 * Callback for add_meta_box(). Renders meta box for post's custom fields.
 *
 * @see wordbench_post_type_meta_boxes()
 * @uses wordbench_the_form_element()
 * @param object $post The post currently being editted.
 * @param array $args The the custom field values as associative array.
 */
function wordbench_post_type_meta_edit( $post, $args = array() ) {
?>
<table id="wb-fields-table" class="wb-form-table">
	<tbody>
		<?php foreach ( $args['args'] as $field ) :
			$value = get_post_meta( $post->ID, '_' . $field['name'], true );
		?>
		<tr>
			<td><label for="post_meta-<?php esc_attr_e( $field['name'] ); ?>"><?php echo $field['title']; ?></label></td>
			<td>
				<?php wordbench_the_form_element( $field, array(
					'show_label' => false,
					'prefix'     => 'post_meta',
					'value'      => $value
				) ); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
}

function wordbench_post_type_meta_field_edit( $post, $args ) {
	$field = $args['args'];
	$value = get_post_meta( $post->ID, '_' . $field['name'], true );

	wordbench_the_form_element( $field, array(
		'show_label' => false,
		'prefix'     => 'post_meta',
		'value'      => $value
	) );
}

/**
 * Callback for save_post action. Saves custom post type metadata and field
 * values.
 *
 * @uses wordbench_post_type_save_metadata()
 * @uses wordbench_post_type_save_instance()
 * @param int $post_id The ID of the post being saved.
 */
function wordbench_post_type_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// this might be redundant
	if ( 'auto-draft' == get_post( $post_id )->post_status ) {
		return;
	}

	if ( 'post_type' == $_REQUEST['post_type'] ) {
		wordbench_post_type_save_metadata( $post_id );
	} elseif ( @is_array( $_REQUEST['post_meta'] ) ) {
		wordbench_post_type_save_instance( $post_id );
	}
}

/**
 * Saves custom post type metadata.
 *
 * @see wordbench_post_type_save_post()
 * @uses wordbench_sanitize()
 * @uses WB_Post_Type::get_static_labels()
 * @uses WB_Post_Type::get_static_settings()
 * @uses WB_Post_Type::get_static_supports()
 * @uses WB_Post_Type::register()
 * @param int $post_id The ID of the post containing metadata for the custom
 *    post type.
 */
function wordbench_post_type_save_metadata( $post_id ) {
	if ( ! current_user_can( 'edit_post_type', $post_id ) ) {
		return;
	}

	$meta = $_REQUEST['post_type_meta'];

	$caps       = array();
	$labels     = array();
	$fields     = array();
	$settings   = array();
	$supports   = array();
	$taxonomies = array();

	if ( isset( $meta['caps'] ) && is_array( $meta['caps'] ) ) {
		global $wp_roles;

		$static_roles = $wp_roles->get_names();
		$static_caps  = (array) get_post_type_object( 'post_type' )->cap;

		foreach ( array_keys( $static_roles ) as $role_key ) {
			foreach ( array_keys( $static_caps ) as $cap_key ) {
				$caps[ $role_key ][ $cap_key ] = isset( $meta['caps'][ $role_key ][ $cap_key ] );
			}
		}
	}

	if ( isset( $meta['labels'] ) && is_array( $meta['labels'] ) ) {
		$static_labels = WB_Post_Type::get_static_labels();

		foreach ( $static_labels as $key => $label ) {
			if ( ! empty( $meta['labels'][ $key ] ) ) {
				$labels[ $key ] = $meta['labels'][ $key ];
			}
		}
	}

	if ( isset( $meta['fields'] ) && is_array( $meta['fields'] ) ) {
		for ( $i = 0, $n = count( $meta['fields']['title'] ); $i < $n; $i++ ) {
			if ( ! empty( $meta['fields']['title'][ $i ] ) ) {
				$name = wordbench_sanitize( $meta['fields']['title'][ $i ] );
				$opts = explode( PHP_EOL, $meta['fields']['opts'][ $i ] );

				foreach ( $opts as $index => $opt ) {
					$opts[ $index ] = trim( $opt );

					if ( empty( $opts[ $index ] ) ) {
						unset( $opts[ $index ] );
					}
				}

				$fields[] = array(
					'name'  => $name,
					'title' => $meta['fields']['title'][ $i ],
					'type'  => $meta['fields']['type'][ $i ],
					'opts'  => $opts
				);
			}
		}
	}

	if ( isset( $meta['settings'] ) && is_array( $meta['settings'] ) ) {
		$static_settings = WB_Post_Type::get_static_settings();

		foreach ( $static_settings as $key => $setting ) {
			$settings[ $key ] = isset( $meta['settings'][ $key ] );
		}
	}

	if ( isset( $meta['supports'] ) && is_array( $meta['supports'] ) ) {
		$static_supports = WB_Post_Type::get_static_supports();

		foreach ( $static_supports as $key => $support ) {
			$supports[ $key ] = isset( $meta['supports'][ $key ] );
		}
	}

	// TODO $meta['taxonomies'] will not be set if user unchecks all taxonomies
	if ( isset( $meta['taxonomies'] ) && is_array( $meta['taxonomies'] ) ) {
		$taxonomies = array_keys( $meta['taxonomies'] );

		if ( $post = get_post( $post_id ) ) {
			$post_type = $post->post_name;

			$option = get_option( 'wordbench_taxonomies', array() );

			foreach ( $option as $slug => $taxonomy ) {
				$object_type = $taxonomy['object_type'];

				if ( in_array( $slug, $taxonomies ) ) {
					$object_type[] = $post_type;
				} elseif ( ( $index = array_search( $post_type, $object_type ) ) !== false ) {
					unset( $object_type[ $index ] );
				}

				$option[ $slug ]['object_type'] = array_values( array_unique( $object_type ) );
			}

			update_option( 'wordbench_taxonomies', $option );
		}
	}

	update_post_meta( $post_id, '_post_type_caps',       $caps       );
	update_post_meta( $post_id, '_post_type_labels',     $labels     );
	update_post_meta( $post_id, '_post_type_fields',     $fields     );
	update_post_meta( $post_id, '_post_type_settings',   $settings   );
	update_post_meta( $post_id, '_post_type_supports',   $supports   );
	update_post_meta( $post_id, '_post_type_taxonomies', $taxonomies );

	global $wp_rewrite;

	WB_Post_Type::register( get_post( $post_id ) );

	$wp_rewrite->flush_rules();
}

/**
 * Saves custom post type field values.
 *
 * @see wordbench_post_type_save_post()
 * @uses wordbench_sanitize()
 * @uses wordbench_get_form_element()
 * @uses WB_Form_Element::validate()
 * @uses WB_Post_Type::fetch()
 * @uses WB_Post_Type::get_fields()
 * @param int $post_id The ID of the post being saved.
 */
function wordbench_post_type_save_instance( $post_id ) {
	$post_type = WB_Post_Type::fetch( $_REQUEST['post_type'] );
	$post_meta = $_REQUEST['post_meta'];

	$fields = $post_type->get_fields();

	foreach ( $fields as $field ) {
		$element = wordbench_get_form_element( $field );

		$meta_key   = '_' . $field['name'];
		$meta_value = $post_meta[ $field['name'] ];

		$meta_value = $element->validate( $meta_value );

		update_post_meta( $post_id, $meta_key, $meta_value );
	}
}

/**
 * Returns array of input types for custom post type fields.
 *
 * @see wordbench_post_type_meta_fields()
 * @return array Returns input types and names as associative array.
 */
function wordbench_post_type_field_types() {
	return array(
		'text'     => 'Inline Text',
		'textarea' => 'Block Text',
		'checkbox' => 'Check Box',
		'radio'    => 'Radio Button',
		'select'   => 'Drop-down Menu'
	);
}
