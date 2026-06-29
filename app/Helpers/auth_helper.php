<?php

if (!function_exists('getAuthSessionkey')) {
    function getAuthSessionkey($user)
    {
        if (!empty($user)) {
            return hash('sha256', $user['password'] . $user['id']);
        }
        return null;
    }

    if (!function_exists('generateAuthToken')) {
        function generateAuthToken($short = false)
        {
            $token = uniqid('', TRUE);
            $token = strReplace('.', '-', $token);
            if ($short == false) {
                $token = $token . '-' . rand(10000000, 99999999);
            }
            return $token;
        }
    }
}
