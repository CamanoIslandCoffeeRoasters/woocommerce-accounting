<?php
    require $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

    $table_data = $_GET['table_data'];
    $table_data = stripcslashes($table_data);
    $table_data = json_decode($table_data, TRUE);
    $results = $table_data;
    $report = $_GET['report'];
    $date = date('mdY Hi');

    $columns = array();
	$csv_output = '';

    foreach ($results as $result ) {
        foreach ($result as $column => $value) {
            $value = str_replace('/', '-', $value);
            if (strpos($value, ',')) {
                $csv_output .= '"' . $value . '",';
            }else {
                $csv_output .= $value . ',';
            }
        }
        $csv_output .= "\n";
    }


		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: text/x-csv");
		header("Content-Disposition: attachment; filename=\"$report-$date.csv\";" );
		header("Content-Transfer-Encoding: binary");

        echo $csv_output;
		exit;
 ?>
