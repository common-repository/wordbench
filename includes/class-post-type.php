<?php

class WB_Post_Type {
	private static $_post_types = array();

	private static $_static_labels = array(
		'name'               => array(
			'descrip' => 'Caption used for this type',
			'default' => '%P'
		),
		'menu_name'          => array(
			'descrip' => 'Caption used for this type in admin menu',
			'default' => '%P'
		),
		'singular_name'      => array(
			'descrip' => 'Caption used for a single item of this type',
			'default' => '%S'
		),
		'add_new'            => array(
			'descrip' => 'Caption used for adding a new item',
			'default' => 'Add New'
		),
		'add_new_item'       => array(
			'descrip' => 'Caption used for adding a new item of this type',
			'default' => 'Add New %S'
		),
		'new_item'           => array(
			'descrip' => 'Caption used for a new item of this type',
			'default' => 'New %S'
		),
		'edit_item'          => array(
			'descrip' => 'Caption used for editing a single item',
			'default' => 'Edit %S'
		),
		'view_item'          => array(
			'descrip' => 'Caption used for viewing a single item',
			'default' => 'View %S'
		),
		'all_items'          => array(
			'descrip' => 'Caption used for all items of this type',
			'default' => 'All %P'
		),
		'search_items'       => array(
			'descrip' => 'Caption used for search box',
			'default' => 'Search %P'
		),
		'not_found'          => array(
			'descrip' => 'Caption used when no search results are found',
			'default' => 'No %p found'
		),
		'not_found_in_trash' => array(
			'descrip' => 'Caption used when no search results are found in the trash',
			'default' => 'No %p found in trash'
		),
		'parent_item_colon'  => array(
			'descrip' => 'Delimiter used to separate item titles the breadcrumb',
			'default' => 'Parent %S:'
		)
	);

	private static $_static_settings = array(
		'publicly_queryable'  => array(
			'descrip' => 'This type can be queried from the front end',
			'default' => true
		),
		'exclude_from_search' => array(
			'descrip' => 'This type will be excluded from search results',
			'default' => false
		),
		'show_ui'             => array(
			'descrip' => 'This type will have an admin interface',
			'default' => true
		),
		'show_in_menu'        => array(
			'descrip' => 'This type will appear in the admin menu',
			'default' => true
		),
		'show_in_nav_menus'   => array(
			'descrip' => 'This type can be used in nav menus',
			'default' => true
		),
		'hierarchical'        => array(
			'descrip' => 'This type is hierarchical',
			'default' => false
		),
		'has_archive'         => array(
			'descrip' => 'This type has an archive view on the front end',
			'default' => true
		),
		'can_export'          => array(
			'descrip' => 'This type can be exported with the admin tools',
			'default' => false
		)
	);

	private static $_static_supports = array(
		'title'           => array(
			'descrip' => 'Items of this type will have titles',
			'default' => true
		),
		'editor'          => array(
			'descrip' => 'Items of this type will have editable content',
			'default' => true
		),
		'author'          => array(
			'descrip' => 'Items of this type will be associated with an author',
			'default' => true
		),
		'thumbnail'       => array(
			'descrip' => 'Items of this type can have featured images',
			'default' => true
		),
		'excerpt'         => array(
			'descrip' => 'Items of this type can have editable excerpts',
			'default' => true
		),
		'trackbacks'      => array(
			'descrip' => 'Items of this type will support trackback/pingback links',
			'default' => true
		),
		'custom-fields'   => array(
			'descrip' => 'Items of this type can have custom fields added to them',
			'default' => true
		),
		'comments'        => array(
			'descrip' => 'Items of this type can be commented on by users',
			'default' => true
		),
		'revisions'       => array(
			'descrip' => 'Items of this type will have revision control enabled',
			'default' => true
		),
		'page-attributes' => array(
			'descrip' => 'Items of this type can be assigned a parent and order',
			'default' => false
		),
		'post-formats'    => array(
			'descrip' => 'Items of this type can be displayed in various formats',
			'default' => true
		)
	);

	public static function get_static_labels() {
		return self::$_static_labels;
	}

	public static function get_static_settings() {
		return self::$_static_settings;
	}

	public static function get_static_supports() {
		return self::$_static_supports;
	}

	public static function get_default_caps() {
		global $wp_roles;

		if ( empty( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$roles = $wp_roles->get_names();
		$caps  = (array) get_post_type_object( 'post' )->cap;

		$default_caps = array();

		foreach ( array_keys( $roles ) as $role_key ) {
			$role = get_role( $role_key );

			foreach ( array_keys( $caps ) as $cap_key ) {
				$default_caps[ $role_key ][ $cap_key ] = $role->has_cap( $cap_key );
			}
		}

		return $default_caps;
	}

	public static function get_default_settings() {
		$settings = array();

		foreach ( self::$_static_settings as $key => $setting ) {
			$settings[ $key ] = $setting['default'];
		}

		return $settings;
	}

	public static function get_default_supports() {
		$supports = array();

		foreach ( self::$_static_supports as $key => $support ) {
			if ( $support['default'] ) {
				$supports[] = $key;
			}
		}

		return $supports;
	}

	public static function register( $post = null ) {
		if ( is_null( $post ) ) {
			$post = object;
		}

		if ( isset( self::$_post_types[ $post->post_name ] ) ) {
			return self::$_post_types[ $post->post_name ];
		}

		$post_type = new WB_Post_Type( $post );

		self::$_post_types[ $post->post_name ] = $post_type;

		$name = wordbench_sanitize( $post_type->_name );

		$args = $post_type->get_settings();
		$args['labels']          = $post_type->get_labels();
		$args['description']     = $post_type->get_description();
		$args['supports']        = $post_type->get_supports();
		$args['taxonomies']      = $post_type->get_taxonomies();
		//$args['capability_type'] = $post_type->get_name();
		$args['map_meta_cap']    = true;
		$args['public']          = true;
		$args['rewrite']         = true;
		$args['query_var']       = true;

		register_post_type( $name, $args );

		$wp_post_type = get_post_type_object( $name );

		foreach ( $post_type->get_caps() as $role_key => $caps ) {
			$role = get_role( $role_key );

			foreach ( $caps as $cap_key => $granted ) {
				$role->add_cap( $wp_post_type->cap->$cap_key, $granted );
			}
		}

		// TODO remove this callback once a better solution is available
		foreach ( $wp_post_type->taxonomies as $tax_name ) {
			if ( ! taxonomy_exists( $tax_name ) ) {
				add_action( 'registered_taxonomy', function( $taxonomy ) use ( $name, $tax_name ) {
					if ( $taxonomy == $tax_name ) {
						register_taxonomy_for_object_type( $tax_name, $name );
					}
				} );
			}
		}

		return $post_type;
	}

	public static function fetch( $name ) {
		if ( isset( self::$_post_types[ $name ] ) ) {
			return self::$_post_types[ $name ];
		}

		return null;
	}

	public static function fetch_all() {
		return self::$_post_types;
	}

	private $_parent = null;

	private $_name    = null;
	private $_title   = null;
	private $_descrip = null;

	private $_caps       = array();
	private $_labels     = array();
	private $_fields     = array();
	private $_settings   = array();
	private $_supports   = array();
	private $_taxonomies = array();

	private function __construct( $post ) {
		if ( $post->post_parent > 0 ) {
			if ( $parent = get_post( $post->post_parent ) ) {
				$this->_parent = new WB_Post_Type( $parent );
			}
		}

		$this->_name    = $post->post_name;
		$this->_title   = $post->post_title;
		$this->_descrip = $post->post_content;

		$this->_caps       = get_post_meta( $post->ID, '_post_type_caps',       true );
		$this->_labels     = get_post_meta( $post->ID, '_post_type_labels',     true );
		$this->_fields     = get_post_meta( $post->ID, '_post_type_fields',     true );
		$this->_settings   = get_post_meta( $post->ID, '_post_type_settings',   true );
		$this->_supports   = get_post_meta( $post->ID, '_post_type_supports',   true );
		$this->_taxonomies = get_post_meta( $post->ID, '_post_type_taxonomies', true );

		if ( empty( $this->_caps ) ) {
			$this->_caps = self::get_default_caps();
		}

		if ( empty( $this->_labels ) ) {
			$this->_labels = array( 'name' => $post->post_title );
		}

		if ( empty( $this->_fields ) ) {
			$this->_fields = array();
		}

		if ( empty( $this->_settings ) ) {
			$this->_settings = wp_parse_args( $this->_settings, self::get_default_settings() );
		}

		if ( empty( $this->_supports ) ) {
			$this->_supports = array();
		}

		if ( empty( $this->_taxonomies ) ) {
			$this->_taxonomies = array();
		}

		foreach ( array_keys( $this->_settings ) as $key ) {
			$this->_settings[ $key ] = (bool) $this->_settings[ $key ];
		}
	}

	public function get_name() {
		return str_replace( '-', '_', $this->_name );
	}

	public function get_title() {
		return $this->_title;
	}

	public function get_description() {
		return $this->_descrip;
	}

	public function get_caps() {
		return $this->_caps;
	}

	public function get_label( $key = 'name' ) {
		$obj = get_post_type_object( $this->_name );

		return $obj->labels->$key;
	}

	public function get_labels( $merge = true ) {
		if ( is_object( $this->_parent ) && $merge ) {
			return array_merge( $this->_parent->get_labels(), $this->_labels );
		}

		return $this->_labels;
	}

	public function get_fields( $merge = true ) {
		if ( is_object( $this->_parent ) && $merge ) {
			return array_merge( $this->_fields, $this->_parent->get_fields() );
		}

		return $this->_fields;
	}

	public function get_settings( $merge = true ) {
		if ( is_object( $this->_parent ) && $merge ) {
			return array_merge( $this->_settings, $this->_parent->get_settings() );
		}

		return $this->_settings;
	}

	public function get_supports( $merge = true ) {
		$supports = array();

		foreach ( $this->_supports as $support => $enabled ) {
			if ( $enabled ) {
				$supports[] = $support;
			}
		}

		if ( is_object( $this->_parent ) && $merge ) {
			$supports = array_merge( $supports, $this->_parent->get_supports() );
		}

		return $supports;
	}

	public function get_taxonomies() {
		return $this->_taxonomies;
	}
}
