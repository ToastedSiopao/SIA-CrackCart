<?php

function get_paypal_access_token($client_id, $client_secret) {
    if (isset($_SESSION['paypal_access_token']) && time() < $_SESSION['paypal_token_expires_at']) {
        return $_SESSION['paypal_access_token'];
    }

    $token_url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Accept-Language: en_US']);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $json_result = json_decode($result, true);

    if (isset($json_result['access_token'])) {
        $_SESSION['paypal_access_token'] = $json_result['access_token'];
        $_SESSION['paypal_token_expires_at'] = time() + $json_result['expires_in'] - 30;
        return $json_result['access_token'];
    }

    return null;
}

?>