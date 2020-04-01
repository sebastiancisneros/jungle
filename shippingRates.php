<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once("easypost-php/lib/easypost.php");

function connectToDb(){
    $link = mysqli_connect('shipping.ceg9fco2h5qr.us-west-2.rds.amazonaws.com', 'admin', 'Kllejero09!', 'spOrders', 3306);
    $db = array(
            "link" => $link,
            "schema" => 'orderList'
    );
    return $db;
};

\EasyPost\EasyPost::setApiKey('P00P4pRFtOegZlBlF5oomA');

// GET SHIPPING RATES
//header("Content-Type: application/json; charset=UTF-8");
//$shipData = json_decode($_POST[shippingData]);
//var_dump($shipData);


//var_dump($shippingData);
$request = "";

if ($_POST){
  $shippingData = json_decode($_POST['shippingData']);
  $orderId = $shippingData->orderId;
  $recipient['name'] = $shippingData->name;
  $recipient['company'] = $shippingData->company;
  $recipient['address1'] = $shippingData->address1;
  $recipient['address2'] = $shippingData->address2;
  $recipient['city'] = $shippingData->city;
  $recipient['state'] = $shippingData->state;
  $recipient['postalCode'] = $shippingData->postalCode;
  $measurements['height'] = $shippingData->height;
  $measurements['width'] = $shippingData->width;
  $measurements['depth'] = $shippingData->depth;
  $measurements['weight'] = $shippingData->weight;
  $service = $shippingData->service;
  $carrier = $shippingData->carrier;
  $request = $shippingData->request;
}

if ($request == "quote"){
  getShippingRates($recipient, $measurements);
}

if($request == "buy"){
  $shipment = createShipment($recipient, $measurements);
  buyShippingLabel($shipment, $carrier, $service, $orderId);
}
if($_REQUEST){
  if($_REQUEST['request'] == "label"){
    $orderId = $_REQUEST['orderId'];
    $labelUrl = $_REQUEST['labelUrl'];
    $label = array(
        "orderId" => $orderId,
        "url" => $labelUrl,
    );
    createShippingLabelPDF($label);
  }
}
function createShipment($recipient, $measurements){
    $to_address = \EasyPost\Address::create_and_verify(
        array(
            "name"    => $recipient['name'],
            "company" => $recipient['company'],
            "street1" => $recipient['address1'],
            "street2" => $recipient['address2'],
            "city"    => $recipient['city'],
            "state"   => $recipient['state'],
            "zip"     => $recipient['postalCode']
        )
    );
    $verify_address = json_decode($to_address);
    if($to_address['error']['code'] == "ADDRESS.VERIFY.FAILURE"){
        $verify_address = false;
        echo 'Failed Verifying Shipping Address!';
        exit();
    }
    $from_address = \EasyPost\Address::create(
        array(
            "company" => "Cloudforest",
            "street1" => "800 SE 10th Ave.",
            "street2" => "",
            "city"    => "Portland",
            "state"   => "OR",
            "zip"     => "97214",
            "phone"   => "503-475-0359"
        )
    );
    $parcel = \EasyPost\Parcel::create(
        array(
            "length" => intval($measurements['depth']),
            "width" => intval($measurements['width']),
            "height" => intval($measurements['height']),
            "weight" => intval($measurements['weight'])
        )
    );
    $shipment = \EasyPost\Shipment::create(
        array(
            "to_address"   => $to_address,
            "from_address" => $from_address,
            "parcel"       => $parcel
        )
    );
    return $shipment;
}
function getShippingRates($recipient, $measurements){
  $shipment = createShipment($recipient, $measurements);
  displayRates($shipment);
}
function displayRates($shipment){
  foreach ($shipment->rates as $rates){
      if($rates->carrier == "UPS" || $rates->carrier == "USPS"){
          $slash = '&nbsp;/&nbsp;';
          if ($rates->service == "First"){
              $usps_first= '
              <div class="option">
                <input type="radio" class="carrier" name="shipping-service" id="carrier1" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                <label for="carrier2" class="usps">
                  <div>
                    <span class="mobile">USPS</span>
                    <span class="service">First Class</span>
                    <span class="time">5-7 Day</span>
                    <span class="price">'.$slash.'$'.$rates->rate.'</span>
                  </div>
                </label>
              </div>';
              $checked = '';
              $paid = "";
              $usps = true;
          }
          if ($rates->service == "ParcelSelect"){
              $usps_parcel_select= '
              <div class="option">
                <input type="radio" class="carrier" name="shipping-service" id="carrier2" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                <label for="carrier3" class="usps">
                  <div>
                    <span class="mobile">USPS</span>
                    <span class="service">Parcel Select</span>
                    <span class="time">5-7 Day</span>
                    <span class="price">'.$slash.'$'.$rates->rate.'</span>
                  </div>
                </label>
              </div>';
              $checked = '';
              $usps = true;

          }
          if ($rates->service == "Priority"){
              $usps_priority = '
              <div class="option">
                  <input type="radio" class="carrier" name="shipping-service" id="carrier3" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                  <label for="carrier4" class="usps">
                      <div>
                          <span class="mobile">USPS</span>
                          <span class="service">Priority</span>
                          <span class="time">2-3 Day</span>
                          <span class="price">'.$slash.'$'.$rates->rate.'</span>
                      </div>
                  </label>
              </div>';
              $paid = "";
              $checked = '';
              $usps = true;
          }
          if($rates->service == "2ndDayAir"){
            $usps_2ndDayAir= '
            <div class="option">
              <input type="radio" class="carrier" name="shipping-service" id="carrier4" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
              <label for="carrier1" class="usps">
                <div>
                  <span class="mobile">USPS</span>
                  <span class="service">2nd Day Air</span>
                  <span class="time">1 Day</span>
                  <span class="price">'.$slash.'$'.$rates->rate.'</span>
                </div>
              </label>
            </div>';
            $checked = '';
            $paid = "";
            $usps = true;
          }
          if ($rates->service == "Express"){
              $usps_express = '
              <div class="option">
                  <input type="radio" class="carrier" name="shipping-service" id="carrier5" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                  <label for="carrier5" class="usps">
                      <div>
                          <span class="mobile">USPS</span>
                          <span class="service">Express</span>
                          <span class="time">1 Day</span>
                          <span class="price">'.$slash.'$'.$rates->rate.'</span>
                      </div>
                  </label>
              </div>';
              $paid = "";
              $checked = '';
              $usps = true;
          }
          if ($rates->service == "Ground"){
              $ups_ground = '
              <div class="option">
                <input type="radio" class="carrier" name="shipping-service" id="carrier6" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                <label for="carrier6" class="ups">
                  <div>
                    <span class="mobile">UPS</span>
                    <span class="service">Ground</span>
                    <span class="time">5-7 Day</span>
                    <span class="price">'.$slash.'$'.$rates->rate.'</span>
                  </div>
                </label>
              </div>';
              $paid = "";
              $checked = '';
              $ups = true;
          }
          if($rates->service == "NextDayAir"){
              $ups_next_day_air = '
              <div class="option">
                  <input type="radio" class="carrier" name="shipping-service" id="carrier7" value="'.$rates->service.'" carrier="'.$rates->carrier.'">
                  <label for="carrier7" class="ups '.$paid.'">
                      <div>
                          <span class="mobile">UPS</span>
                          <span class="service">Next Day Air</span>
                          <span class="time">1 Day</span>
                          <span class="price">'.$slash.'$'.$rates->rate.'</span>
                      </div>
                  </label>
              </div>';
              $paid = "";
              $checked = '';
              $ups = true;
          }
      }
  }
  $buyButton = '
  <div class="button">
    <button class="buyPostage orange">Buy Postage</button>
  </div>';
  $estimateResults = '<div class="options">'.$usps_first.$usps_parcel_select.$usps_priority.$usps_express.$usps_2ndDayAir.$ups_ground.$ups_next_day_air.'</div>'.$buyButton;
                          echo $estimateResults;

}
function buyShippingLabel($shipment, $carrier, $service, $orderId){
  $db = connectToDb();
  $link = $db['link'];
  $schema = $db['schema'];
  $shipment->buy(array(
    'rate'      => $shipment->lowest_rate(array($carrier), array($service)),
    'insurance' => 249.99
  ));
  //$tracking_code = '9461200897846036034181'; //$shipment->tracking_code;
  $label = array(
      "orderId" => $orderId,
      "shipmentId" => $shipment->id,
  );
  $label_url = $shipment->postage_label->label_url;
  $carrier = $shipment->selected_rate->carrier;
  $tracking = $shipment->tracking_code;

  if($carrier == "USPS"){
    $trackingURL = "https://tools.usps.com/go/TrackConfirmAction.action?tLabels=".$tracking;
  } else if($carrier == "UPS"){
    $trackingURL = "http://wwwapps.ups.com/WebTracking/processInputRequest?TypeOfInquiryNumber=T&InquiryNumber1=".$tracking;
  } else if($carrier == "FEDEX"){
    $trackingURL = "https://www.fedex.com/fedextrack/?tracknumbers=".$tracking;
  }

  $sql = "INSERT INTO orderList(orderNumber, shipmentId) VALUES ('".$label['orderId']."', '".$label['shipmentId']."')";
  $storeLabelData = mysqli_query($link, $sql) or die(mysqli_error($link));

  $postBuyHTML = '
    <div><h5>Label Purchase Confirmed!</h5>
    <div>
      <button class="printLabel green">Print Label</button>
      <input class="labelUrl" value="'.$label_url.'" hidden>
    </div>
    <div>Tracking: <a href="'.$trackingURL.'" target="_blank">'.$tracking.'</a></div>';
  ;
  echo $postBuyHTML;
}
function getShipment($shipmentId){
  $shipment = \EasyPost\Shipment::retrieve($shipmentId);
  return $shipment;
}
function createShippingLabelPDF($label){
  //header('Content-type: application/pdf');
    // Create PDF
    require_once('FPDF/fpdf.php');
    require_once('FPDF/FPDI/src/autoload.php');
    require_once('FPDF/rotation.php');
    class PDF extends PDF_Rotate
    {
        function RotatedText($x, $y, $txt, $angle)
        {
            //Text rotated around its origin
            $this->Rotate($angle, $x, $y);
            $this->Text($x, $y, $txt);
            $this->Rotate(0);
        }

        function RotatedImage($file, $x, $y, $w, $h, $angle)
        {
            //Image rotated around its upper-left corner
            $this->Rotate($angle, $x, $y);
            $this->Image($file, $x, $y, $w, $h);
            $this->Rotate(0);
        }
    }
    // initiate FPDI
    $pdf = new PDF("P","in",array(6,4));

    $title = 'Cloudforest Shipping Label - Order '.$label['orderId'];
    $file = $label['url'];
    $w = 4;
    $h = 6;
    $x = 0;
    $y = 0;

    // add a page
    $pdf->AddPage();
    $pdf->SetMargins(0,0,0);
    $pdf->SetAutoPageBreak(false);
    $pdf->Image($file, $x, $y+0.5, $w, $h);
    $pdf->Image('pdf/header.jpg', 0, 0, 4, 0.5);

    $pdf->SetAuthor('Cloudforest');
    $pdf->SetTitle($title);

    //$filename = __DIR__."/pdf/labels/order-".$label['orderId'].".pdf";
    $filename = "Cloudforest Order ".$label['orderId']." Shipping Label.pdf";
    //$pdf->Output($filename,'I');
    $pdf->Output($filename,'I');
}
?>
