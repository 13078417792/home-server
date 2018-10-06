<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/5
 * Time: 22:20
 */

//namespace crypt;


class crypt {

    static private $authCodeKey = '6dec3ed0409ae753f8913bbe372eeb59';

    static function authCode($input, $key) {

        # Input must be of even length.
        if (strlen($input) % 2) {
            //$input .= '0';
        }

        # Keys longer than the input will be truncated.
        if (strlen($key) > strlen($input)) {
            $key = substr($key, 0, strlen($input));
        }

        # Keys shorter than the input will be padded.
        if (strlen($key) < strlen($input)) {
            $key = str_pad($key, strlen($input), '0', STR_PAD_RIGHT);
        }

        # Now the key and input are the same length.
        # Zero is used for any trailing padding required.

        # Simple XOR'ing, each input byte with each key byte.
        $result = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $result .= $input{$i} ^ $key{$i};
        }
        return $result;
    }

    /**
     * 加密
     */
    static function encrypt($sessionId) {

        $hashKey = self::base64url_encode(self::authCode($sessionId, self::$authCodeKey));
        $hashKey = self::base64url_encode($sessionId);
        return $hashKey;
    }


    /**
     * 解密
     */
    static function decrypt($hashKey) {

        $authCodeKey = 'khUvFB9pijNyCYMGZdzqeKalyg7dh';
        $sessionId = self::authCode(self::base64url_decode($hashKey), self::$authCodeKey);
        $sessionId = self::base64url_decode($hashKey);
        return $sessionId;
    }

    // url传输需要替换部分字符
    static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    // url传输需要替换部分字符
    static function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}