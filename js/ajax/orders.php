<?php

    require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    global $wpdb;

    $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
    $dateTo = date("Y-m-d", strtotime($_POST['dateTo']));

    $dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
    $dateFromSQL = $dateFromSQL . " 20:45:01";
    $dateToSQL = $dateTo . " 20:45:00";

    $orders = $wpdb->get_col("SELECT ID
                                FROM $wpdb->posts
                                WHERE post_status = 'wc-completed'
                                AND post_type = 'shop_order'
                                AND post_date
                                    BETWEEN '$dateFromSQL'
                                        AND '$dateToSQL'
                                ", 0);
                                // print_r($orders);

        if ($orders) {
            $message = $row = '';
            $columns = array("Order", "Date", "Customer", "State", "Total", "Tax", "Actions");
                $message .= "<table class='widefat fixed striped exportable'>";
                $message .= "<thead>";
                $message .= "<tr>";
                foreach ($columns as $column) {
                    $message .= "<th>$column</th>";
                }
                $message .= "</tr>";
                $message .= "</thead>";
                $message .= "<tbody>";


				$total_item_tax = $total_order_cost = $total_orders = $row = 0;

				foreach ($orders as $order_id) {
                    $subscription_type = '';
                    $subscription_type = get_post_meta($order_id, 'subscription_type', true);
                    // For some unknown reason, the continue will stop wholesale orders. My best guess is that "continue" means, skip over this iteration, and start a new one
                    if ($subscription_type == "Wholesale") continue;

					$_order = new WC_Order($order_id);

                    if ( number_format($_order->order_total, 2, '.', ',') > 8.00) {
                        $total_orders++;

                        $total_order_cost += $_order->order_total;
                        $message .= "<tr><td>";
                        $message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>$_order->id</a>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "" . date("m/d/Y", strtotime($_order->order_date)) . "";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>" . $_order->billing_first_name . " " . $_order->billing_last_name . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>" . strtoupper($_order->shipping_state) . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>$" . number_format($_order->order_total, 2, '.', ',') . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>$" . number_format($_order->order_tax, 2, '.', ',') . "</p>";
                        $message .= "</td>";

						$message .= "<td class='action' id='$_order->id'>";
						$message .= "<button class='actions button-primary'>Items</button>";
						$message .= "</td>";

                        $order_items = $_order->get_items($type = 'line_item');
                        foreach ($order_items as $k => $v ) {
                            if ($v['line_tax'] > 0) {
                                $total_item_tax += number_format(round($v['line_tax'], 2, PHP_ROUND_HALF_UP), 2,'.', ',');
                            }
                        }

                    $row+=1;

					}
                }

                $total_shipping = number_format($total_orders * 8.00, 2, '.', ',');

                $message .= "</tr>";
				$message .= "<tfoot>";

				$message .= "<tr>";
				$message .= "<td>";
                $message .= "<h1>Total Orders: </h1>";
                $message .= "</td>";
                $message .= "<td>";
                $message .= "<h1>$total_orders</h1>";
                $message .= "<td>";
                $message .= "</tr>";

                $message .= "<tr>";
                $message .= "<td>";
                $message .= "<h1>Total:</h1>";
                $message .= "</td>";
                $message .= "<td>";
                $message .= "<h1>$" . $total_order_cost . "</h1>";
                $message .= "</td>";
                $message .= "</tr>";

                $message .= "<tr>";
                $message .= "<td>";
                $message .= "<h1>Total Shipping:</h1>";
                $message .= "</td>";
                $message .= "<td>";
                $message .= "<h1> $" . $total_shipping . "</h1>";
                $message .= "</td>";
                $message .= "</tr>";

                $minus_shipping = ($total_order_cost - $total_shipping);

                $message .= "<tr>";
                $message .= "<td>";
                $message .= "<h1>Minus Shipping:</h1>";
                $message .= "</td>";
                $message .= "<td>";
                $message .= "<h1> $" . $minus_shipping . "</h1>";
                $message .= "</td>";
                $message .= "</tr>";

				$message .= "</tfoot>";
				$message .= "</tbody>";
				$message .= "</table>";


            }else {
                echo "No Orders for selected date range";
            }

            echo $message;
?>
