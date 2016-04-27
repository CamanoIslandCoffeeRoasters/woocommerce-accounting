<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );

global $wpdb, $woocommerce;

date_default_timezone_set('America/Los_Angeles');

	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom']));
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo']));

	$dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
	$dateFromSQL = $dateFromSQL . " 20:45:01";
	$dateToSQL = $dateTo . " 20:45:00";

			$total_dollars = $row = $in_state_total = $out_state_total = $in_state_refunds = $out_state_refunds = $total_refunds = 0;

			$message = '';

			$_order = new WC_Order();


			$args = array(
			            'posts_per_page' => -1,
			            'post_type' => 'shop_order',
			            'post_status' => 'wc-completed',
			            'meta_query' => array(
			                'relation' => 'AND',
			                array(
			                    'key' => '_order_tax',
			                    'value' => '0.00',
			                    'compare'	=> '>'
			                ),
			                array(
			                    'key' => '_paid_date',
			                    'value' => array($dateFromSQL, $dateToSQL),
			                    'compare'	=> 'BETWEEN'
			                )
			            )
			        );
			// Select taxable orders
			$taxable_orders = get_posts($args);

			// Select all orders
               $total_sales_orders = $wpdb->get_col("SELECT meta.post_id
                                                         FROM {$wpdb->posts} posts
                                                         LEFT JOIN {$wpdb->postmeta} meta
                                                              ON ((posts.ID = meta.post_id)
															  AND (meta.meta_key = '_paid_date'))
                                                         WHERE posts.post_type = 'shop_order'
                                                         AND posts.post_status = 'wc-completed'
                                                         AND meta.meta_value
                                                             BETWEEN '$dateFromSQL'
                                                                 AND '$dateToSQL'
                                                         ORDER BY meta.post_id DESC",0);


			foreach ($total_sales_orders as $k => $v) {

			  $_order->get_order($v);

			  		// Sum totals for sales in Washington State
				  if (strtoupper($_order->shipping_state) == "WA") {
						$in_state_total += $_order->order_total;

				  // Sum totals for sales in states besides Washington
					} else {
						  $out_state_total += $_order->order_total;
					}
				$total_dollars += $_order->order_total;
			}
?>

		<?php

			$in_state_tax = $wpdb->get_var("SELECT SUM(itemmeta.meta_value)
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta
													ON ((posts.ID = meta.post_id) AND (meta.meta_key = '_shipping_state') AND (meta.meta_value = 'WA'))
												LEFT JOIN {$wpdb->postmeta} meta1
													ON ((posts.ID = meta1.post_id) AND (meta1.meta_key = '_paid_date'))
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
													ON ((posts.ID = items.order_id) AND (items.order_item_type = 'tax'))
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta
													ON ((items.order_item_id = itemmeta.order_item_id) AND (itemmeta.meta_key = 'tax_amount') AND (itemmeta.meta_value > 0))
												WHERE meta1.meta_value
													BETWEEN '$dateFromSQL'
														AND '$dateToSQL'
												AND posts.post_type = 'shop_order'
												AND posts.post_status = 'wc-completed'
											");
			$out_state_tax = $wpdb->get_var("SELECT SUM(itemmeta.meta_value)
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta
													ON posts.ID = meta.post_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta
													ON items.order_item_id = itemmeta.order_item_id
												WHERE posts.post_date
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
												AND posts.post_type = 'shop_order'
												AND posts.post_status = 'wc-completed'
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value != 'WA'))
												AND items.order_item_type = 'tax'
												AND itemmeta.meta_key = 'tax_amount'
												AND itemmeta.meta_value > 0
											");

			// Select all orders
			$refunded_orders = $wpdb->get_col("SELECT DISTINCT(ID) FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id
												WHERE posts.post_modified
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
												AND posts.post_status = 'wc-refunded'
												AND ((meta.meta_key = '_order_total') AND (meta.meta_value > 0))",0);



			foreach ($refunded_orders as $r => $o) {
			$_order = new WC_Order();
			  $_order->get_order($o);

			  		// Sum totals for sales in Washington State
				  if (strtoupper($_order->shipping_state) == "WA") {
						$in_state_refunds += $_order->order_total;

				  // Sum totals for sales in states besides Washington
					} else {
						  $out_state_refunds += $_order->order_total;
					}
				$total_refunds += $_order->order_total;
			}



			$in_state_total = number_format($in_state_total, 2,'.', ',');
			$out_state_total = number_format($out_state_total, 2,'.', ',');
			$total_dollars = number_format($total_dollars, 2,'.',',');

			$in_state_refunds = number_format($in_state_refunds, 2,'.', ',');
			$out_state_refunds = number_format($out_state_refunds, 2,'.', ',');
			$total_refunds = number_format($total_refunds, 2,'.', ',');

			$in_state_tax = number_format($in_state_tax, 2,'.', ',');
			$out_state_tax = number_format($out_state_tax, 2,'.', ',');
			$total_tax = number_format($in_state_tax + $out_state_tax, 2,'.', ',')
		?>

		<?php $columns = array("In-State", "Out-of-State", "Total"); ?>
		<?php $rows = array("Total Dollars" =>  array($in_state_total, $out_state_total, $total_dollars),
					  	    "Refunds" 		=>	array($in_state_refunds, $out_state_refunds, $total_refunds),
					  	    "Sales Tax"		=> 	array($in_state_tax, $out_state_tax, $total_tax)); ?>

		<?php if ($total_dollars) : ?>
			<div>
				<h1>Dollars</h1>
				<table class="widefat fixed exportable">
					<thead>
						<tr>
							<th><b><?php echo count($total_sales_orders) . " Orders";?></b></th>
						<?php foreach ($columns as $column) : ?>
							<th class="column"> <?php echo $column ?> </th>
						<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php $counter = 0; foreach ($rows as $row => $values) : ?>
							<?php if ($counter % 2 == 0 ) {
                              	echo  "<tr valign=\"center\" class=\"alternate\">";
                              }
                              else {
                              	echo "<tr>";
                              } ?>
							<td><?php echo $row ?></td>
							<?php foreach ($values as $value) : ?>
							<td><?php echo $value ?></td>
							<?php endforeach; ?>
							</tr>

						<?php $counter++; endforeach; ?>
					</tbody>
				</table>
			</div>

		<?php endif;

		if ($taxable_orders) {
			$message .= "<hr />";
			$message .= "<h1>Taxable Orders</h1>";
            $columns = array("Order", "Date", "Customer", "State", "Total", "Tax", "Actions");
            $message .= "<table id='table' class='widefat fixed striped exportable'>";
            $message .= "<thead>";
            $message .= "<tr>";
            foreach ($columns as $column) {
                $message .= "<th>$column</th>";
            }
            $message .= "</tr>";
            $message .= "</thead>";
            $message .= "<tbody>";

			$total_item_tax = 0;
			foreach ($taxable_orders as $order) {
				$_order = new WC_Order($order->ID);
				$message .= "<tr><td>";
				$message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>$_order->id</a>";
				$message .= "</td>";

				$message .= "<td>";
				$message .= date("m/d/Y", strtotime($_order->order_date));
				$message .= "</td>";

				$message .= "<td>";
				$message .= "<p>" . ucwords($_order->billing_first_name . " " . $_order->billing_last_name) . "</p>";
				$message .= "</td>";

				$message .= "<td>";
				$message .= "<p>" . strtoupper($_order->shipping_state) . "</p>";
				$message .= "</td>";

				$message .= "<td>";
				$message .= "<p>$" . number_format($_order->order_total, 2, '.', ',') . "</p>";
				$message .= "</td>";

				$message .= "<td>";
				if ($_order->order_tax > 0) {
				  $total_item_tax += (float) $_order->order_tax;
				  $message .= "$" . number_format($_order->order_tax, 2);
				}
				$message .= "</td>";

				$message .= "<td class='action' id='$_order->id'>";
				$message .= "<button class='actions button-primary'>Items</button>";
				$message .= "</td>";
				$message .= "</tr>";

			}

			$message .= "<tr>";
			$message .= "<td colspan='7'>";
			$message .= "<h1>Total tax: $". number_format(round($total_item_tax, 2, PHP_ROUND_HALF_DOWN), 2) . "</h1>";
			$message .= "</td>";
			$message .= "</tr>";
			$message .= "</tbody>";
			$message .= "</table>";
        }

	echo $message;
?>
