<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

date_default_timezone_set('America/Los_Angeles');

require_once("shippingRates.php");

$apikey = "b4d58ad8-dd5b-47a6-891b-67235457e225";
$handle = curl_init();

$url = "https://api.squarespace.com/1.0/commerce/orders?fulfillmentStatus=PENDING";

// Set the url
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_USERAGENT, "My User Agent Name");
// Set the result output to be a string.
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
//
curl_setopt($handle, CURLOPT_HTTPHEADER, array(
  'Authorization: Bearer '. $apikey,
  'Accept: application/json',
  'Content-Type: application/json'
));



$output = curl_exec($handle);
$reponse_array = json_decode($output);
curl_close($handle);

$response_result = $reponse_array->result;
   $order_list ="";
   foreach($response_result as $key => $value) {
    $order_d = $response_result[$key];
    $orderDate = new DateTime($order_d->createdOn); // datetime object
    // Check if Shipping Label Exists
    $db = connectToDb();
    $link = $db['link'];
    $schema = $db['schema'];
    $result = mysqli_query($link, "SELECT * FROM ".$schema." WHERE orderNumber='".$order_d->orderNumber."' LIMIT 1 ") or die (mysqli_error($link));
    $num_rows = mysqli_num_rows($result);
    $shipped = false;
    if ($num_rows == 1 ){
      $shipped = true;
        while($row = mysqli_fetch_array($result))
        {
            $shipmentId = $row['shipmentId'];
            $shipment = getShipment($shipmentId);
            echo $_shipmentId;
            /*$carrier = $shipment->selected_rate->carrier;
            $labelUrl = $shipment->postage_label->label_url;
            $tracking = $shipment->tracking_code;
            if($carrier == "USPS"){
              $trackingURL = "https://tools.usps.com/go/TrackConfirmAction.action?tLabels=".$tracking;
            } else if($carrier == "UPS"){
              $trackingURL = "http://wwwapps.ups.com/WebTracking/processInputRequest?TypeOfInquiryNumber=T&InquiryNumber1=".$tracking;
            } else if($carrier == "FEDEX"){
              $trackingURL = "https://www.fedex.com/fedextrack/?tracknumbers=".$tracking;
            }*/
        }
    }
    if ($shipped == true){
      $rates = '<div class="rates">
        <div>
          <button class="printLabel green">Print Label</button>
          <input class="labelUrl" value="'.$labelUrl.'" hidden>
        </div>
        <div>Tracking: <a href="'.$trackingURL.'" target="_blank">'.$tracking.'</a></div>';
    } else {
      $rates = '<div class="rates">
        <div class="dimmensions">
          <h5>Dimmensions:</h5>
          <label>H:</label>
          <input type="text" class="height" name="height" placeholder="in." />
          <label>W:</label>
          <input type="text" class="width" name="width" placeholder="in." />
          <label>D:</label>
          <input type="text" class="depth" name="depth" placeholder="in." />
          <label>Wt:</label>
          <input type="text" class="weight" name="weight" placeholder="oz." />
          <input class="address1" value="'.$order_d->shippingAddress->address1.'" hidden>
          <input class="address2" value="'.$order_d->shippingAddress->address2.'" hidden>
          <input class="city" value="'.$order_d->shippingAddress->city.'" hidden>
          <input class="state" value="'.$order_d->shippingAddress->state.'" hidden>
          <input class="postalCode" value="'.$order_d->shippingAddress->postalCode.'" hidden>
          <input class="carrier" value="" hidden>
          <input class="service" value="" hidden>
        </div>
        <div class="button">
          <button class="getRates blue">Get Rates</button>
        </div>
        <div class="results">
        </div>
      </div>';
    }
    $order_list = $order_list.'<li class="order">
                <div class="number">'.$order_d->orderNumber.'</div>
                <div class="date">'.$orderDate->format('M d, Y').'</div>
                <div class="name">'.$order_d->shippingAddress->firstName." ".$order_d->shippingAddress->lastName.'</div>
                <div class="address">'.$order_d->shippingAddress->address1.', '.$order_d->shippingAddress->address2.', '.$order_d->shippingAddress->city.', '.$order_d->shippingAddress->state.' '.$order_d->shippingAddress->postalCode.'</div>
                <div class="service">'.$order_d->shippingLines[0]->method.'</div>'
                .$rates.'
              </li>';
  }
?>
<!DOCTYPE html>
<head>
<title>Cloudforest JUNGLE</title>
<meta name="description" content="" />
<meta name="Author" content="sebastian cisneros" />
<meta http-equiv="Content-Type" content="text/; charset=utf-8" />
<meta name="viewport" content="target-densitydpi=device-dpi, width=device-width, initial-scale=2.0, maximum-scale=1,user-scalable=no" />
<link rel="shortcut icon" href="/favicon.png"/>
<link rel="icon" href="/favicon.png"/>
<link rel="stylesheet" type="text/css" href="/js/vex/dist/css/vex.css" />
<link rel="stylesheet" href="/js/vex/dist/css/vex-theme-default.css" />
<link rel="stylesheet" href="/js/vex/dist/css/vex-theme-wireframe.css" />
<link rel="stylesheet" type="text/css" href="/css/shipping.css" />
<script type="text/javascript" src="/js/jquery/dist/jquery.min.js"></script>
<script src="/js/vex/dist/js/vex.combined.min.js"></script>
<script type="text/javascript" src="/js/ship.js"></script>

</head>
<header>
  <h1 class="logo">
    <span>JUNGLE</span></h1>
</header>
<body>
  <ul class="free">
    <li class="order subHeader">
      <div class="number">No.</div>
      <div class="date">Date</div>
      <div class="name">Name</div>
      <div class="address">Address</div>
      <div class="service">Service</div>
    </li>
    <?php
      echo $order_list;
      ?>
  </ul>
</body
</html>
