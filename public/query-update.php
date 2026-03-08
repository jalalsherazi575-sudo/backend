<?php
//$conn=mysqli_connect("localhost","root","","mobilegi_quickserve");
//$conn->set_charset("UTF8");
$conn = new mysqli("localhost","mobilegi_quickserveusr","*rq&QQTrzD5D","mobilegi_quickserve");
$conn->set_charset("UTF8");

$select=mysqli_query($conn,"select * from tblvender where isActive=1");
$rows=mysqli_num_rows($select);
if ($rows > 0) {
   while($datas=mysqli_fetch_array($select)) {
     $id=trim($datas['id']);
	 

	 $selectbusiness=mysqli_query($conn,"select id from `tblvendersubscription` where venderId=".$id."");
	 $checkbusinessrows=mysqli_num_rows($selectbusiness);
	  if ($checkbusinessrows > 0) {
	      $businessdata=mysqli_fetch_assoc($selectbusiness);
		  $subID=$businessdata['id'];
		  
	  } else {
	  	$endDate=date("Y-m-d",strtotime("+30 days"));

	    $insert=mysqli_query($conn,"Insert `tblvendersubscription` SET venderId=".$id.",subscriptionPlanId=1,subscriptionName='Free Plan',subscriptionDesc='This is free plan.',price='0.00',startDate='".date('Y-m-d H:i:s')."',endDate='".$endDate."',noOfLeadsPerDuration=100,noOfRemainingLeads=100,status=1,createdDate='".date("Y-m-d")."'");
	    $vendorsubId=mysqli_insert_id($conn);
        
        $vendorsubhistory=mysqli_query($conn,"Insert `tblvendersubscriptionhistory` SET subscriptionId=".$vendorsubId.",startDate='".date('Y-m-d H:i:s')."',endDate='".$endDate."'");

		//echo $businessId;
		//exit;
	  }
       
	  

      
         
	  

       
	 
   }

}	

?>