
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>
<body>

<?php
     $page="";
    if(isset($_GET['page'])){
	  $page= $_GET['page'];
	  
	  switch($page){
		case "home":
		include "home.php";  
		break;
		case "product":
		include "product.php";  
		break;
		case "contact":
		include "contact.php";  
		break;
		case "about":
		include "about.php";  
		break;
		default:
		echo "no page";}
	  }else{
		  include "home.php"; 
		  }
?>
</body>
</html>