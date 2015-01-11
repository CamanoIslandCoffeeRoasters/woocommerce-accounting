<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	
	global $wpdb;
	
	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom'])) . '<br />';
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo'])) . '<br />';
	
	$total_pounds =  $wpdb->get_var("SELECT SUM(LEFT(meta.meta_value,1)) 
									FROM {$wpdb->posts} posts
									LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
										ON posts.ID = items.order_id
									LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta 
										ON items.order_item_id = meta.order_item_id
									WHERE DATE(posts.post_date) 
										BETWEEN '$dateFrom' 
											AND '$dateTo' 
									AND posts.post_status = 'wc-completed'
									AND meta.meta_key = 'pa_pack'
								");
						
	$free_pounds =  $wpdb->get_var("SELECT COUNT(items.order_id) 
									FROM {$wpdb->posts} posts
									LEFT JOIN {$wpdb->prefix}woocommerce_order_items` items ON posts.ID = items.order_id
									WHERE DATE(posts.post_date) 
										BETWEEN '$dateFrom' 
											AND '$dateTo' 
									AND posts.post_status = 'wc-completed'
									AND items.order_item_name LIKE '%Free Pound%'
								");
						
						
						
		?>
		<div>
			<h2>Total Pounds: <?php echo isset($total_pounds) ? $total_pounds : "Nil" ?></h2>
			<h2>Free Pounds: <?php echo isset($free_pounds) ? $free_pounds : "Nil" ?></h2>
		</div>
