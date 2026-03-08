<?php
echo phpinfo();
exit;
//$conn=mysqli_connect("localhost","root","","mobilegi_quickserve");
//$conn->set_charset("UTF8");
$conn = new mysqli("localhost","mobilegi_quickserveusr","*rq&QQTrzD5D","mobilegi_quickserve");
$conn->set_charset("UTF8");

$select=mysqli_query($conn,"select * from Sheet2");
$rows=mysqli_num_rows($select);
if ($rows > 0) {
   while($datas=mysqli_fetch_array($select)) {
     $maincat=trim($datas['CategoryName']);
	 $maincat_arabic=trim($datas['CategoryNameAr']);
	 $subcat=trim($datas['Subcategory']);
	 $subcat_arabic=trim($datas['SubCategoryNameAr']);
	 
	 $selectbusiness=mysqli_query($conn,"select id from `tblbusinesscategory` where name='".$maincat."' LIMIT 1");
	 $checkbusinessrows=mysqli_num_rows($selectbusiness);
	  if ($checkbusinessrows > 0) {
	      $businessdata=mysqli_fetch_assoc($selectbusiness);
		  $businessId=$businessdata['id'];
		  
	  } else {
	    $insert=mysqli_query($conn,"Insert `tblbusinesscategory` SET name='".$maincat."',photo='',noOfVenders=0");
	    $businessId=mysqli_insert_id($conn);
		//echo $businessId;
		//exit;
	  }
       
	  $selectbusinesseng=mysqli_query($conn,"select * from `tblbusinesscategorytranslation` where name='".$maincat."'  LIMIT 1");
	  $checkbusinessengrows=mysqli_num_rows($selectbusinesseng);
       if ($checkbusinessengrows==0) {
	     $enginsert=mysqli_query($conn,"Insert `tblbusinesscategorytranslation` SET name='".$maincat."',langId=1,businessCategoryId=".$businessId."");
	   }	

       $selectbusinessarb=mysqli_query($conn,"select * from `tblbusinesscategorytranslation` where name='".$maincat_arabic."'  LIMIT 1");
	  $checkbusinessarbrows=mysqli_num_rows($selectbusinessarb);
       if ($checkbusinessarbrows==0) {
	     $arbinsert=mysqli_query($conn,"Insert `tblbusinesscategorytranslation` SET name='".$maincat_arabic."',langId=2,businessCategoryId=".$businessId."");
	   }

         $selectservice=mysqli_query($conn,"select id from `tblservicetype` where name='".$subcat."' and businessCategoryId=".$businessId."  LIMIT 1");
	 $checkservicerows=mysqli_num_rows($selectservice);
	  if ($checkservicerows > 0) {
	      $servicedata=mysqli_fetch_assoc($selectservice);
		  $serviceId=$servicedata['id'];
		  
	  } else {
	    $insertd=mysqli_query($conn,"Insert `tblservicetype` SET name='".$subcat."',businessCategoryId=".$businessId.",photo='',noOfVenders=0,isActive=1");
	    $serviceId=mysqli_insert_id($conn);
		//echo $businessId;
		//exit;
	  }	   
	  
	  $selectserviceseng=mysqli_query($conn,"select * from `tblservicetypetranslation` where name='".$subcat."' and serviceTypeId=".$serviceId."  LIMIT 1");
	  $checkserviceengrows=mysqli_num_rows($selectserviceseng);
       if ($checkserviceengrows==0) {
	     $enginsert=mysqli_query($conn,"Insert `tblservicetypetranslation` SET name='".$subcat."',langId=1,serviceTypeId=".$serviceId."");
	   }	

       $selectservicearb=mysqli_query($conn,"select * from `tblservicetypetranslation` where name='".$subcat_arabic."' and serviceTypeId=".$serviceId."  LIMIT 1");
	  $checkservicearbrows=mysqli_num_rows($selectservicearb);
       if ($checkservicearbrows==0) {
	     $arbinsert=mysqli_query($conn,"Insert `tblservicetypetranslation` SET name='".$subcat_arabic."',langId=2,serviceTypeId=".$serviceId."");
	   }
	  
	 
   }

}	

?>