<?php
require_once __DIR__ . '/../autoload.php';
class RSTest extends PHPUnit_Framework_TestCase
{
    private $conf = array(
        'accessKey' => 'change me'
        'secretKey' => 'change me'
        'bucket' => 'com-163-sinkcup-test',
    );

    public function testDeleteFile()
    {
        $c = new \Qiniu\RS($this->conf);
        try {
            $r = $c->deleteFile('1.jpg');
            var_dump($r);
            $this->assertEquals(true, $r);
        } catch (\Exception $e) {
            echo $e->getCode();
            echo $e->getMessage();
            $this->assertEquals(true, false);
        }
    }

    public function testUploadFile()
    {
        $c = new \Qiniu\RS($this->conf);
        try{
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
            $this->assertEquals(true, isset($r['httpUri']));
        } catch (\Exception $e) {
            echo $e->getCode();
            echo $e->getMessage();
        }
    }
}
