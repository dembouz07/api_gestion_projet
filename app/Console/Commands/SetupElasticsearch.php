<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Elastic\Elasticsearch\ClientBuilder;

class SetupElasticsearch extends Command
{
    protected $signature = 'elasticsearch:setup';
    protected $description = 'Setup Elasticsearch indexes for the application';

    public function handle()
    {
        try {
            $this->info('🔍 Connecting to Elasticsearch...');

            $client = ClientBuilder::create()
                ->setHosts(config('elasticsearch.hosts'))
                ->build();

            // Test connection
            $client->ping();
            $this->info('✅ Connected to Elasticsearch');

            $indexPrefix = config('elasticsearch.index_prefix');
            $today = date('Y_m_d');

            // Define indexes to create
            $indexes = [
                "{$indexPrefix}_logs_{$today}",
                "{$indexPrefix}_metrics_{$today}",
                "{$indexPrefix}_user_activity_{$today}",
                "{$indexPrefix}_performance_{$today}",
            ];

            foreach ($indexes as $index) {
                // Check if index exists
                if ($client->indices()->exists(['index' => $index])->asBool()) {
                    $this->warn("⚠️  Index '{$index}' already exists");
                    continue;
                }

                // Create index with mappings
                $params = [
                    'index' => $index,
                    'body' => [
                        'settings' => [
                            'number_of_shards' => 1,
                            'number_of_replicas' => 0,
                        ],
                        'mappings' => [
                            'properties' => [
                                'timestamp' => ['type' => 'date'],
                                'user_id' => ['type' => 'integer'],
                                'message' => ['type' => 'text'],
                                'level' => ['type' => 'keyword'],
                                'action' => ['type' => 'keyword'],
                                'type' => ['type' => 'keyword'],
                            ],
                        ],
                    ],
                ];

                $client->indices()->create($params);
                $this->info("✅ Created index: {$index}");
            }

            $this->info('');
            $this->info('🎉 Elasticsearch setup completed successfully!');
            $this->info('');
            $this->info('You can now view your data at: http://localhost:5601/app/lens');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to setup Elasticsearch');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡 Make sure Elasticsearch is running on http://localhost:9200');

            return 1;
        }
    }
}
