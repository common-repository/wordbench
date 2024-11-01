<?php
/*
Plugin Name: WordBench
Plugin URI: http://rubberchickenfarm.com/wordbench/
Author: J Andrew Scott
Author URI: http://rubberchickenfarm.com/
Description: WordBench provides admin user interfaces for managing post types,
	post formats, taxonomies, and roles/capabilities.
Version: 0.9
License: GPLv2 or later
*/

/*
Copyright (C) 2012  J Andrew Scott

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

define( 'WORDBENCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'WORDBENCH_INC', WORDBENCH_PATH . 'includes/' );

require_once WORDBENCH_INC . 'functions.php';
require_once WORDBENCH_INC . 'settings.php';
require_once WORDBENCH_INC . 'post-type.php';
require_once WORDBENCH_INC . 'post-format.php';
require_once WORDBENCH_INC . 'taxonomy.php';
require_once WORDBENCH_INC . 'capabilities.php';
require_once WORDBENCH_INC . 'form-element.php';

require_once WORDBENCH_INC . 'class-post-type.php';

// Admin UI Resources
add_action( 'admin_enqueue_scripts', 'wordbench_enqueue_scripts');

// Plugin Settings
add_action( 'admin_init', 'wordbench_settings_admin_init' );
add_action( 'admin_menu', 'wordbench_settings_admin_menu' );

// Post Types
add_action( 'init',           'wordbench_post_type_init' );
add_action( 'add_meta_boxes', 'wordbench_post_type_meta_boxes' );
add_action( 'save_post',      'wordbench_post_type_save_post' );

// Post Formats
add_action( 'init',                 'wordbench_post_format_init' );
add_action( 'admin_menu',           'wordbench_post_format_menu_page' );
add_action( 'save_post',            'wordbench_post_format_save_post' );
add_action( 'post_submitbox_start', 'wordbench_post_format_select' );

// Taxonomies
add_action( 'init',       'wordbench_taxonomy_init' );
add_action( 'admin_menu', 'wordbench_taxonomy_menu_page' );

// Roles & Capabilities
add_action( 'admin_menu',       'wordbench_capabilities_admin_menu' );
add_action( 'personal_options', 'wordbench_capabilities_personal_options' );
add_action( 'profile_update',   'wordbench_capabilities_profile_update' );
