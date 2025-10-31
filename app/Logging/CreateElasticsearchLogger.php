<?php

namespace App\Logging;

use Monolog\Logger;

class CreateElasticsearchLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('elasticsearch');
        $logger->pushHandler(new ElasticsearchHandler());

        return $logger;
    }
}
