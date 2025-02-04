<?php 
include 'config.php';

session_start();
$user = $_SESSION['username'];

$db = new Database();
$db->select('options','site_name',null,null,null,null);
$site_name = $db->getResult();

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://instamojo.com/api/1.1/payment-requests/');
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "c35102179c1f29428244e023de580008",
    "cff295f5c5caeda6bc79aa568d703b6f"
]);

$payload = [
    'purpose' => 'Payment to '.$site_name[0]['site_name'],
    'amount' => $_POST['product_total'],
    'buyer_name' => $user,
    'redirect_url' => $hostname.'/success.php',
    'allow_repeated_payments' => false
];

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
$response = curl_exec($ch);

if(curl_errno($ch)) {
    die('Curl error: ' . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response = json_decode($response);

// Check for successful API response
if($httpCode !== 201 || !isset($response->payment_request)) {
    error_log('Instamojo API Error: ' . print_r($response, true));
    die('Error creating payment request. Please try again later.');
}

$paymentRequest = $response->payment_request;

// Store payment request ID in session
$_SESSION['TID'] = $paymentRequest->id;

// Prepare database parameters
$params1 = [
    'item_number' => $_POST['product_id'],
    'txn_id' => $paymentRequest->id,
    'payment_gross' => $_POST['product_total'],
    'payment_status' => 'credit',
];

$params2 = [
    'product_id' => $_POST['product_id'],
    'product_qty' => $_POST['product_qty'],
    'total_amount' => $_POST['product_total'],
    'product_user' => $_SESSION['user_id'],
    'order_date' => date('Y-m-d'),
    'pay_req_id' => $paymentRequest->id
];

// Insert into database
$db = new Database();
$db->insert('payments', $params1);
$db->insert('order_products', $params2);

if(!empty($db->getResult()['error'])) {
    die('Database error: ' . $db->getResult()['error']);
}

// Redirect to payment URL
if(isset($paymentRequest->longurl)) {
    header('Location: ' . $paymentRequest->longurl);
} else {
    die('Payment URL not found in API response');
}
exit;
?>