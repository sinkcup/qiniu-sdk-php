<?php
require_once __DIR__ . '/vendor/autoload.php';
$conf = array(
    'accessKey' => 'asdf',
    'secretKey' => 'qwer',
    'bucket' => 'com-163-sinkcup-test',
);
$c = new \Qiniu\RS($conf);
$headers = array(
    'Content-Type' => 'image/jpeg',
    //'Content-Type' => 'application/vnd.android.package-archive',
    //'Content-Type' => 'application/octet-stream',
    //'Content-Type' => 'application/xml',
);
$r = $c->uploadFile('/home/sinkcup/1.jpg', '/1.jpg', $headers);
//$r = $c->uploadFile('/home/sinkcup/1.apk', '1.apk', $headers);
//$r = $c->uploadFile('/home/sinkcup/1.ipa', '1.ipa', $headers);
//$r = $c->uploadFile('/home/sinkcup/1.plist', '1.plist');
var_dump($r);
     
//其他功能的使用方法：请参考 tests/
?>
