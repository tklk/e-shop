<?php 
header("Content-Type: text/html; charset=utf-8");

require_once("session.php");	
require_once("class.user.php");
require_once("mycart.php");	
	$auth_user = new USER();
	
	$user_id = $_SESSION['user_session'];
	
	$stmt = $auth_user->runQuery("SELECT * 
								  FROM users
								  WHERE user_id=:user_id");
								  
	$stmt->execute(array(":user_id"=>$user_id));
	
	$userRow=$stmt->fetch(PDO::FETCH_ASSOC);

// ------------------------------------ //
// Shopping cart ---------------------- //
// ------------------------------------ //

$cart = unserialize($_SESSION['cart']);
if(!is_object($cart))
{
	$cart = new myCart();
}
if(isset($_POST['button3']))
{
	$cart->add_item($_POST['id'],$_POST['qty'],$_POST['price'],$_POST['name']);
	$_SESSION['cart'] = serialize($cart);
}

// Update cart
if(isset($_POST['updatebtn']))
{
	if(isset($_POST["updateid"]))
	{
		$i=count($_POST["updateid"]);
		for($j=0;$j<$i;$j++)
		{
			$cart->edit_item($_POST['updateid'][$j],$_POST['qty'][$j]);
		}
	}
	$_SESSION['cart'] = serialize($cart);
	header("Location: cart.php");
}

// Delete item in cart
if(isset($_GET["cartaction"]) && ($_GET["cartaction"]=="remove")){
	$rid = intval($_GET['delid']);
	$cart->del_item($rid);
	$_SESSION['cart'] = serialize($cart);
	header("Location: cart.php");	
}

// Clear cart
if(isset($_GET["cartaction"]) && ($_GET["cartaction"]=="empty"))
{
	unset($_SESSION['cart']);
	$cart = new myCart();
	$_SESSION['cart'] = serialize($cart);
	header("Location: cart.php");
}



// ------------------------------------ //
// Product menu- ---------------------- //
// ------------------------------------ //

$query_RecCategory = "SELECT `category`.`categoryid`, `category`.`categoryname`, `category`.`categorysort`, count(`product`.`productid`) as productNum FROM `category` LEFT JOIN `product` ON `category`.`categoryid` = `product`.`categoryid` GROUP BY `category`.`categoryid`, `category`.`categoryname`, `category`.`categorysort` ORDER BY `category`.`categorysort` ASC";
$RecCategory = $auth_user->runQuery($query_RecCategory);
$RecCategory->execute();

// Total record
$query_RecTotal = "SELECT count(`productid`)as totalNum FROM `product`";
$RecTotal = $auth_user->runQuery($query_RecTotal);
$RecTotal->execute();
$row_RecTotal=$RecTotal->fetch(PDO::FETCH_ASSOC);
?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> NCTU E-Store </title>
<link href="style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="index.css" type="text/css"  />
</head>

<body>
	<div class="forbeut1" >
    <a href="homepage.php?"><img src="images/logo.png" width="150" align="absmiddle"></a><br><br>
  </div>
<table width="80%" border="0" align="center" cellpadding="4" cellspacing="0" bgcolor="#FFFFFF">
  <tr>
    <td class="tdbline"><table width="100%" border="0" cellspacing="0" cellpadding="10">
      <tr valign="top">
        <td width="200" class="tdrline">
            <div class="boxtl"></div>
            <div class="boxtr"></div>            
            <div class="categorybox">
              <p class="heading">
                <img src="images/search_icon.png" width="16" height="16" align="absmiddle"> Product 
                <span class="smalltext"> Search </span>
              </p>
              <form name="form1" method="get" action="homepage.php">
                <p>
                  <input name="keyword" type="text" id="keyword" value="Search for anything" size="12" onClick="this.value='';">
                  <input type="submit" id="button" value="Search">
                  </p>
              </form>
              <p class="heading">
                <img src="images/price-icon.png" width="16" height="16" align="absmiddle"> Price  
                <span class="smalltext"> range </span>
              </p>
              <form action="homepage.php" method="get" name="form2" id="form2">
                <p>
                  <input name="price1" type="text" id="price1" value="0" size="3">
                  -
                  <input name="price2" type="text" id="price2" value="0" size="3">
                <input type="submit" id="button2" value="Search">
                </p>
              </form>
            </div>
            <div class="boxbl"></div>
            <div class="boxbr"></div>                    	
            <hr width="100%" size="1" />
            <div class="boxtl"></div>
            <div class="boxtr"></div>            
            <div class="categorybox">
            <p class="heading">
              <img src="images/type_icon.png" width="16" height="16" align="absmiddle"> Shop by Category 
            </p>
            <ul>
              <li><a href="homepage.php?"> All <span class="categorycount">(<?php echo $row_RecTotal["totalNum"];?>)</span></a></li>
			  <?php	while($row_RecCategory=$RecCategory->fetch(PDO::FETCH_ASSOC)){ ?>
              <li><a href="homepage.php?cid=<?php echo $row_RecCategory["categoryid"];?>"><?php echo $row_RecCategory["categoryname"];?> <span class="categorycount">(<?php echo $row_RecCategory["productNum"];?>)</span></a></li>
              <?php }?>
            </ul>
          </div>
          <div class="boxbl"></div>
          <div class="boxbr"></div></td>
        <td>
          <div class="list">&nbsp; Dear &nbsp; <?php echo $userRow['user_email']; ?>
            <ul class="pslist">
            <li><a href="resetpass.php" class="c"> Reset password </a></li>
            <li><a href="logout.function.php?logout=true" class="c"> Logout </a></li>
            </ul>
          </div>
          <div class="subjectDiv"> <span class="heading"><img src="images/store_icon.png" width="16" height="16" align="absmiddle"></span> Shopping Cart </div>
          <div class="normalDiv">
		  <?php if($cart->itemcount > 0) {?>
          <form action="" method="post" name="cartform" id="cartform">
          <table width="98%" border="0" align="center" cellpadding="2" cellspacing="1">
              <tr>
                <th bgcolor="#ECE1E1"><p> Remove item </p></th>
                <th bgcolor="#ECE1E1"><p> Product name </p></th>
                <th bgcolor="#ECE1E1"><p> Quantity </p></th>
                <th bgcolor="#ECE1E1"><p> Price </p></th>
                <th bgcolor="#ECE1E1"><p> Sub </p></th>
              </tr>
          <?php		  
		  	foreach($cart->get_contents() as $item) {
		  ?>              
              <tr>
                <td align="center" bgcolor="#F6F6F6" class="tdbline"><p><a href="?cartaction=remove&delid=<?php echo $item['id'];?>"> Delete </a></p></td>
                <td bgcolor="#F6F6F6" class="tdbline"><p><?php echo $item['info'];?></p></td>
                <td align="center" bgcolor="#F6F6F6" class="tdbline"><p>
                  <input name="updateid[]" type="hidden" id="updateid[]" value="<?php echo $item['id'];?>">
                  <input name="qty[]" type="text" id="qty[]" value="<?php echo $item['qty'];?>" size="1">
                  </p></td>
                <td align="center" bgcolor="#F6F6F6" class="tdbline"><p>$ <?php echo number_format($item['price']);?></p></td>
                <td align="center" bgcolor="#F6F6F6" class="tdbline"><p>$ <?php echo number_format($item['subtotal']);?></p></td>
              </tr>
          <?php }?>
              <tr>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p> Shipping & Handling </p></td>
                <td valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p>$ <?php echo number_format($cart->deliverfee);?></p></td>
              </tr>
              <tr>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p> Total </p></td>
                <td valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p>&nbsp;</p></td>
                <td align="center" valign="baseline" bgcolor="#F6F6F6"><p class="redword">$ <?php echo number_format($cart->grandtotal);?></p></td>
              </tr>          
            </table>
            <hr width="100%" size="1" />
            <p align="center">
              <input name="cartaction" type="hidden" id="cartaction" value="update">
              <input type="submit" name="updatebtn" id="button3" value="Update cart">
              <input type="button" name="emptybtn" id="button5" value="Clear cart" onClick="window.location.href='?cartaction=empty'">
              <input type="button" name="button" id="button6" value="Proceed to checkout" onClick="window.location.href='checkout.php';">
              <input type="button" name="backbtn" id="button4" value="Back" onClick="window.history.back();">
              </p>
          </form>
          </div>          
            <?php }else{ ?>
            <div class="infoDiv"> Cart is empty. </div>
          <?php } ?></td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td height="30" align="center" class="trademark"> NCTU E-Store </td>
  </tr>
</table>
</body>
</html>
