<?php

namespace App\Logging;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class ElasticsearchHandler extends AbstractProcessingHandler
{
    protected $client;
    protected $index;

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->build();

        $this->index = config('elasticsearch.index_prefix') . '_logs';
    }

    protected function write(LogRecord $record): void
    {
        $this->client->index([
            'index' => $this->index . '_' . date('Y_m_d'),
            'body' => [
                'message' => $record->message,
                'level' => $record->level->getName(),
                'level_name' => $record->level->getName(),
                'channel' => $record->channel,
                'context' => $record->context,
                'extra' => $record->extra,
                'datetime' => $record->datetime->format('Y-m-d H:i:s'),
                'timestamp' => $record->datetime->getTimestamp(),
            ],
        ]);
    }
}
