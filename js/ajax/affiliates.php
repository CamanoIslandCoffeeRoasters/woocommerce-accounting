<?php

    require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    global $wpdb;

    $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
    $dateTo = date("Y-m-d", strtotime($_POST['dateTo']));

    $dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
    $dateFromSQL = $dateFromSQL . " 20:45:01";
    $dateToSQL = $dateTo . " 20:45:00";
    $message = '';
    $affiliate = $_POST['choose_affiliate'];

    $affiliate_orders = $wpdb->get_col("SELECT posts.ID
                                            FROM {$wpdb->prefix}posts posts
                                            JOIN {$wpdb->prefix}postmeta meta ON posts.ID = meta.post_id
                                            WHERE meta.meta_key = 'subscription_id'
                                            AND posts.post_status = 'wc-completed'
                                            AND DATE(posts.post_date)
                                                BETWEEN '$dateFromSQL'
                                                    AND '$dateToSQL'
                                            AND meta.meta_value IN
                                                (SELECT subscription_id
                                                 FROM {$wpdb->prefix}subscriptions
                                                 WHERE source = '$affiliate'
                                                )", 0);

        if ($affiliate_orders && $affiliate) {
                $message .= "<h1>$affiliate</h1>";
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


                $total_item_tax = $total_order_cost = $total_orders = 0;
                foreach ($affiliate_orders as $key => $order) {
                    $_order = new WC_Order($order);

                    if ( (float) number_format($_order->order_total, 2, '.', ',') > 8.00) {
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
                        $message .= "<p>$" . number_format($_order->order_total, 2, '.', ',') . "</p>";
                        $message .= "</td>";

                        $message .= "<td>";
						if ($_order->order_tax > 0) {
                            $total_item_tax += (float) number_format($_order->order_tax, 2);
                            $message .= "$" . number_format($_order->order_tax, 2, '.', ',');
                        }
						$message .= "</td>";

                        $message .= "<td class='action' id='$_order->id'>";
						$message .= "<button class='actions button-primary'>Items</button>";
						$message .= "</td>";

                    }
                }

                $total_shipping = number_format($total_orders * 8.00, 2, '.', ',');

                $message .= "</tr>";
				$message .= "</tbody>";
				$message .= "</table>";

                $totals['Orders']   = $total_orders;
                $totals['Subtotal'] = $total_order_cost;
                $totals['Shipping'] = $total_orders * 8;
                $totals['Tax']      = $total_item_tax;
                $totals['Total']    = $totals['Subtotal'] - $totals['Shipping'];
                $totals['Percentage'] = $totals['Total'] * 0.1;

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


                echo $totals_table . $message;
            }else {
                if ($affiliate) {
                   echo "No Orders for $affiliate";
                }else {
                   echo "No Affiliate Selected";
                }
            }

?>
