<?
    //API KEYS & DB CONNECTION LINK
    $_SESSION['live'] = "off";
    if ($_SESSION['live'] == "on"){
        //db link
        $link = mysqli_connect('localhost', 'sebastian', 'Kllejero09');

    } else if ($_SESSION['live'] == "off"){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        //db link
        $link = mysqli_connect('shipping.ceg9fco2h5qr.us-west-2.rds.amazonaws.com', 'admin', 'Kllejero09!', 'spOrders', 3306);
    }
    mysqli_select_db($link, 'shipping');
?>
