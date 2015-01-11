<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	
global $wpdb, $woocommerce;

date_default_timezone_set('America/Los_Angeles');	

	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom'])) . '<br />';
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo'])) . '<br />';
			
			
			$dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
			$dateFromSQL = $dateFromSQL . " 20:45:01";
			$dateToSQL = $dateTo . " 20:45:00";
            
			$row = $in_state_total = $out_of_state_total = 0;
			
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
			                    'value' => '0',
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
			$total_sales_orders = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_paid_date' AND meta_value BETWEEN '$dateFromSQL' AND '$dateToSQL' ORDER BY post_id DESC",0);
						 


			foreach ($total_sales_orders as $k => $v) {
			
			  $_order->get_order($v);
			  
			  		// Sum totals for sales in Washington State
				  if (strtoupper($_order->shipping_state) == "WA") {
						$in_state_total += $_order->order_total;
						
				  // Sum totals for sales in states besides Washington	
					} else {
						  $out_of_state_total += $_order->order_total;
					}
			}
						
			$message .= "<div style=\"background:;padding:20px;border-radius:10px;\">";
			$message = "<h1>". get_option('blogname'). "</h1>";	
			$message .= "<h2>In-State Total: $" . number_format($in_state_total, 2,'.', ',') . "</h2>";
			$message .= "<h2>Out-of-State Total: $" . number_format($out_of_state_total, 2,'.', ',') . "</h2></div>";
			$message .= "</div>";
			
			
			
			if ($taxable_orders) {
				$message .= "<hr />";
				$message .= "<h1>Taxable</h1>";
				$message .= "<table class=\"widefat fixed\">";                        
				$message .= "<tbody>";
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
				$message .= "<th>";
				$message .= "Order Items";                        
				$message .= "</th>";
				$message .= "<th>";
				$message .= "";                        
				$message .= "</th>";
				$message .= "</tr>";                        
				$message .= "</thead>";




							
							foreach ($taxable_orders as $order){
									$_order = new WC_Order($order->ID);
								if ($row % 2 == 0 ) {  
                              $message .= "<tr valign=\"center\" class=\"alternate\">";
                              }
                              else {
                                  $message .= "<tr>";
                              }
                              $message .= "<td><h2 align=\"center\">";
                              $message .= "#" . $_order->id;
                              $message .= "</h2><div style=\"text-align:center;\" class=\"row-actions\"><span><a href=\"" . get_option("siteurl") . "/wp-admin/post.php?post={$_order->id}&action=edit\">View Order</a></span></div></td>";

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

                              $message .= "<td colspan=\"2\">";
								$message .= "<table>";
                              	$message .= "<tbody>";
                              
                              
                              
                              $orderContent = $_order->get_items($type ='line_item');
                              foreach ($orderContent as $k => $v ) {
                                  $message .= "<tr>";
                                  $message .= "<td>";
                                  
                                  if ($v['line_tax'] > 0) {
                                      $message .= $v['qty'] ." - " . $v['name'] . " - $" . $v['line_total'] . " + tax: $" . round($v['line_tax'], 2, PHP_ROUND_HALF_UP);
                                  }
                                  
                                  else {
                                  $message .= $v['qty'] ." - " . $v['name'] . " - $" . $v['line_total'];
                                  }
                                  $message .= "</td>";
                                  $message .= "</tr>";
								  
								  $total_tax += round($v['line_tax'], 2, PHP_ROUND_HALF_UP);
                               
                              }

                              $message .= "</tbody>";
                              $message .= "</table>";
								
								$row+=1;

							}

								$message .= "</tr>";
								$message .= "<tr>";
								$message .= "<td>";
								$message .= "<h1>Total tax: $$total_tax</h1>";
								$message .= "</td>";
								$message .= "</tr>";
								$message .= "</tbody>";
								$message .= "</table>";
                            }

			echo $message;	
?>
