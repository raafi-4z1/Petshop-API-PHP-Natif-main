<?php
    // ! Set your server key (Note: Server key for sandbox and production mode are different)
    $server_key = '<server key>';

    // * Set true for production, set false for sandbox
    $is_production = false;
    $url_production = 'https://app.midtrans.com/snap/v1/transactions';
    $url_sandbox = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $api_url = $is_production ? $url_production : $url_sandbox;