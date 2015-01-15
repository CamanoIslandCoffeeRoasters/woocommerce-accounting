<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
	
global $wpdb, $woocommerce;

date_default_timezone_set('America/Los_Angeles');	

	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom']));
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo']));
			
			
			$dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
			$dateFromSQL = $dateFromSQL . " 20:45:01";
			$dateToSQL = $dateTo . " 20:45:00";
            
			$row = $in_state_total = $out_state_total = 0;
			
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
						  $out_state_total += $_order->order_total;
					}
				$total_dollars += $_order->order_total;
			}
?>			
			
		<?php
		
			$in_state_tax = $wpdb->get_var("SELECT SUM(itemmeta.meta_value) 
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id 
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta 
													ON items.order_item_id = itemmeta.order_item_id
												WHERE DATE(posts.post_date) 
													BETWEEN '$dateFrom' 
														AND '$dateTo'
												AND posts.post_status = 'wc-completed'
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value = 'WA'))
												AND items.order_item_type = 'tax'
												AND itemmeta.meta_key = 'tax_amount' 
												AND itemmeta.meta_value > 0
											");
			$out_state_tax = $wpdb->get_var("SELECT SUM(itemmeta.meta_value) 
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id 
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items 
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta 
													ON items.order_item_id = itemmeta.order_item_id
												WHERE DATE(posts.post_date) 
													BETWEEN '$dateFrom' 
														AND '$dateTo'
												AND posts.post_status = 'wc-completed'
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value != 'WA'))
												AND items.order_item_type = 'tax'
												AND itemmeta.meta_key = 'tax_amount' 
												AND itemmeta.meta_value > 0
											"); 
		
			// Select all orders
			$refunded_orders = $wpdb->get_col("SELECT DISTINCT(ID) FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id 
												WHERE DATE(posts.post_modified) BETWEEN '$dateFrom' AND '$dateTo' 
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
			
			$in_state_refunds = number_format($in_state_refunds, 2,'.', ',');
			$out_state_refunds = number_format($out_state_refunds, 2,'.', ',');
			
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
				<table class="widefat fixed">
					<thead>
						<tr>
							<th></th>
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
		<?php endif; ?>
			
			
			
			
			
			
			
			
		<?php				
			
			if ($taxable_orders) {
				$message .= "<hr />";
				$message .= "<h1>Taxable Orders</h1>";
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
				$message .= "<th>";
				$message .= "Order Items";                        
				$message .= "</th>";
				$message .= "<th>";
				$message .= "";                        
				$message .= "</th>";
				$message .= "</tr>";                        
				$message .= "</thead>";
				$message .= "<tbody>";




							$total_item_tax = 0;
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
                              
                              
                              
                              $orderContent = $_order->get_items($type = 'line_item');
                              foreach ($orderContent as $k => $v ) {
                                  $message .= "<tr>";
                                  $message .= "<td>";
                                  
                                  if ($v['line_tax'] > 0) {
                                  	
                                      $message .= $v['qty'] ." - " . $v['name'] . " - $" . $v['line_total'] . " + tax: $" . number_format(round($v['line_tax'], 2, PHP_ROUND_HALF_UP), 2,'.', ',');
                                  }
                                  else {
                                  $message .= $v['qty'] ." - " . $v['name'] . " - $" . $v['line_total'];
                                  }
                                  $message .= "</td>";
                                  $message .= "</tr>";
								  
								  $total_item_tax += number_format(round($v['line_tax'], 2, PHP_ROUND_HALF_UP), 2,'.', ',');
                               
                              }

								$message .= "</tbody>";
								$message .= "</table>";
								$row+=1;

							}

								$message .= "</tr>";
								$message .= "<tr>";
								$message .= "<td>";
								$message .= "<h1>Total tax: $$total_item_tax</h1>";
								$message .= "</td>";
								$message .= "</tr>";
								$message .= "</tbody>";
								$message .= "</table>";
                            }

			echo $message;	
?>
