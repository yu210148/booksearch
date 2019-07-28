<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="mobile-web-app-capable" content="yes">
  <title>Find Book Info</title>
  <link href='http://fonts.googleapis.com/css?family=Reenie+Beanie&subset=latin' rel='stylesheet' type='text/css'>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <link rel=StyleSheet href="./standard.css" type="text/css">
</head>
<body>
<?php
setlocale(LC_MONETARY, 'en_US');

function print_form($server){
print <<<HERE
<center><a href="http://zxing.appspot.com/scan?ret=http%3A%2F%2F$server%2Fscan%2Fscan.php?bCode=%7BCODE%7D"><h1>Tap to Scan</h1></a></center>
<br>
<br>
<FORM action="scan.php" method="POST">
<center><input type="text" name="bCode" size=20 maxlength=13 autofocus></center>
<br>
<center><INPUT class="myButton" type="submit" name="Generate" value="Do It"></center>
</form>
HERE;

  // test change
return 0;
} // end function definition for print form

function print_bookfinder($url){
print "<center><iframe width=800 height=2048 src=\"$url\">
<p>Your browser does not support iframes.</p>
</iframe></center>";
return 0;
}

function check_bookfinder($bCode){
  // a function to call the python script to check bookfinder for 'lowest price'
  $command = "./bookfinder.py $bCode";
  $command = escapeshellcmd($command);
  exec($command, $bfLowPriceArray);
  $bfLowPrice = $bfLowPriceArray[0];
  return $bfLowPrice;
}

function check_ebay($bCode){
  // a function to call the python script to check ebay for the lowest price there
  $command = "./ebay.py $bCode";
  $command = escapeshellcmd($command);
  exec($command, $ebLowPriceArray);
  $ebayLowPrice = $ebLowPriceArray[0];
  return $ebayLowPrice;
}

function check_amazon($bCode){
  // a function to call the python script to check amazon for the lowest price there
  $command = "./amazon.py $bCode";
  $command = escapeshellcmd($command);
  exec($command, $amzLowPriceArray);
  $amzLowPrice = $amzLowPriceArray[0];
  return $amzLowPrice;
}

function genchksum13($isbn){
   // shamlessly stolen from https://johnveldboom.com/posts/convert-isbn10-to-isbn13-with-php/ (https://github.com/jveldboom?tab=repositories)
   $isbn = trim($isbn);
   $tb = 0;
   for ($i = 0; $i <= 12; $i++)
   {
      $tc = substr($isbn, -1, 1);
      $isbn = substr($isbn, 0, -1);
      $ta = ($tc*3);
      $tci = substr($isbn, -1, 1);
      $isbn = substr($isbn, 0, -1);
      $tb = $tb + $ta + $tci;
   }
   
   $tg = ($tb / 10);
   $tint = intval($tg);
   if ($tint == $tg) { return 0; }
   $ts = substr($tg, -1, 1);
   $tsum = (10 - $ts);
   return $tsum;
}

function isbn10_to_13($isbn){
   // shamlessly stolen from https://johnveldboom.com/posts/convert-isbn10-to-isbn13-with-php/ (https://github.com/jveldboom?tab=repositories)
   $isbn = trim($isbn);
   if(strlen($isbn) == 12){ // if number is UPC just add zero
      $isbn13 = '0'.$isbn;}
   else
   {
      $isbn2 = substr("978" . trim($isbn), 0, -1);
      $sum13 = genchksum13($isbn2);
      $isbn13 = "$isbn2$sum13";
   }
   return ($isbn13);
}

function get_cover_image_url($bCode){
  // a function to construct and return the likely url for the cover image from bookfinder
  
  // test data
  //$bCode = "9780596101398";
  //$isbn = $bCode;
  
  //TODO: CONVERT ISBN 10 TO 13
  if(strlen($bCode) == 10){
    // received 10 character bCode; convert to isbn 13
    $isbn = isbn10_to_13($bCode);
  } else {
    // received code is longer than 10 characters; assume isbn 13 already so isbn is bCode
    $isbn = $bCode;
  }
  
  $pictureURL = "https://pictures.abebooks.com/isbn/$isbn-us-300.jpg";

  // example urls from bookfinder
  //https://pictures.abebooks.com/isbn/9780596101398-us-300.jpg
  //https://pictures.abebooks.com/isbn/9780596158064-us-300.jpg
  
  return $pictureURL;
}

function output_bookfinder($amzLowPrice, $ebayLowPrice, $bfLowPrice, $bCode){

  // get cover image url
  $coverImageURL = get_cover_image_url($bCode);

  // get half of the lowest prices
  $halfbfLowPrice = money_format('%i', $bfLowPrice / 2);
  $halfebayLowPrice = money_format('%i', $ebayLowPrice / 2);
  $halfamzLowPrice = money_format('%i', $amzLowPrice / 2);

print <<<HERE
<br>
<div align="center"  id="reportsummary">
<table class="mine" border="1">
<tr>
<th colspan="3"><center><img alt="book cover" height="200" width="150" src=$coverImageURL></center></th>
</tr>
<tr><td></td><td></td><th>.5 lowest price</th></tr>
<tr>
HERE;

  //TODO: This is ugly. put this output into a loop and sort by the price so that it always appears lowest to highest
  
  // a function to display the output from bookfinder.command
  if (is_numeric($bfLowPrice)){
    print "<td><a target=\"_blank\" href=https://www.bookfinder.com/search/?isbn=$bCode&st=xl&ac=qr>Lowest Price on BookFinder.com is</a></td><td><a target=\"_blank\" href=https://www.bookfinder.com/search/?isbn=$bCode&st=xl&ac=qr><div align=\"right\">$$bfLowPrice</a></div></td><td><div align=\"right\">$$halfbfLowPrice</div></td>";
  } else {
    print "<td><a target=\"_blank\" href=https://www.bookfinder.com/search/?isbn=$bCode&st=xl&ac=qr>No result found on Bookfinder.com</a></td><td></td><td></td>";
  } // end else
  
  print "</tr><tr>";
  
  // and ebay result(s)
  if (is_numeric($ebayLowPrice)){
    print "<td><a target=\"_blank\" href=https://www.ebay.com/sch/i.html?_from=R40&_nkw=$bCode&_sacat=0&_sop=15>Lowest Price on eBay.com is</a></td><td><a target=\"_blank\" href=https://www.ebay.com/sch/i.html?_from=R40&_nkw=$bCode&_sacat=0&_sop=15><div align=\"right\">$$ebayLowPrice</div></a></td><td><div align=\"right\">$$halfebayLowPrice</div></td>";
  } else {
    print "<td><a target=\"_blank\" href=https://www.ebay.com/sch/i.html?_from=R40&_nkw=$bCode&_sacat=0&_sop=15>No result found on eBay.com</a></td><td></td><td></td>";
  } // end else
  
  print "</tr></tr>";
  
  // and amazon result
  if (is_numeric($amzLowPrice)){
    print "<td><a target=\"_blank\" href=https://www.amazon.com/s?k=$bCode&ref=nb_sb_noss>Lowest Price on amazon.com is</a></td><td><a target=\"_blank\" href=https://www.amazon.com/s?k=$bCode&ref=nb_sb_noss><div align=\"right\">$$amzLowPrice</div></a></td><td><div align=\"right\">$$halfamzLowPrice</div></td>";
  } else {
    print "<td><a target=\"_blank\" href=https://www.amazon.com/s?k=$bCode&ref=nb_sb_noss>No result found on amazon.com</a></td><td></td><td></td>";
  } // end else
return 0;
}

// HERE'S MAIN
$bCode = $_REQUEST["bCode"];
$server = $_SERVER['SERVER_ADDR']; // this can be changed to the address of the hosting machine 

// test data
//$bCode = "0596101392";
// $bCode = "0596158068";
 //$bCode = "7749700014";

if (empty($bCode)){
	print_form($server);
} else {
	//test($bCode);
	//test2($bCode);
	print_form($server);
	$bfLowPrice = check_bookfinder($bCode);
	$ebayLowPrice = check_ebay($bCode);
	$amzLowPrice = check_amazon($bCode);
	
	output_bookfinder($amzLowPrice, $ebayLowPrice, $bfLowPrice, $bCode);
	//print "<br>Lowest Price on BookFinder.com is: $bfLowPrice<br>";
	//$url = call_bookfinder($bCode);
	//print_bookfinder($url);
	
} // end the grand else




//debug
//print "<br>$bCode<br>";

?>	  
</body>
  
