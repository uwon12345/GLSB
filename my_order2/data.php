<?php
include "../inc/dbconfig.php";
include "../inc/dbfunctions.php";
include "../main/session.php";
include "../main/functions.php";

$connect = mysql_connect($dbHost, $dbUser, $dbPass)
or die('Could not connect: ' . mysql_error());
//Select The database
$bool = mysql_select_db($dbName, $connect);
if ($bool === False){
   print "can't find $database";
}
// get data and store in a json array

	
	$status_id = $_GET['status_id'];
	$user_id = $_SESSION[user_id];
	
	$subname = $_GET['subname'];

	
	$pagenum = $_GET['pagenum'];
	$pagesize = $_GET['pagesize'];
	$start = $pagenum * $pagesize;
	
	$user_group = $_SESSION['user_grp'];

	if($subname == 'myorder')
	{
		$viewAllSQL = 'where customer_id = ' . $user_id . '';
		$viewAllSQLFilter = 'and customer_id = ' . $user_id . '';
	}
		
	if ($subname == 'AllOrder')
	{		
		if($user_group == 1) //admin only
		{
			if($status_id == 1)//pending and need approved
			{
			$viewAllSQL = 'where sorder.status_id = 1';	
			}
			else 
			{
			$viewAllSQL = 'where sorder.status_id != 1';	
			}
		}
	}


	$query = "SELECT SQL_CALC_FOUND_ROWS *, sorder.approve_date as approve_date_sorder FROM sorder left join user on user.user_id = sorder.customer_id left join sorder_status on sorder.status_id = sorder_status.status_id $viewAllSQL order by sorder.so_id desc LIMIT $start, $pagesize";	
	
	$result = mysql_query($query) or die("SQL Error 1: " . mysql_error());
	$sql = "SELECT FOUND_ROWS() AS `found_rows`;";
	$rows = mysql_query($sql);
	$rows = mysql_fetch_assoc($rows);
	$total_rows = $rows['found_rows'];
	$filterquery = "";
	

	// filter data.
	if (isset($_GET['filterscount']))
	{
		$filterscount = $_GET['filterscount'];
		
		if ($filterscount > 0)
		{
			$where = " WHERE (";
			$tmpdatafield = "";
			$tmpfilteroperator = "";
			for ($i=0; $i < $filterscount; $i++)
		    {
				// get the filter's value.
				$filtervalue = $_GET["filtervalue" . $i];
				// get the filter's condition.
				$filtercondition = $_GET["filtercondition" . $i];
				// get the filter's column.
				$filterdatafield = $_GET["filterdatafield" . $i];
				// get the filter's operator.
				$filteroperator = $_GET["filteroperator" . $i];
				
				if ($tmpdatafield == "")
				{
					$tmpdatafield = $filterdatafield;			
				}
				else if ($tmpdatafield <> $filterdatafield)
				{
					$where .= ")AND(";
				}
				else if ($tmpdatafield == $filterdatafield)
				{
					if ($tmpfilteroperator == 0)
					{
						$where .= " AND ";
					}
					else $where .= " OR ";	
				}
				
				// build the "WHERE" clause depending on the filter's condition, value and datafield.
				switch($filtercondition)
				{
					case "NOT_EMPTY":
					case "NOT_NULL":
						$where .= " " . $filterdatafield . " NOT LIKE '" . "" ."'";
						break;
					case "EMPTY":
					case "NULL":
						$where .= " " . $filterdatafield . " LIKE '" . "" ."'";
						break;
					case "CONTAINS_CASE_SENSITIVE":
						$where .= " BINARY  " . $filterdatafield . " LIKE '%" . $filtervalue ."%'";
						break;
					case "CONTAINS":
						$where .= " " . $filterdatafield . " LIKE '%" . $filtervalue ."%'";
						break;
					case "DOES_NOT_CONTAIN_CASE_SENSITIVE":
						$where .= " BINARY " . $filterdatafield . " NOT LIKE '%" . $filtervalue ."%'";
						break;
					case "DOES_NOT_CONTAIN":
						$where .= " " . $filterdatafield . " NOT LIKE '%" . $filtervalue ."%'";
						break;
					case "EQUAL_CASE_SENSITIVE":
						$where .= " BINARY " . $filterdatafield . " = '" . $filtervalue ."'";
						break;
					case "EQUAL":
						$where .= " " . $filterdatafield . " = '" . $filtervalue ."'";
						break;
					case "NOT_EQUAL_CASE_SENSITIVE":
						$where .= " BINARY " . $filterdatafield . " <> '" . $filtervalue ."'";
						break;
					case "NOT_EQUAL":
						$where .= " " . $filterdatafield . " <> '" . $filtervalue ."'";
						break;
					case "GREATER_THAN":
						$where .= " " . $filterdatafield . " > '" . $filtervalue ."'";
						break;
					case "LESS_THAN":
						$where .= " " . $filterdatafield . " < '" . $filtervalue ."'";
						break;
					case "GREATER_THAN_OR_EQUAL":
						$where .= " " . $filterdatafield . " >= '" . $filtervalue ."'";
						break;
					case "LESS_THAN_OR_EQUAL":
						$where .= " " . $filterdatafield . " <= '" . $filtervalue ."'";
						break;
					case "STARTS_WITH_CASE_SENSITIVE":
						$where .= " BINARY " . $filterdatafield . " LIKE '" . $filtervalue ."%'";
						break;
					case "STARTS_WITH":
						$where .= " " . $filterdatafield . " LIKE '" . $filtervalue ."%'";
						break;
					case "ENDS_WITH_CASE_SENSITIVE":
						$where .= " BINARY " . $filterdatafield . " LIKE '%" . $filtervalue ."'";
						break;
					case "ENDS_WITH":
						$where .= " " . $filterdatafield . " LIKE '%" . $filtervalue ."'";
						break;
				}
				//added by uw support
				$where = $where . $viewAllSQLFilter;
								
				if ($i == $filterscount - 1)
				{
					$where .= ")";
				}
				
				$tmpfilteroperator = $filteroperator;
				$tmpdatafield = $filterdatafield;			
			}
			// build the query.
			$query = "SELECT *, sorder.approve_date as approve_date_sorder FROM sorder left join user on user.user_id = sorder.customer_id left join sorder_status on sorder.status_id = sorder_status.status_id ".$where;
			$filterquery = $query;
			$result = mysql_query($query) or die("SQL Error 1: " . mysql_error());
			$sql = "SELECT FOUND_ROWS() AS `found_rows`;";
			$rows = mysql_query($sql);
			$rows = mysql_fetch_assoc($rows);
			$new_total_rows = $rows['found_rows'];		
			$query = "SELECT *, sorder.approve_date as approve_date_sorder FROM sorder left join user on user.user_id = sorder.customer_id left join sorder_status on sorder.status_id = sorder_status.status_id ".$where." LIMIT $start, $pagesize";		
			$total_rows = $new_total_rows;	
		}
	}
	
	if (isset($_GET['sortdatafield']))
	{
	
		$sortfield = $_GET['sortdatafield'];
		$sortorder = $_GET['sortorder'];
		
		if ($sortorder != '')
		{
			if ($_GET['filterscount'] == 0)
			{
				if ($sortorder == "desc")
				{
					$query = "SELECT *, sorder.approve_date as approve_date_sorder FROM sorder left join user on user.user_id = sorder.customer_id left join sorder_status on sorder.status_id = sorder_status.status_id $viewAllSQL ORDER BY" . " " . $sortfield . " DESC LIMIT $start, $pagesize";
				}
				else if ($sortorder == "asc")
				{
					$query = "SELECT *, sorder.approve_date as approve_date_sorder FROM sorder left join user on user.user_id = sorder.customer_id left join sorder_status on sorder.status_id = sorder_status.status_id $viewAllSQL ORDER BY" . " " . $sortfield . " ASC LIMIT $start, $pagesize";
				}
			}
			else
			{
				if ($sortorder == "desc")
				{
					$filterquery .= " ORDER BY" . " " . $sortfield . " DESC LIMIT $start, $pagesize";
				}
				else if ($sortorder == "asc")	
				{
					$filterquery .= " ORDER BY" . " " . $sortfield . " ASC LIMIT $start, $pagesize";
				}
				$query = $filterquery;
			}		
		}
	}
	
	$result = mysql_query($query) or die("SQL Error 1: " . mysql_error());
	$orders = null;
	// get data and store in a json array
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$orders[] = array(
				'so_id' => $row['so_id'],
				'so_no' => $row['so_no'],
				'status_id' => $row['status_id'],
				'status_name' => $row['status_name'],
				'so_date' => $row['so_date'],
				'approve_date_sorder' => $row['approve_date_sorder'],
				'so_address' => $row['so_address'],
				'so_postcode' => $row['so_postcode'],
				'so_city' => $row['so_city'],
				'so_state' => $row['so_state'],
				'so_country' => $row['so_country'],
				'total_weight_in_gram' => $row['total_weight_in_gram'],
				'total_pv' => $row['total_pv'],
				'courier_amount' => $row['courier_amount'],
				'amount' => $row['amount'],
				'member_reg_no' => $row['member_reg_no'],
				'name' => $row['name'],
				'file_name' => $row['file_name'],
				'delivery_sw' => $row['delivery_sw']
		  );
	}
      $data[] = array(
       'TotalRows' => $total_rows,
	   'Rows' => $orders
	);
	echo json_encode($data);

?>