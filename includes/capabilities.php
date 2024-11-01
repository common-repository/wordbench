<?php

/**
 * Adds Roles & Capabilities subpage to Users menu
 */
function wordbench_capabilities_admin_menu() {
	add_users_page( 'User Roles and Capabilities', 'Roles and Capabilities',
		'edit_users', 'capabilities', 'wordbench_capabilities_admin_page' );
}

/**
 * Handles postbacks and delegates the list/edit views
 */
function wordbench_capabilities_admin_page() {
	global $wp_roles;

	$nonce = @$_REQUEST['_wpnonce'];
	$url   = @$_REQUEST['_wp_http_referer'];

	if ( isset( $_REQUEST['action'] ) && wp_verify_nonce( $nonce, 'single-user-role' ) ) {
		$builtin_roles = wordbench_capabilities_builtin_roles();

		switch ( $_REQUEST['action'] ) {
			case 'save':
				$slug = @$_REQUEST['slug'];
				$name = @$_REQUEST['name'];

				if ( empty( $slug ) ) {
					$slug = $name;
				}

				$slug = wordbench_sanitize( $slug );

				if ( ! empty( $slug ) && ! isset( $builtin_roles[ $slug ] ) ) {
					$wp_roles->add_role( $slug, $name );
				}

				if ( ! empty( $_REQUEST['cap'] ) && 'administrator' != $slug ) {
					$caps = array_merge(
						wordbench_capabilities_builtin(),
						wordbench_capabilities_post_types()
					);

					foreach ( $caps as $group => $group_caps ) {
						foreach ( $group_caps as $cap ) {
							$wp_roles->add_cap( $slug, $cap, isset( $_REQUEST['cap'][ $cap ] ) );
						}
					}
				}

				break;
			case 'delete':
				$slug = wordbench_sanitize( @$_REQUEST['slug'] );

				if ( ! empty( $slug ) && ! isset( $builtin_roles[ $slug ] ) ) {
					$wp_roles->remove_role( $slug );
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

	switch ( @$_REQUEST['view'] ) {
		case 'edit':
			wordbench_capabilities_edit_view();
			break;
		case 'list':
		default:
			wordbench_capabilities_list_view();
			break;
	}
}

/**
 * List View
 * Displays form for adding new roles and list of currently existing roles
 */
function wordbench_capabilities_list_view() {
	$table = new WB_User_Role_List_Table();
?>
<div class="wrap">
	<div id="icon-users" class="icon32"><br></div>
	<h2>User Roles and Capabilities</h2>
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<form action="users.php?page=capabilities" method="post">
					<?php $table->display(); ?>
				</form>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3>Add New Role</h3>
					<form action="users.php?page=capabilities" method="post">
						<input type="hidden" name="action" value="save">
						<?php wp_nonce_field( 'single-user-role' ); ?>
						<div class="form-field form-required">
							<label for="role-name">Name</label>
							<input id="role-name" type="text" size="40" name="name">
							<p>[description]</p>
						</div>
						<div class="form-field">
							<label for="role-slug">Slug</label>
							<input id="role-slug" type="text" size="40" name="slug">
							<p>[description]</p>
						</div>
						<p class="submit">
							<input type="submit" value="Add New Role" class="button">
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

/**
 * Edit View
 * Displays form for editting role name and granting/revoking capabilities
 */
function wordbench_capabilities_edit_view() {
	global $wp_roles;

	$builtin_roles = wordbench_capabilities_builtin_roles();

	$slug = @$_REQUEST['slug'];

	$builtin_caps  = wordbench_capabilities_builtin();
	$builtin_roles = wordbench_capabilities_builtin_roles();

	if ( empty( $slug ) || ! isset( $builtin_roles[ $slug ] ) || true ) {
		if ( $role = $wp_roles->get_role( $slug ) ) {
			$role->name = $wp_roles->role_names[ $slug ];
			$role->slug = $slug;

			$role->builtin = isset( $builtin_roles[ $slug ] );
		}
	}

	$max_rows = 0;

	foreach ( $builtin_caps as $group ) {
		$max_rows = max( $max_rows, count( $group ) );
	}

	$post_type_caps = wordbench_capabilities_post_types();
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.cap_group').click(function() {
			var checked = this.checked;

			$('.' + $(this).attr('id')).each(function() {
				this.checked = checked;
			});
		});
	});
</script>
<div class="wrap">
	<div id="icon-users" class="icon32"><br></div>
	<h2>Edit Role</h2>
	<form action="users.php?page=capabilities" method="post">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>">
		<?php wp_nonce_field( 'single-user-role' ); ?>
		<div class="metabox-holder">
			<div id="titlediv">
				<div id="titlewrap">
					<input id="title" type="text" size="30" name="name" value="<?php echo esc_attr( $role->name ); ?>">
				</div>
				<div class="inside">
					<div id="edit-slug-box">
						<strong>Slug:</strong>
						<span id="sample-slug"><span id="editable-role-name" title="Click to edit this slug"><?php echo $role->slug; ?></span></span>
						<span id="edit-slug-buttons"><a href="#title" class="edit-slug button hide-if-no-js">Edit</a></span>
					</div>
				</div>
			</div>
			<div class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span>Capabilities</span></h3>
					<div class="inside">
						<?php if ( 'administrator' != $role->slug ) : ?>
						<table class="wb-form-table">
							<thead>
								<tr>
									<?php foreach ( $builtin_caps as $group_name => $caps ) :
										$group_id = wordbench_sanitize( 'cap_' . $group_name );
									?>
									<th>
										<input id="<?php echo $group_id; ?>" type="checkbox" class="cap_group">
										<label for="<?php echo $group_id; ?>"><?php echo $group_name; ?></label>
										<?php if ( 'cap_levels' == $group_id ) : ?>
										<sup>*</sup>
										<?php endif; ?>
									</th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php for ( $i = 0, $n = $max_rows; $i < $n; $i++) : ?>
								<tr>
									<?php foreach ( $builtin_caps as $group_name => $caps ) : ?>
									<td>
										<?php if ( isset( $caps[ $i ] ) ) :
											$cap_id    = esc_attr( 'cap_' . $caps[ $i ] );
											$cap_name  = esc_attr( 'cap[' . $caps[ $i ] . ']' );
											$cap_class = wordbench_sanitize( 'cap_' . $group_name );
											$cap_label = wordbench_labelize( $caps[ $i ] );
											$checked   = $role->has_cap( $caps[ $i ] ) ? ' checked="checked"' : '';
										?>
										<input id="<?php echo $cap_id; ?>" type="checkbox" name="<?php echo $cap_name; ?>"
											class="<?php echo $cap_class; ?>"<?php echo $checked; ?>>
										<label for="<?php echo $cap_id; ?>"><?php echo $cap_label; ?></label>
										<?php endif;?>
									</td>
									<?php endforeach; ?>
								</tr>
								<?php endfor; ?>
							</tbody>
						</table>
						<div>
							<sup>*</sup>
							<em>The use of Levels is deprecated and should be replaced with specific capabilities.</em>
						</div>
						<?php else : ?>
						<div><em>Administrator capabilities are not editable.</em></div>
						<?php endif; ?>
					</div>
				</div>
				<div class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span>Post Types</span></h3>
					<div class="inside">
						<?php if ( 'administrator' != $role->slug ) : ?>
						<table class="wb-form-table">
							<thead>
								<tr>
									<?php foreach ( $post_type_caps as $post_type => $caps ) :
										$group_id    = esc_attr( 'cap_' . $post_type );
										$group_label = wordbench_labelize( $post_type );
									?>
									<th>
										<input id="<?php echo $group_id; ?>" type="checkbox" class="cap_group">
										<label for="<?php echo $group_id; ?>"><?php echo $group_label; ?></label>
									</th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $post_type_caps['post'] as $i => $cap ) : ?>
								<tr>
									<?php foreach ( $post_type_caps as $post_type => $caps ) : ?>
									<td>
										<?php if ( isset( $caps[ $i ] ) ) :
											$cap_id    = esc_attr( 'cap_' . $caps[ $i ] );
											$cap_name  = esc_attr( 'cap[' . $caps[ $i ] . ']' );
											$cap_class = wordbench_sanitize( 'cap_' . $post_type );
											$cap_label = wordbench_labelize( $caps[ $i ] );
											$checked   = $role->has_cap( $caps[ $i ] ) ? ' checked="checked"' : '';
										?>
										<input id="<?php echo $cap_id; ?>" type="checkbox" name="<?php echo $cap_name; ?>"
											class="<?php echo $cap_class; ?>"<?php echo $checked; ?>>
										<label for="<?php echo $cap_id; ?>"><?php echo $cap_label; ?></label>
										<?php endif;?>
									</td>
									<?php endforeach; ?>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php else : ?>
						<div><em>Administrator capabilities are not editable.</em></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<p class="submit">
				<input type="submit" value="Save Role" class="button-primary">
			</p>
		</div>
	</form>
</div>
<?php
}

/**
 * Allows for assigning user to multiple roles
 * Replaces role drop-down in user profile editor with checkboxes
 * Requires javascript to be enabled
 */
function wordbench_capabilities_personal_options( $user ) {
	global $wpdb, $wp_roles;

	$key = $wpdb->prefix . 'capabilities';

	// if ( current_user_can( 'edit_users' ) ) {
		$roles = get_user_meta( $user->ID, $key, true );
?>
<div class="hide-if-no-js">
	<tr id="xroles-row">
		<th scope="row">Roles</th>
		<td>
			<ul style="margin: 0px;">
				<?php foreach ( $wp_roles->get_names() as $role_slug => $role_name ) : ?>
				<li>
					<input id="xroles_<?php echo $role_slug; ?>" type="checkbox" name="xroles[<?php echo $role_slug; ?>]" value="1"
						<?php echo isset( $roles[ $role_slug ] ) ? ' checked="checked"' : ''; ?>>
					<label for="xroles_<?php echo $role_slug; ?>"><?php echo $role_name; ?></label>
				</li>
				<?php endforeach; ?>
			</ul>
		</td>
	</tr>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var source = $('#xroles-row');
		var target = $('table.form-table tr').has('select[name="role"]');

		// target.find('th label').text('Primary Role');
		target.after(source);
		target.hide();
	});
</script>
<?php
	// }
}

/**
 * Replaces single role with all checked roles when user profile is updated
 */
function wordbench_capabilities_profile_update( $user_id ) {
	global $wpdb;

	if ( is_array( @$_REQUEST['xroles'] ) ) {
		$key = $wpdb->prefix . 'capabilities';
		update_user_meta( $user_id, $key, $_REQUEST['xroles'] );
	}
}

/**
 * Defines collection of non-post-related capabilities used by WP
 */
function wordbench_capabilities_builtin() {
	$caps = array(
		'Content' => array(
			'moderate_comments',
			'manage_categories',
			'manage_links',
			'unfiltered_html',
			'unfiltered_upload',
			'upload_files',
			'edit_files',
			'import',
			'export'
		),
		'Themes' => array(
			'switch_themes',
			'edit_theme_options',
			'install_themes',
			'update_themes',
			'edit_themes',
			'delete_themes'
		),
		'Plugins' => array(
			'activate_plugins',
			'install_plugins',
			'update_plugins',
			'edit_plugins',
			'delete_plugins'
		),
		'Users' => array(
			'list_users',
			'edit_users',
			'promote_users',
			'create_users',
			'delete_users',
			'add_users',
			'remove_users'
		),
		'Admin' => array(
			'update_core',
			'edit_dashboard',
			'manage_options'
		),
		'Levels' => array(
			'level_0',
			'level_1',
			'level_2',
			'level_3',
			'level_4',
			'level_5',
			'level_6',
			'level_7',
			'level_8',
			'level_9',
			'level_10'
		)
	);

	return $caps;
}

/**
 * Defines post-type-related capabilities for current post types
 */
function wordbench_capabilities_post_types() {
	$caps = array(
		'post' => (array) get_post_type_object( 'post' )->cap,
		'page' => (array) get_post_type_object( 'page' )->cap
	);

	foreach ( WB_Post_Type::fetch_all() as $post_type ) {
		$name = $post_type->get_name();
		$caps[ $name ] = (array) get_post_type_object( $name )->cap;
	}

	$hidden_caps = array( 'read', 'read_post', 'edit_post', 'delete_post' );

	foreach ( $caps as $post_type => $post_type_caps ) {
		foreach ( $post_type_caps as $cap_slug ) {
			if ( in_array( $cap_slug, $hidden_caps ) ) {
				unset( $caps[ $post_type ][ $cap_slug ] );
			}
		}
	}

	return $caps;
}

/**
 * Defines collection of default roles used by WP
 */
function wordbench_capabilities_builtin_roles() {
	return array(
		'administrator' => 'Administrator',
		'editor'        => 'Editor',
		'author'        => 'Author',
		'contributor'   => 'Contributor',
		'subscriber'    => 'Subscriber'
	);
}

/**
 * List table for user roles
 */
class WB_User_Role_List_Table extends WP_List_Table {
	static $_menu_slug = 'capabilities';

	function __construct() {
		parent::__construct( array(
			'singular' => 'role',
			'plural'   => 'roles'
		) );
	}

	function display() {
		$this->process_bulk_action();
		$this->prepare_items();

		parent::display();
	}

	function prepare_items() {
		global $wp_roles;

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$builtin = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		$this->_column_headers = array( $columns, array(), $sortable );

		$this->items = array();

		$builtin = wordbench_capabilities_builtin_roles();

		foreach ( $wp_roles->get_names() as $slug => $name ) {
			$role = $wp_roles->get_role( $slug );

			$this->items[] = array(
				'slug'    => $slug,
				'name'    => $name,
				'caps'    => $role->capabilities,
				'builtin' => isset( $builtin[ $slug ] )
			);
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
			'cb'   => '<input type="checkbox">',
			'name' => 'Name',
			'slug' => 'Slug',
			'caps' => 'Capabilities'
		);
	}

	function get_sortable_columns() {
		return array(
			array( 'name', true ),
			array( 'slug', true )
		);
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="role[%s]" value="%s">',
			$item['slug'],
			esc_attr( $item['name'] )
		);
	}

	function column_name( $item ) {
		$refurl = sprintf( 'users.php?page=%s',
			self::$_menu_slug
		);

		$editurl = sprintf( '%s&slug=%s&view=edit', $refurl, $item['slug'] );
		$delurl  = sprintf( '%s&slug=%s&action=delete', $refurl, $item['slug'] );

		if ( 'administrator' != $item['slug'] ) {
			$actions['edit'] = sprintf( '<a href="%s">Edit</a>', $editurl );
		}

		if ( ! $item['builtin'] ) {
			$actions['delete'] = sprintf(
				'<a href="%s&_wp_http_referer=%s">Delete</a>',
				wp_nonce_url( $delurl, 'single-user-role' ),
				urlencode( $refurl )
			);
		}

		return sprintf( '<a href="%s">%s</a> %s',
			'administrator' == $item['slug'] ? '#' : $editurl,
			$item['name'],
			$this->row_actions( $actions )
		);
	}

	function column_slug( $item ) {
		return $item['slug'];
	}

	function column_caps( $item ) {
		$html = '<ul>';

		foreach ( $item['caps'] as $cap => $grant ) {
			if ( ! $grant ) unset( $item['caps'][ $cap ] );
		}

		$caps = array_slice( $item['caps'], 0, 3 );

		$num_caps = count( $item['caps'] );

		foreach ( $caps as $cap => $grant ) {
			if ( $grant ) {
				$html .= sprintf( '<li>%s</li>', wordbench_labelize( $cap ) );
			}
		}

		if ( 3 < $num_caps ) {
			$html .= sprintf( '<li><em>%d More</em></li>', $num_caps - 3 );
		}

		$html .= '</ul>';

		return $html;
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
