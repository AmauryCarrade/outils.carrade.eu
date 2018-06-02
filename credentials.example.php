<?php
return array(
    // SHA256
    'upload' => array(
        '88f47a779957555ec5c813f74e19abd5d7093cc0c5eab4944b19f054b1116e13'
    ),

    // SHA256, permet de voir les serveurs cachés dans la liste sur /stats?key=<clef>
    'stats' => array(
        '88f47a779957555ec5c813f74e19abd5d7093cc0c5eab4944b19f054b1116e13'
    ),

    // Connection (PDO object) retrieved using get_db_connector($app, 'connection_name')
    // Required: 'mcstats'; optional: 'steam'
    'sgbd' => array(
        'connection_name' => array(
            'host' => '',
            'user' => '',
            'pass' => '',
            'base' => ''
        )
    ),

    'zcraft_profile_salt' => '',
    'zcraft_profile_key'  => '',
    'zcraft_profile_permanent_keys' => array(),

    'manager_raw_files_keys' => array(),

    'steam_access_key' => '',

    // IDs
    'steam_favorites' => [
        'steam' => [],
        'discord' => []
    ]
);
