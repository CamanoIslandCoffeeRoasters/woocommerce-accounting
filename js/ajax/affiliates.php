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
											AND DATE(posts.post_date) 
												BETWEEN '$dateFrom' 
													AND '$dateTo'
											AND meta.meta_value IN 
												(SELECT subscription_id 
											     FROM {$wpdb->prefix}subscriptions 
												WHERE source = '$affiliate'
											    )", 0);
		
		if ($affiliate_orders) {
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




							$total_item_tax = $total_order_cost = 0;
							foreach ($affiliate_orders as $key => $order){
								$_order = new WC_Order($order);
								
								$total_order_cost += $_order->order_total;
								
							  if ($row % 2 == 0 ) {  
                              	$message .= "<tr valign=\"center\" class=\"alternate\">";
                              }
                              else {
                              	$message .= "<tr>";
                              }
                              $message .= "<td><h4 align=\"center\">";
                              $message .= "#" . $_order->id;
                              $message .= "</h4><div style=\"text-align:center;\" class=\"row-actions\"><span><a href=\"" . get_option("siteurl") . "/wp-admin/post.php?post={$_order->id}&action=edit\" target=\"_blank\">View Order</a></span></div></td>";

                              $message .= "<td>";
                              $message .= "<h4>" . $_order->order_date . "</h4>";
                              $message .= "</td>";
                              
                              $message .= "<td>";
                              $message .= "<h4>" . $_order->billing_first_name . " " . $_order->billing_last_name . "</h4>";
                              $message .= "</td>";
                              
                              $message .= "<td>";
                              $message .= "<h4>" . $_order->shipping_state . "</h4>";
                              $message .= "</td>";

                              $message .= "<td>";
                              $message .= "<h4>$" . $_order->order_total . "</h4>";
                              $message .= "</td>";                              
                              
                              $orderContent = $_order->get_items($type = 'line_item');
                              foreach ($orderContent as $k => $v ) {
                                  
                                  if ($v['line_tax'] > 0) {
                                  	
                                      $total_item_tax += number_format(round($v['line_tax'], 2, PHP_ROUND_HALF_UP), 2,'.', ',');
                                  }                              
                              }
							$row+=1;

							}

								$message .= "<tfoot>";
								$message .= "<tr>";
								$message .= "<td>";
								$total_item_tax = number_format($total_item_tax, 2, '.', ',');
								$message .= "<h1>Total: $$total_order_cost</h1>";
								//$message .= "<h1>Total tax: $$total_item_tax</h1>";
								$message .= "</td>";
								$message .= "</tr>";
								$message .= "</tfoot>";
								$message .= "</tbody>";
								$message .= "</table>";
                            }else {
                            	if ($affiliate) 
                            	echo "No Orders for $affiliate";
                            }

			echo $message;
	 
	 
?>
