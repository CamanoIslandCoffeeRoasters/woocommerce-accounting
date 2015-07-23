<?php

	/*
		Plugin Name: Woocommerce Accounting
	 	Description: Customized Reports for Subscription Accounting
	 	Author: tobinfekkes
	 	Author URI: http://tobinfekkes.com
	 	Version: 0.9
	*/
	define( 'WOOCOMMERCE_ACCOUNTING_URL', __FILE__);
	define( 'WOOCOMMERCE_ACCOUNTING_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
	
	function woocommerce_accounting_css_and_js() {
		wp_register_style('woocommerce_accounting_css', plugins_url('css/admin.css',__FILE__ ));
		wp_enqueue_style('woocommerce_accounting_css');
		wp_register_script('woocommerce_accounting_js', plugins_url('js/admin.js',__FILE__ ));
		wp_enqueue_script('woocommerce_accounting_js');
	}

	add_action( 'wp_enqueue_scripts','woocommerce_accounting_css_and_js');


	//call register settings function
	add_action( 'admin_init', 'register_woo_accounting_settings' );
	
	
	function register_woo_accounting_settings() {
		//register our settings
		register_setting( 'woocommerce_accounting_group', 'accounting_affiliates' );
	}

	function Woocommerce_Accounting() {
		add_menu_page('Accounting', 'Accounting', 'manage_options', 'accounting_dashboard', 'accounting_dashboard_callback', 'dashicons-edit');
		add_submenu_page('accounting_dashboard', 'Accounting Options', 'Options', 'manage_options', 'accounting_options', 'accounting_options_callback');
	}
	
	add_action('admin_menu', "Woocommerce_Accounting");
	
	
	function accounting_dashboard_callback() {
		global $wpdb;
          
		include WOOCOMMERCE_ACCOUNTING_PATH . '/admin/dashboard.php';
	}
	
	function accounting_options_callback() {
		global $wpdb;
		
		include WOOCOMMERCE_ACCOUNTING_PATH . '/admin/options.php';
	}
?>