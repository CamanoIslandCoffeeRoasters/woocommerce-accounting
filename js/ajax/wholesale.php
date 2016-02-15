<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );

    global $wpdb;

    $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
    $dateTo = date("Y-m-d", strtotime($_POST['dateTo']));

    $dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
    $dateFromSQL = $dateFromSQL . " 20:45:01";
    $dateToSQL = $dateTo . " 20:45:00";

    $orders = $wpdb->get_col("SELECT posts.ID
                                            FROM $wpdb->posts posts
                                            JOIN $wpdb->postmeta meta ON posts.ID = meta.post_id
                                            WHERE meta.meta_key = 'subscription_type'
                                            AND posts.post_status = 'wc-completed'
                                            AND DATE(posts.post_date)
                                                BETWEEN '$dateFromSQL'
                                                    AND '$dateToSQL'
                                            AND meta.meta_value = 'Wholesale'", 0);

	        if ($orders) {
	            $message = $row = '';

            	$columns = array("Order", "Date", "Customer", "State", "Weight", "Total", "Actions");
                $message .= "<table class='widefat fixed striped exportable'>";
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

						$message .= "<tr id='row-{$_order->id}' valign='center'>";

						$message .= "<td>";
						$message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>$_order->id</a>";
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

					echo $message;
				}
?>
