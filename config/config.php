<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    // Enable/Disable debug
    'debug' => env('APP_DEBUG', false),

    // Available log drivers: syslog, stderr, stdout or file
    'log_driver' => env('APP_LOG', 'file'),

    // Available cache drivers are "file", "memory" and "memcached"
    'cache_driver' => 'memory',

    // Hide login form, useful if all your users use Google/Github/ReverseProxy authentication
    'hide_login_form' => false,

    // Enable/disable url rewrite
    'enable_url_rewrite' => true,

    // Available db drivers are "mysql", "sqlite" and "postgres"
    'db_driver' => env('DB_CONNECTION', 'mysql'),

    'db_connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => 'jitamin',
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'jitamin'),
            'username'  => env('DB_USERNAME', 'jitamin'),
            'password'  => env('DB_PASSWORD', ''),
            'port'      => env('DB_PORT', '3306'),
            'charset'   => 'utf8',
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'jitamin'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'port'     => '5432',
            'charset'  => 'utf8',
        ],
    ],

    //放假设定
    'specday' => [
        2020=>[
            9=>[27=>0],
            10=>[1=>1,2=>1,5=>1,6=>1,7=>1,8=>1,10=>0],
        ],
        2021=>[
            1=>[1=>1],
            2=>[6=>0,7=>0,8=>1,9=>1,10=>1,11=>1,12=>1,15=>1,16=>1,17=>1,18=>1,19=>1],
            4=>[5=>1,25=>0],
            5=>[3=>1,4=>1,5=>1,8=>0],
            6=>[14=>1],
            9=>[18=>0,20=>1,21=>1,26=>0],
            10=>[1=>1,4=>1,5=>1,6=>1,7=>1,9=>0],
        ],
    ],
];
