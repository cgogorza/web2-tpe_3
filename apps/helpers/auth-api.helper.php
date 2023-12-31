<?php

class AuthApiHelper {

    function getToken(){
        //$header.$payload.$signature

        $auth = $this->getAuthHeader(); 
        $auth = explode(" ", $auth); // [$header, $payload, $signature]

        if($auth[0]!="Bearer" || count($auth) != 2){
            return array();
        }
        $token = explode(".", $auth[1]); 
        $header = $token[0];
        $payload = $token[1];
        $signature = $token[2];

        $new_signature = hash_hmac('SHA256', "$header.$payload", "Clave1234", true);
        $new_signature = base64url_encode($new_signature);

        //se chequea que las firmas coincidan
        if($signature!=$new_signature)
            return array();

        //si es correcto lo descompongo
        $payload = json_decode(base64_decode($payload));
        if(!isset($payload->exp) || $payload->exp<time())
            return array();
        
        return $payload; //se lo retorno al controller
    }

    function isLoggedIn(){
        $payload = $this->getToken();
        if(isset($payload->id))
            return true;
        else
            return false;
    }

    function getAuthHeader(){
        $header = "";
        if(isset($_SERVER['HTTP_AUTHORIZATION']))
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        return $header;
    }
}