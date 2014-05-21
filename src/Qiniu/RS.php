<?php
/**
 * 七牛云存储 Qiniu Resource (Cloud) Storage PHP SDK（composer版）
 *
 * 书写规范：PSR2
 *
 * phpcs --standard=PSR2 src/Qiniu/RS.php
 *
 * @author   sink <sinkcup@163.com>
 * @link     https://github.com/sinkcup/qiniu-sdk-php
 */

namespace Qiniu;

class RS
{
    private $bucket = null;

    private $conf = array();

    private $confTemplate = array(
        'accessKey' => null,
        'secretKey' => null,
        'host'      => array(
            'up'   => 'up.qiniu.com',
            'rs'   => 'rs.qbox.me',
            'rsf'  => 'rsf.qbox.me',
        ),
        'httpUriSuffix' => '.qiniudn.com',
        'httpsUriPrefix' => 'dn-',
        'httpsUriSuffix' => '.qbox.me', //http://kbkb.qiniudn.com/https-support
        'customDomain' => null,
    );

    public function __construct($conf)
    {
        $this->setConf($conf);
    }

    /**
     * 删除文件，要auth认证
     * @example shell curl -i -H 'Authorization: QBox asdf' 'http://rs.qbox.me/delete/asdf'
     * @return boolean
     */
    public function deleteFile($remoteFileName)
    {
        $remoteFileName = str_replace('/', '', $remoteFileName);
        $uri = 'http://' . str_replace('//', '/', $this->conf['host']['rs'] . '/delete/') . $this->encode($this->bucket . ':' . $remoteFileName);
        $policy =  array('scope' => $this->bucket, 'deadline' => time() + 3600);
        $tmp = parse_url($uri);
        $auth = $this->sign($tmp['path'] . "\n");

        $http = new \HTTPRequest($uri, HTTP_METH_POST);
        $http->addHeaders(array('Authorization' => 'QBox ' . $auth));
        $r = $http->send();
        $body = json_decode($http->getResponseBody(), true);
        $code = $http->getResponseCode();
        //612是文件不存在
        if ($code == 200 || $code == 612) {
            return true;
        }
        throw new Exception($body['error'], $code);
    }
    
    private function encode($str)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($str));
    }

    /**
     * 上传文件，要token认证
     * @example shell curl -i -F 'file=@2.jpg' -F 'token=asdf' -F 'key=2.jpg' 'http://up.qiniu.com/' 
     * @example shell ./qrsync ./conf.json
     * @return array array(
            "httpUri" => "http://com-163-sinkcup-test.qiniudn.com/1.jpg",
            "httpsUri" => "https://dn-com-163-sinkcup-test.qbox.me/1.jpg",
        }
     */
    public function uploadFile($localPath, $remoteFileName, $headers = array())
    {
        $remoteFileName = str_replace('/', '', $remoteFileName);
        $uri = 'http://' . str_replace('//', '/', $this->conf['host']['up'] . '/');
        //scope中指定文件，就可以覆盖。如果只写bucket，则重复上传会出现错误：614 文件已存在。
        $policy =  array('scope' => $this->bucket . ':' . $remoteFileName, 'deadline' => time() + 3600);
        $data = $this->encode(json_encode($policy));
        $token = $this->sign($data) . ':' . $data;

        //$hash = hash_file('crc32b', $localPath);
        //$tmp = unpack('N', pack('H*', $hash));
        $fields = array(
            'token' => $token,
            'key'   => $remoteFileName,
            //'crc32' => sprintf('%u', $tmp[1]),
        );
        $http = new \HTTPRequest($uri, HTTP_METH_POST);
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'multipart/form-data';
        $http->addPostFile('file', $localPath, $contentType);
        $http->addPostFields($fields);
        //$http->setHeader($headers);
        $http->send();
        $body = json_decode($http->getResponseBody(), true);
        $code = $http->getResponseCode();
        if ($code == 200) {
            //自定义域名一定是http，因为证书不能跨域名
            if (empty($this->conf['customDomain'])) {
                $httpUri = 'http://' . str_replace('//', '/', $this->bucket . $this->conf['httpUriSuffix'] . '/' . $body['key']);
            } else {
                $httpUri = 'http://' . $this->conf['customDomain'] . '/' . $body['key'];
            }
            return array(
                'httpUri'  => $httpUri,
                'httpsUri' => 'https://' . str_replace('//', '/', $this->conf['httpsUriPrefix'] . $this->bucket . $this->conf['httpsUriSuffix'] . '/' . $body['key']),
            );
        }
        throw new Exception($body['error'], $code);
    }

    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
        return true;
    }

    public function setConf($conf)
    {
        if (isset($conf['bucket'])) {
            $this->setBucket($conf['bucket']);
        }
        $this->conf = array_merge($this->confTemplate, $conf);
        return true;
    }

    private function sign($data)
    {
        return $this->conf['accessKey'] . ':' . $this->encode(hash_hmac('sha1', $data, $this->conf['secretKey'], true));
    }
}
