--TEST--
Check for yar concurrent client with throw exception in callback
--SKIPIF--
<?php 
if (!extension_loaded("yar")) {
    print "skip";
}
?>
--FILE--
<?php 
include "yar.inc";

yar_server_start();

function callback($return, $callinfo) {
    global $sequence;

    if ($callinfo) {
        throw new Exception("exception in callback");
    }
}

$arguments = array(
    YAR_API_ADDRESS,
    "normal",
    array(
      "xxx", "3.8"
    )
);

Yar_Concurrent_Client::call(...$arguments);
Yar_Concurrent_Client::call(...$arguments);
Yar_Concurrent_Client::call(...$arguments);

try {
    Yar_Concurrent_Client::loop("callback");
} catch (Exception $e) {
    var_dump($e->getMessage());
}

--CLEAN--
<?php
include 'yar.inc';
yar_server_cleanup();
?>
--EXPECTF--
string(21) "exception in callback"
