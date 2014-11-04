<?php

function jsonResponse($status_str, $data_array) {
    return json_encode(array("status" => $status_str, "data" => $data_array));
}

// リクエストパラメータ内に欲しいものが全部存在しているか?
// $req_params: リクエストパラメータそのもの
// $check_params: チェックしたいパラメータ array(hoge1, hoge2, ...)
function issetAllParams($req_params, $check_params) {
    $req_param_keys = array_keys($req_params);

    // check empty
    if (!$req_param_keys) {
        return false;
    }

    foreach ($req_param_keys as $req_param_key) {
        // check parametar exist
        if (!in_array($req_param_key, $check_params)) {
            return false;
        }

        // check parametar not ""
        if ($req_params[$req_param_key] === "") {
            return false;
        }
    }

    return true;
}

function prePr($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}
