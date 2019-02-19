<?php

/*
* Helper class for PHP code to access Wampei Register© public API
* @copyright Wampei, Inc 2018
* @version 1.0
* function to check the status of a transaction in Wampei Register© 
* domain is the domain of the server
* address is the address of the transaction
* principal is an array with the 'user' and 'password' required for access
* returns an array with 
*   priceBTC = price in BTC or Bitcoin Cash
*   address = crypto address
*   btcToUsd = Exchange price used from crypto to currency
*   priceUSD = price in currency
*   dueBTC = amount due in crypto
*   billURL = url for bill
*   status = status
*   network = network
*   desc = description sent
*/

function CheckTransactionStatus($domain, 
$address = false, 
$principal = false)
{
    $url = 'https://' . $domain . '/invoice/remote/status/json';

    
    if ( $principal == FALSE ) 
    {
        die('No user information set');
    };
    if ( $address  == FALSE ) 
    {
        die('No address information set');
    };

    $data = array (
        'address' => $address
    );
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $principal['user'] . ':'. $principal['password']);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);


    curl_close($curl);
    return (array) json_decode($result,true);
}

/* function to create a transaction in Wampei Register©
*   domain is the domain of the server
*   total is the amount of the transaction in the currency Wampei Register© is configured to use
*   desc is the optional description, we recommend using a unique id to make reconcilliation simple 
*   principal is an array with the 'user' and 'password' required for access
*   returns an array with :
*   priceBTC = price in Crypto
*    address = Crypto Address
*    btcToUsd = currency equivalent
*    priceUSD = original price in currency
*    dueBTC = amount currently due in crypto
*    bitcoinUri = payment request URI
*    qrImage link for QR code
*    billURL = URL for the bill
*    status = status of invoice 
*    network = test or production
*    desc = description passed
*/

function CreateTransaction($domain, 
$total = false, 
$desc, 
$principal = false)
{
    $url = 'https://' . $domain . '/invoice/remote/terminal/json';

    
    if ( $principal == FALSE ) 
    {
        die('No user information set');
    };
    if ( $total  == FALSE ) 
    {
        die('No total price set');
    };

    $data = array (
        'usd' => $total,
        'desc' => $desc
    );
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $principal['user'] . ':'. $principal['password']);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);


    curl_close($curl);
    return (array) json_decode($result,true);
}




$user = array(
    'user' => '', // enter user
    'password' => '' // enter password
);

echo 'User info:' .chr(10) . implode(chr(10),$user) . chr(10);
//echo 'Rest Post info:' .chr(10) . implode(chr(10),$data) . chr(10);


// $decoded = CheckTransactionStatus('test.register.cryptowampum.com','mszuhyJ4u4o1mGZjPk9VrToGN9LXmqP8D4',$user);

$decoded = CreateTransaction('test-register.wampei.com',3.56,'testing php api',$user);


/* $foo = CallAPI('POST','https://test-register.wampei.com/invoice/remote/status/json', $data, $user);
//

echo 'Response: '. $foo . chr(10);

$decoded = (array) json_decode($foo,true);
if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occured: ' . $decoded->response->errormessage);
}
echo (' call result ' . chr(10));
print_R($foo);
*/

echo 'Response ok!' . chr(10);
//echo 'JSON Resp: ' . implode( chr(10) , $decoded) . chr(10);
print_R($decoded);



//echo $decoded->response->status;


//var_export($decoded->response);
?>
