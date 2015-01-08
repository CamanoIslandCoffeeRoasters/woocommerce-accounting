<?php

			// If set, get date range from $_POST, if not create 1 month span
					if (!empty($_POST['dateFrom'])) {
		                
		               $dateFrom = $_POST['dateFrom'];
		            }
		            else {
		                $dateFrom = date('Y-m-d', strtotime("-1 month"));    
		            }
		            
		            if (!empty ($_POST['dateTo'])) {
		                    
		                $dateTo = $_POST['dateTo'];
		            }
		                
		            else {
		               $dateTo = date('Y-m-d');
		            }
			
					
			// Set Time	

					// Set start date			
					$startTime = new DateTime($dateFrom);
					// Set end date
					$endTime = new DateTime($dateTo);
					
					//$date_array[] = $startTime->format("Y-m-d");
					
					while ($startTime < $endTime) {
						
						$startTime->add(new DateInterval('P1D'));
						$date_array[] = $startTime->format("Y-m-d");	
					}

global $wpdb;

echo "<hr /><h2>";
echo "<form action=\"\" method=\"POST\">";
echo "From: <input type=\"text\" name=\"dateFrom\" value=\"". $dateFrom . "\" size=\"9\" />&nbsp;";
echo "To: <input type=\"text\" name=\"dateTo\" value=\"". $dateTo . "\" size=\"9\" />&nbsp;&nbsp;&nbsp;";

//$rickandbubba = new wpdb('bbc_wpuser', 'fK5XR&6QdTNo', 'bbc_wpdb', 'localhost');

//var_dump( $rickandbubba->get_var("SELECT option_value FROM bbc_2_options where option_name = 'siteurl'"));

$wpdb->select('bbc_wpdb');

var_dump( $wpdb->get_var("SELECT option_value FROM bbc_2_options where option_name = 'siteurl'"));



?>