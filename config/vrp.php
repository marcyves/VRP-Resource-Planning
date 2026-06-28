<?php

return [
    'allow_registration' => (bool) env('VRP_ALLOW_REGISTRATION', false),

    'account_request_email' => env('VRP_ACCOUNT_REQUEST_EMAIL', env('MAIL_FROM_ADDRESS')),
];
