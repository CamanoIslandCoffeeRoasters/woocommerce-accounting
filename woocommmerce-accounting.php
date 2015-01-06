<?php

	/*
		Plugin Name: Woocommerce Accounting
	 	Description: Customized Reports for Subscription Accounting
	 	Author: tobinfekkes
	 	Author URI: http://tobinfekkes.com
	 	Version: 0.1
	*/
	
	

	add_action('admin_menu', "Woocommerce_Accounting");


	function Woocommerce_Accounting() {
		add_menu_page('Accounting', 'Accounting', 'manage_options', 'accounting_dashboard', 'accounting_dashboard', '', $position);
	}
?>