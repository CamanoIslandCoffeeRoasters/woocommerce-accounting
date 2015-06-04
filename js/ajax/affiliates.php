<?php
	
	require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

	global $wpdb;
	
	$dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
	$dateTo = date("Y-m-d", strtotime($_POST['dateTo']));
	
	$affiliate = $_POST['choose_affiliate'];
	
	$affiliate_orders = $wpdb->get_col("SELECT posts.ID 
											FROM {$wpdb->prefix}posts posts
											JOIN {$wpdb->prefix}postmeta meta ON posts.ID = meta.post_id
											WHERE meta.meta_key = 'subscription_id'
											AND posts.post_status = 'wc-completed' 
											AND DATE(posts.post_date) 
												BETWEEN '$dateFrom' 
													AND '$dateTo'
											AND meta.meta_value IN 
												(SELECT subscription_id 
											     FROM {$wpdb->prefix}subscriptions 
												WHERE source = '$affiliate'
											    )", 0);
		
		if ($affiliate_orders && $affiliate) {
				$message .= "<h1>$affiliate</h1>";
				$message .= "<table class=\"widefat fixed\">";
				$message .= "<thead>";
				$message .= "<tr>";
				$message .= "<th>";
				$message .= "Order #";                        
				$message .= "</th>";                        
				$message .= "<th>";
				$message .= "Date";                        
				$message .= "</th>";                        
				$message .= "<th>";
				$message .= "Customer";                        
				$message .= "</th>";                                    
				$message .= "<th>";
				$message .= "State";                        
				$message .= "</th>";
				$message .= "<th>";
				$message .= "Order Total";                        
				$message .= "</th>";
				$message .= "</tr>";                        
				$message .= "</thead>";
				$message .= "<tbody>";




						$total_item_tax = $total_order_cost = $total_orders = 0;
						foreach ($affiliate_orders as $key => $order){
							$_order = new WC_Order($order);
							
                            if ( number_format($_order->order_total, 2, '.', ',') > 8.00) {
                                $total_orders++;
                            
							  $total_order_cost += $_order->order_total;
								
                              $message .= ($row % 2 == 0) ? "<tr valign=\"center\" class=\"alternate\">" : "<tr>";
                              
                              $message .= "<td><p align=\"center\">";
                              $message .= "#" . $_order->id;
                              $message .= "</p><div style=\"text-align:center;\" class=\"row-actions\"><span><a href=\"" . get_option("siteurl") . "/wp-admin/post.php?post={$_order->id}&action=edit\" target=\"_blank\">View Order</a></span></div></td>";

                              $message .= "<td>";
                              $message .= "<p>" . $_order->order_date . "</p>";
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
                              
                              $orderContent = $_order->get_items($type = 'line_item');
                              foreach ($orderContent as $k => $v ) {
                                  
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
                                $message .= "<td>";
                                $message .= "<td>";
                                $message .= "<h1>$total_orders</h1>";
                                $message .= "<td>";
                                $message .= "</tr>";
                                
                                $message .= "<tr>";
                                $message .= "<td>";
                                $message .= "<h1>Total:</h1>";
                                $message .= "<td>";
                                $message .= "<td>";
                                $message .= "<h1>$" . $total_order_cost . "</h1>";
                                $message .= "<td>";
                                $message .= "</tr>";
                                
                                $message .= "<tr>";
                                $message .= "<td>";
                                $message .= "<h1>Total Shipping:</h1>";
                                $message .= "<td>";
                                $message .= "<td>";
                                $message .= "<h1> $" . $total_shipping . "</h1>";
                                $message .= "<td>";
                                $message .= "</tr>";
                                
                                $minus_shipping = ($total_order_cost - $total_shipping);
                                
                                $message .= "<tr>";
                                $message .= "<td>";
                                $message .= "<h1>Minus Shipping:</h1>";
                                $message .= "<td>";
                                $message .= "<td>";
                                $message .= "<h1> $" . $minus_shipping . "</h1>";
                                $message .= "<td>";
                                $message .= "</tr>";
                                
                                $percentage = number_format($minus_shipping * 0.10, 2, ".", ",");
                                
                                $message .= "<tr>";
                                $message .= "<td>";
                                $message .= "<h1>10%:</h1>";
                                $message .= "<td>";
                                $message .= "<td>";
                                $message .= "<h1> $" . $percentage . "</h1>";
                                $message .= "<td>";
                                $message .= "</tr>";
                                
                                
								$message .= "</tfoot>";
								$message .= "</tbody>";
								$message .= "</table>";

                            }else {
                            	if ($affiliate) {
                            	   echo "No Orders for $affiliate";
                                }else {
                                   echo "No Affiliate Selected";
                            }
                            }


			echo $message;
	 
	 
?>
