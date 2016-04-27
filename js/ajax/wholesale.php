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
                                            AND meta.meta_value = 'Wholesale'
											ORDER BY posts.post_date ASC", 0);

	        if ($orders) {
	            $message = '';

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
						$total_orders++;

						$weight = $wpdb->get_col("SELECT sum(m2.meta_value * (REPLACE(REPLACE(m1.meta_value, '12oz', '0.75'), 'lb', ''))) as weight
													FROM {$wpdb->prefix}woocommerce_order_items items
													JOIN {$wpdb->prefix}woocommerce_order_itemmeta m1
														ON ((items.order_item_id = m1.order_item_id)
														AND (m1.meta_key = 'pa_pack'))
													JOIN {$wpdb->prefix}woocommerce_order_itemmeta m2
														ON ((items.order_item_id = m2.order_item_id)
														AND (m2.meta_key = '_qty'))
													WHERE items.order_id = '$order_id'");


						$total_order_cost += (float) $_order->order_total;

						$message .= "<tr id='row-{$_order->id}' valign='center'>";

						$message .= "<td>";
						$message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>$_order->id</a>";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "" . date("m/d/Y", strtotime($_order->order_date)) . "";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "<span title='". $_order->billing_company ."'>" . $_order->billing_first_name . " " . $_order->billing_last_name . "</span>";
						$message .= "</td>";

						$message .= "<td>";
						$message .= "" . strtoupper($_order->shipping_state) . "";
						$message .= "</td>";

						$message .= "<td>";
						$message .= array_sum($weight) . ' lbs';
						$message .= "</td>";

						$message .= "<td>";
						$message .= "$" . number_format($_order->order_total, 2, '.', ',');
						if ($_order->order_tax > 0) {
							$total_item_tax += (float) number_format($_order->order_tax, 2);
							$message .= " + tax: $" . number_format($_order->order_tax, 2);
						}
						$message .= "</td>";

						$message .= "<td class='action' id='$_order->id'>";
						$message .= "<button class='actions button-primary'>Items</button>";
						$message .= "</td>";

	                }

	                $message .= "</tr>";
					$message .= "</tbody>";
					$message .= "</table>";

					$totals['Orders']   = $total_orders;
	                $totals['Subtotal'] = $total_order_cost;
	                // $totals['Shipping'] = $total_orders * 8;
	                $totals['Tax'] 		= $total_item_tax;
	                $totals['Total']    = $totals['Subtotal'] - $totals['Shipping'];

	                $totals = array_map(function($number) { return number_format($number,2); }, $totals);

	                $totals_table = '';
	                $totals_table = "<table class='widefat fixed striped'>";
	                $totals_table .= "<thead><tr>";
	                foreach (array_keys($totals) as $key) {
	                    $totals_table .= "<th><b>$key</b></th>";
	                }
	                $totals_table .= "</tr></thead>";
	                $totals_table .= "<tbody><tr>";
	                foreach (array_values($totals) as $key) {
	                    $totals_table .= "<th>$key</th>";
	                }
	                $totals_table .= "</tr></tbody>";
	                $totals_table .= "</table><br />";

				}else {
	                echo "No Orders for selected date range";
	            }
			echo $totals_table . $message;
?>
