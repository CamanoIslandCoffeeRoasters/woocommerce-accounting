<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );

    global $wpdb;
    $products = $orders = array();
    $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
    $dateTo = date("Y-m-d", strtotime($_POST['dateTo']));

    $dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
    $dateFromSQL = $dateFromSQL . " 20:45:01";
    $dateToSQL = $dateTo . " 20:45:00";
    $merchandise = $_POST['add_item_search'];

    $products = $wpdb->get_results("SELECT items.order_item_name as item, sum(meta.meta_value) as qty
                                    FROM $wpdb->posts posts
                                    JOIN {$wpdb->prefix}woocommerce_order_items items ON posts.ID = items.order_id
                                    JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta
                                    	ON ((items.order_item_id = meta.order_item_id)
                                    	AND (meta.meta_key = '_qty'))
                                    WHERE posts.post_status = 'wc-completed'
                                    AND posts.post_type = 'shop_order'
                                    AND REPLACE(order_item_name, '- ', '') LIKE '%$merchandise%'
                                    AND DATE(posts.post_date)
                                    BETWEEN '$dateFromSQL'
                                    AND '$dateToSQL'
                                    GROUP BY items.order_item_name
                                    ", 0);

    $orders = $wpdb->get_col("SELECT posts.ID
                                FROM $wpdb->posts posts
                                JOIN {$wpdb->prefix}woocommerce_order_items items ON posts.ID = items.order_id
                                WHERE posts.post_status = 'wc-completed'
                                AND posts.post_type = 'shop_order'
                                AND REPLACE(order_item_name, '- ', '') LIKE '%$merchandise%'
                                AND DATE(posts.post_date)
                                    BETWEEN '$dateFromSQL'
                                    AND '$dateToSQL'
                                GROUP BY posts.ID
                                            ", 0);

	        if ($orders && $orders) {
	            $message = $row = '';
                $message .= "<table class='widefat fixed striped'>";
                $message .= "<thead><tr><td>Product</td><td>Quantity</td></tr><tbody>";
                foreach ($products as $product) {
                    $message .= sprintf("<tr><td>%s</td><td>%s</td></tr>", $product->item, $product->qty);
                }
                $message .= "</tbody></table>";
                $message .= "<br /><hr /><br />";

                $columns = array("Order #", "Date", "Customer", "State", "Total", "Tax", "Actions");
                $message .= "<table class='widefat fixed striped'>";
                $message .= "<thead>";
                $message .= "<tr>";
                foreach ($columns as $column) {
                    $message .= "<th>$column</th>";
                }
                $message .= "</tr>";
                $message .= "</thead>";
                $message .= "<tbody>";

					$total_item_tax = $total_order_cost = $total_orders = 0;

					foreach ($orders as $order_id) {
						$_order = new WC_Order($order_id);
						$weight = $wpdb->get_col("SELECT (m2.meta_value * (REPLACE(REPLACE(m1.meta_value, '12oz', '0.75'), 'lb', ''))) as weight
													FROM {$wpdb->prefix}woocommerce_order_itemmeta m1
													JOIN {$wpdb->prefix}woocommerce_order_itemmeta m2
														ON ((m2.order_item_id = m1.order_item_id)
														AND (m2.meta_key = '_qty'))
													WHERE m1.order_item_id
														IN (SELECT order_item_id
													        FROM {$wpdb->prefix}woocommerce_order_items
													        WHERE order_id = '$order_id')
													AND m1.meta_key = 'pa_pack'");
	                    $total_orders++;

						$total_order_cost += $_order->order_total;

						$message .= "<tr id='row-$_order->id' valign=\"center\">";

						$message .= "<td>";
						$message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>#$_order->id</a>";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "" . date("m/d/Y", strtotime($_order->order_date)) . "";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "" . $_order->billing_first_name . " " . $_order->billing_last_name . "";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "" . strtoupper($_order->shipping_state) . "";
						$message .= "</td>";

						$message .= "<td>";
						$message .= array_sum($weight) . ' lbs';
						$message .= "</td>";

						$message .= "<td>";
						$message .= "$" . number_format($_order->order_total, 2, '.', ',');
						if ($_order->order_tax > 0) $message .= " + tax: $" . number_format($_order->order_tax, 2, '.', ',');
						$message .= "</td>";

						$message .= "<td class='action' id='$_order->id'>";
						$message .= "<button class='actions button-primary'>Items</button>";
						$message .= "</td>";

	                    $row+=1;
	                }

	                $total_shipping = number_format($total_orders * 8.00, 2, '.', ',');

	                $message .= "</tr>";
					$message .= "</tbody>";
					$message .= "</table>";

                    print_r($results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subscriptions WHERE subscription_id < 20"));
					echo $message;
				}
?>
