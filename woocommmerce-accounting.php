<?php

	/*
		Plugin Name: Woocommerce Accounting
	 	Description: Customized Reports for Subscription Accounting
	 	Author: tobinfekkes
	 	Author URI: http://tobinfekkes.com
	 	Version: 0.1
	*/
	define( 'WOOCOMMERCE_ACCOUNTING_URL', __FILE__);
	define( 'WOOCOMMERCE_ACCOUNTING_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
	

	add_action('admin_menu', "Woocommerce_Accounting");


	function Woocommerce_Accounting() {
		add_menu_page('Accounting', 'Accounting', 'manage_options', 'accounting_dashboard', 'accounting_dashboard_callback', '', $position);
	}
	
	function accounting_dashboard_callback() {
		include WOOCOMMERCE_ACCOUNTING_PATH . '/templates/dashboard.php';
	}
?>