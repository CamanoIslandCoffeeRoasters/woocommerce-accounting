<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	
	global $wpdb;
	
	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom']));
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo']));
	
	$total_shipping = $wpdb->get_var("SELECT SUM(meta.meta_value) 
										FROM {$wpdb->posts} posts
										LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
											ON posts.ID = items.order_id
										LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta 
											ON items.order_item_id = meta.order_item_id
										WHERE DATE(posts.post_date) 
											BETWEEN '$dateFrom' 
												AND '$dateTo' 
										AND posts.post_status = 'wc-completed'
										AND items.order_item_type = 'shipping'
										AND meta.meta_key = 'cost'
										AND meta.meta_value > 0
						");
						
						
	$in_state_shipping_costs = $wpdb->get_var("SELECT SUM(itemmeta.meta_value) 
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id 
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta 
													ON items.order_item_id = itemmeta.order_item_id
												WHERE DATE(posts.post_date) 
													BETWEEN '$dateFrom' 
														AND '$dateTo'
												AND posts.post_status = 'wc-completed'
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value = 'WA'))
												AND items.order_item_type = 'shipping'
												AND itemmeta.meta_key = 'cost' 
												AND itemmeta.meta_value > 0
											");
	$out_state_shipping_costs = $wpdb->get_var("SELECT SUM(itemmeta.meta_value) 
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id 
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta 
													ON items.order_item_id = itemmeta.order_item_id
												WHERE DATE(posts.post_date) 
													BETWEEN '$dateFrom' 
														AND '$dateTo'
												AND posts.post_status = 'wc-completed'
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value != 'WA'))
												AND items.order_item_type = 'shipping'
												AND itemmeta.meta_key = 'cost' 
												AND itemmeta.meta_value > 0
											");

		$in_state_shipping_costs = number_format($in_state_shipping_costs, 2,'.', ',');
		$out_state_shipping_costs = number_format($out_state_shipping_costs, 2,'.', ',');
		$total_shipping = number_format($total_shipping, 2,'.', ',');									
											
											
		$columns = array("In-State", "Out-of-State", "Total"); 
		$rows = array("Total Shipping" =>  array($in_state_shipping_costs, $out_state_shipping_costs, $total_shipping));
		?>
		
		<?php if ($total_shipping) : ?>
		<div>
			<h1>Shipping</h1>
			<table class="widefat fixed">
				<thead>
					<tr>
						<th></th>
					<?php foreach ($columns as $column) : ?>
						<th class="column"> <?php echo $column ?> </th>
					<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row => $values) : ?>
					<tr class="row">
						<td><?php echo $row ?></td>
						<?php foreach ($values as $value) : ?>
						<td><?php echo $value ?></td>
						<?php endforeach; ?>
						</tr>
					<?php endforeach; ?> 
				</tbody>
			</table>
		</div>
	<?php endif; ?>