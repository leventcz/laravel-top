<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redis Connection
    |--------------------------------------------------------------------------
    |
    | Specify the Redis database connection from config/database.php
    | that Top will use to save data.
    | The default value is suitable for most applications.
    |
    */

    'connection' => env('TOP_REDIS_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Recording Mode
    |--------------------------------------------------------------------------
    |
    | Determine when Top should record application metrics based on this value.
    | By default, Top only listens to your application when it is running.
    | If you want to access metrics through the facade, you can select the "always" mode.
    |
    | Available Modes: "runtime", "always"
    |
    */

    'recording_mode' => env('TOP_RECORDING_MODE', 'runtime'),
];
