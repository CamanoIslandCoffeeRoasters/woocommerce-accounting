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

        if ($orders) {
            $message = '';
            $columns = array("Order", "Date", "Customer", "State", "Total", "Tax", "Actions");
            $message .= "<h1>Orders</h1>";
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
                    $subscription_type = '';
                    $subscription_type = get_post_meta($order_id, 'subscription_type', true);
                    // For some unknown reason, the continue will stop wholesale orders. My best guess is that "continue" means, skip over this iteration, and start a new one
                    if ($subscription_type == "Wholesale") continue;

					$_order = new WC_Order($order_id);

                    if ( number_format((float) $_order->order_total, 2, '.', ',') > 8.00) {
                        $total_orders++;
                        $total_order_cost += (float) $_order->order_total;

                        $message .= "<tr><td>";
                        $message .= "<a href='" . get_option('siteurl') . "/wp-admin/post.php?action=edit&post=$_order->id' target='_blank'>$_order->id</a>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "" . date("m/d/Y", strtotime($_order->order_date)) . "";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>" . ucwords($_order->billing_first_name . " " . $_order->billing_last_name) . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>" . strtoupper($_order->shipping_state) . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        $message .= "<p>$" . (float) number_format($_order->order_total, 2, '.', ',') . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
                        if ((float) $_order->order_tax > 0) {
                            $message .= wc_price(( (float) $_order->order_tax > 0) ? number_format($_order->order_tax, 2, '.', ',') : "");
                            $total_item_tax += (float) number_format($_order->order_tax, 2);
                        }
                        $message .= "</td>";

						$message .= "<td class='action' id='$_order->id'>";
						$message .= "<button class='actions button-primary'>Items</button>";
						$message .= "</td>";

					}
                }

                $message .= "</tr>";
                $message .= "</tbody>";
                $message .= "</table>";

                $totals['Orders']   = $total_orders;
                $totals['Subtotal'] = $total_order_cost;
                $totals['Shipping'] = $total_orders * 8;
                $totals['Tax']      = $total_item_tax;
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
