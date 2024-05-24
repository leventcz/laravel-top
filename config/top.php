<?php

return [
    /*
     * Provide a redis connection from config/database.php
    */
    'connection' => env('TOP_REDIS_CONNECTION', 'default'),

    /*
     * The time (in seconds) that data will be recorded and aggregated
    */
    'recording_time' => env('TOP_RECORDING_TIME', 5),
];
