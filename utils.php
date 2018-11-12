<?php

function config($param) {
    $exploded = explode('.', $param);
    if(count($exploded) != 2)
        return null;

    $filename = __DIR__ . '/config/' . $exploded[0] . '.php';
    if(is_file($filename)) {
        $loaded_config = include $filename;
        if(array_key_exists($exploded[1], $loaded_config))
            return $loaded_config[$exploded[1]];
    }

    return null;
}

function success($params=array()) {
    unset($_SESSION["captcha"]);
    $params['success'] = true;
    die(json_encode($params));
}

function error($params=array()) {
    unset($_SESSION["captcha"]);
    $params['success'] = false;
    die(json_encode($params));
}