<?php


//function Aes Encrypt 
function aes_encrypt($data) {
    $cipher = "aes-256-cbc";
    $key = get_field('aes_key', 'option'); 
    $iv = get_field('aes_iv', 'option'); 
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    return base64_encode($encrypted);
}

//function Aes Decrypt
function aes_decrypt($data, $key, $iv) {
    $cipher = "aes-256-cbc";
    $key = get_field('aes_key', 'option'); 
    $iv = get_field('aes_iv', 'option'); 
    $decrypted = openssl_decrypt(base64_decode($data), $cipher, $key, 0, $iv);
    return $decrypted;
}



