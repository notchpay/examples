<?php


if (session_id() == '' || !isset($_SESSION)) {
  session_start();
}

include 'config.php';

if (isset($_SESSION['cart'])) {

  $total = 0;

  foreach ($_SESSION['cart'] as $product_id => $quantity) {

    $result = $mysqli->query("SELECT * FROM products WHERE id = " . $product_id);

    if ($result) {

      if ($obj = $result->fetch_object()) {


        $cost = $obj->price * $quantity;

        $user = $_SESSION["username"];

        $query = $mysqli->query("INSERT INTO orders (product_code, product_name, product_desc, price, units, total, email) VALUES('$obj->product_code', '$obj->product_name', '$obj->product_desc', $obj->price, $quantity, $cost, '$user')");

        if ($query) {
          $newqty = $obj->qty - $quantity;
          if ($mysqli->query("UPDATE products SET qty = " . $newqty . " WHERE id = " . $product_id)) {
          }
        }
      }
    }
  }
}
require("vendor/autoload.php");
$notchpay = new NotchPay\NotchPay("sb.d9RUnRndHuVM6JaD2VjUnFwVRk82vUVv", false);


if (isset($_GET['trxref'])) {
  try {
    // verify using the library
    $tranx = $notchpay->payment->verify([
      'reference' => $_GET['trxref'], // unique to transactions
    ]);

    echo "<pre>";
    var_dump($tranx);
    echo "</pre>";

    if ('complete' === $tranx->status) {
      unset($_SESSION['cart']);
      header("location:success.php");
    } else {
      header("location:cart.php");
    }
  } catch (\NotchPay\NotchPay\Exception\ApiException $e) {
    print_r($e->getResponseObject());
    die($e->getMessage());
  }
} else {
  try {
    $tranx = $notchpay->payment->initialize([
      'amount' => 4000,
      "currency" => "XAF",
      "callback" => "http://ecommerce.test/orders-update.php",
      'email' => "chapdel.kamga2@gmail.com",         // unique to customers
    ]);

    header('Location: ' . $tranx->authorization_url);
  } catch (\NotchPay\NotchPay\Exception\ApiException $e) {
    die($e->getMessage());
  }
}
