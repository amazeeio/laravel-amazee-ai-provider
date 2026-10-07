<?php

// Copy into the "providers" array of config/ai.php to customise.
return [
    'driver' => 'amazeeio',
    'url' => env('AMAZEE_ENDPOINT_URL'),
    'key' => env('AMAZEE_LLM_TOKEN'),
];
