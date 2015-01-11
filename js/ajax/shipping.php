<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	
	global $wpdb;
	
	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom'])) . '<br />';
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo'])) . '<br />';
	
	$total_shipping = $wpdb->get_var("SELECT SUM(meta.meta_value) 
						FROM {$wpdb->posts} posts
						LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON posts.ID = items.order_id
						LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta on items.order_item_id = meta.order_item_id
						WHERE DATE(posts.post_date) 
							BETWEEN '$dateFrom' 
								AND '$dateTo' 
						AND posts.post_status = 'wc-completed'
						AND items.order_item_type = 'shipping'
						AND meta.meta_key = 'cost'
						AND meta.meta_value > 0
						");
						
?>

		<div>
			<h2>Total Shipping: <?php echo isset($total_shipping) ? $total_shipping : "Nil" ?></h2>
		</div>
