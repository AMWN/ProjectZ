<?php

return array(
    /*
      |--------------------------------------------------------------------------
      | Application Debug Mode
      |--------------------------------------------------------------------------
      |
      | When your application is in debug mode, detailed error messages with
      | stack traces will be shown on every error that occurs within your
      | application. If disabled, a simple generic error page is shown.
      |
     */

    'debug' => true,
    'settings' => array(
        'general' => array(
            'aol' => 1
        ),
        'profitlocal' => array(
            'username' => 'demo',
            'password' => '',
            'environmentid' => 'EA_10_24',
            'url' => 'https://192.168.1.230/profitservices/'
        ),
        'profitaol' => array(
            'username' => '',
            'password' => '',
            'environmentid' => '',
            'url' => 'https://profitweb.afasonline.nl/profitservices/'
        )
    )
);
