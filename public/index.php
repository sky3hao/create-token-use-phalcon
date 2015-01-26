<?php

error_reporting(E_ALL);
define('USER_TOKEN', 0x1);
define('IMAGE_TOKEN', 0x2);

try {

    $config = include __DIR__ . "/../app/config/config.php";
    include __DIR__ . '/../app/config/loader.php';
    include __DIR__ . "/../app/config/services.php";

    $app = new Phalcon\Mvc\Micro($di);

    $app->post('/token/get', function() use ($app) {
        $ip = $app->request->getPost('clientIP', 'string');
        $userId = $app->request->getPost('userId', 'int');
        $period = $app->request->getPost('period', 'int');
        $limit = $app->request->getPost('limit', 'int');

        if (0 == $period) {
            $period = 86400;
        }
        if ($limit < 0 || $limit > 100) {
            $limit = 0;
        }

        $expire = time() + $period;
        $cmd = USER_TOKEN;

        $returnContent = array(
            'ret'   => 0,
            'error' => null,
            'data'  => null
        );

        $tokenInfo = pack('CVCV2', $cmd, ip2long($ip), $limit, $expire, $userId);
        $tokenCrypt = $app->crypt->encrypt($tokenInfo);
        $uuKey = crc32($tokenCrypt);
        $hashKey = \Utils::base62_encode($uuKey);
        $app->tokenBk->save($uuKey, $tokenCrypt);

        $returnContent['data'] = array(
            'token' => $hashKey
        );
        $app->response->setJsonContent($returnContent);
        return $app->response;
    });

    $app->post('/ticket/get', function() use ($app) {
        $ip = $app->request->getPost('clientIP', 'string');
        $userId = $app->request->getPost('userId', 'int');
        $shopId = $app->request->getPost('shopId', 'int');
        $period = $app->request->getPost('period', 'int');
        $limit = $app->request->getPost('limit', 'int');
        $type = $app->request->getPost('type', 'string');
        $size = $app->request->getPost('size', 'string');
        $width = $app->request->getPost('width', 'string');
        $height = $app->request->getPost('height', 'string');
        if (0 == $period) {
            $period = 86400;
        }
        if ($limit < 0 || $limit > 100) {
            $limit = 0;
        }
        if($size > 10485760) {	// 最大10M
        	$size = 10485760;
        }

        $expire = time() + $period;
        $cmd = IMAGE_TOKEN;

        $returnContent = array(
            'ret'   => 0,
            'error' => null,
            'data'  => null
        );

        do {
            if (empty($period)) {
                $returnContent['ret'] = Errors::TOKEN_IMAGE_NO_TYPE;
                $returnContent['error'] = Errors::getErrorMessage(Errors::TOKEN_IMAGE_NO_TYPE);
                break;
            }
            $tokenInfo = pack('CVCV6a*', $cmd, ip2long($ip), $limit, $expire, $userId, $shopId, $size, $width, $height, $type);
            $tokenCrypt = $app->crypt->encrypt($tokenInfo);
            $uuKey = crc32($tokenCrypt);
            $hashKey = \Utils::base62_encode($uuKey);
            $app->tokenBk->save($uuKey, $tokenCrypt);

            $returnContent['data'] = array(
                'token' => $hashKey
            );

        } while (false);
        $app->response->setJsonContent($returnContent);
        return $app->response;
    });

    $app->post('/token/check', function() use ($app) {
        $ip = $app->request->getPost('clientIP', 'string');
        $hashKey = $app->request->getPost('token', 'alphanum');
        $uuKey = \Utils::base62_decode($hashKey);

        $returnContent = array(
            'ret'   => 0,
            'error' => null,
            'data'  => null
        );

        do {
            $tokenCrypt = $app->tokenBk->get($uuKey);
            if (null == $tokenCrypt)  {
                $returnContent['ret'] = Errors::TOKEN_ILLEGAL;
                $returnContent['error'] = Errors::getErrorMessage(Errors::TOKEN_ILLEGAL);
                break;
            }
            $tokenInfo = $app->crypt->decrypt($tokenCrypt);
            $tokenHead = unpack('Ccmd/Vip/Climit/Vexpire', $tokenInfo);

            if ($tokenHead['expire'] < time()) {
                $returnContent['ret'] = Errors::TOKEN_EXPIRED;
                $returnContent['error'] = Errors::getErrorMessage(Errors::TOKEN_EXPIRED); 
                break;
            }
            $tokenBody = substr($tokenInfo, 10);
            if (0 != $tokenHead['limit']) {
                if ($tokenHead['limit'] > 1) {
                    $tokenHead['limit'] -= 1;
                    $tokenInfo = pack('CVCV', $tokenHead['cmd'], $tokenHead['ip'], $tokenHead['limit'], $tokenHead['expire']) .  $tokenBody;
                    $app->tokenBk->save($uuKey, $tokenCrypt);
                } else {
                    $app->tokenBk->delete($uuKey);
                }
            }

            switch ($tokenHead['cmd']) {
            case USER_TOKEN:
                $userData = unpack('VuserId', $tokenBody);
                $returnContent['data'] = $userData;
                break;
            case IMAGE_TOKEN:
                $imageData = unpack('VuserId/VshopId/Vsize/Vwidth/Vheight/a*type', $tokenBody);
                $returnContent['data'] = $imageData;
                break;
            default:
                $returnContent['ret'] = Errors::TOKEN_INVALID;
                $returnContent['error'] = Errors::getErrorMessage(Errors::TOKEN_INVALID);
                break;
            }
       } while (false);

        $app->response->setJsonContent($returnContent);
        return $app->response;
    });

    $app->notFound(function () use ($app) {
        $app->response->setStatusCode('403', 'Not Allowed')->sendHeaders();
        echo 'Not Allowed';
    });
    $app->handle();


} catch (\Exception $e) {
    echo $e->getMessage();
}
