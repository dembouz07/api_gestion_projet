<?php

return [
    'hosts' => json_decode(env('ELASTICSEARCH_HOSTS', '["http://localhost:9200"]'), true),
    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'gestion_projet'),
];
