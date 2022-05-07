--TEST--
Check for yar server __auth (concurrent call)
--SKIPIF--
<?php 
if (!extension_loaded("yar")) {
    print "skip";
}
?>
--FILE--
<?php 
include "yar.inc";

yar_server_start(<<<'PHP'
<?php
error_reporting(-1);
class Service_Provider {
	protected function __auth($provider, $token) {
        return ($provider == "Yar" && ($token == md5("yar") || $token == substr(md5("yar"), 0, 18)));
	}
   
    public function info() {
        return "okay";
    }
}

$yar = new Yar_Server(new Service_Provider());
$yar->handle();
PHP
);

Yar_Concurrent_Client::call(YAR_API_ADDRESS, "info", array());
Yar_Concurrent_Client::call(YAR_API_ADDRESS, "info", array());
Yar_Concurrent_Client::call(YAR_API_ADDRESS, "info", array());
Yar_Concurrent_Client::call(YAR_API_ADDRESS, "info", array());
Yar_Concurrent_Client::call(YAR_API_ADDRESS, "info", array(), NULL, NULL, array(YAR_OPT_PROVIDER=>"Yar", YAR_OPT_TOKEN=>substr(md5("yar"), 0, 18)));

Yar_Concurrent_Client::loop(function($return, $callinfo) {
      echo $return;
}, function($type, $error, $callinfo) {
      if ($error != "authentication failed" || $callinfo["sequence"] != 4) {
          echo "error";
          return;
      }
      echo "okay";
}, 
array(
	YAR_OPT_PROVIDER=>"Yar", YAR_OPT_TOKEN=>md5("yar")
)
);
?>
--CLEAN--
<?php
include 'yar.inc';
yar_server_cleanup();
?>
--EXPECT--
okayokayokayokayokay
