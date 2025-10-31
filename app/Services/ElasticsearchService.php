<?php

namespace App\Services;

use App\Models\User;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class ElasticsearchService
{
    protected $client;
    protected $indexPrefix;
    protected $enabled;

    public function __construct()
    {
        // Check if Elasticsearch is enabled
        $this->enabled = env('LOG_ELASTICSEARCH_ENABLED', false);

        if (!$this->enabled) {
            $this->client = null;
            return;
        }

        try {
            // Create client builder
            $builder = ClientBuilder::create()
                ->setHosts(config('elasticsearch.hosts'));

            // Try to set headers if method exists (for newer versions)
            if (method_exists($builder, 'setHeaders')) {
                $builder->setHeaders([
                    'Accept' => 'application/vnd.elasticsearch+json; compatible-with=8',
                    'Content-Type' => 'application/vnd.elasticsearch+json; compatible-with=8'
                ]);
            } elseif (method_exists($builder, 'setHeader')) {
                $builder->setHeader('Accept', 'application/vnd.elasticsearch+json; compatible-with=8');
                $builder->setHeader('Content-Type', 'application/vnd.elasticsearch+json; compatible-with=8');
            }

            $this->client = $builder->build();
            $this->indexPrefix = config('elasticsearch.index_prefix');

            // Test connection with timeout
            $this->client->ping();
            Log::info('Elasticsearch connection established');
        } catch (\Exception $e) {
            Log::warning('Elasticsearch unavailable, logging disabled', [
                'error' => $e->getMessage()
            ]);
            $this->client = null;
            $this->enabled = false;
        }
    }

    /**
     * Check if Elasticsearch is available
     */
    protected function isAvailable(): bool
    {
        return $this->enabled && $this->client !== null;
    }

    /**
     * Log une métrique personnalisée
     */
    public function logMetric(string $type, array $data)
    {
        if (!$this->isAvailable()) {
            return;
        }

        try {
            $params = [
                'index' => $this->indexPrefix . '_metrics_' . date('Y_m_d'),
                'body' => array_merge([
                    'type' => $type,
                    'timestamp' => now()->toIso8601String(),
                    'user_id' => auth()->id(),
                ], $data),
            ];

            $params['client'] = [
                'headers' => [
                    'Accept' => ['application/vnd.elasticsearch+json; compatible-with=8'],
                    'Content-Type' => ['application/vnd.elasticsearch+json; compatible-with=8']
                ],
                'timeout' => 2,
            ];

            $this->client->index($params);
        } catch (\Exception $e) {
            Log::debug('Elasticsearch metric logging failed', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log une activité utilisateur avec détails complets
     */
    public function logUserActivity(string $action, array $data = [])
    {
        if (!$this->isAvailable()) {
            return;
        }

        try {
            $user = auth()->user();

            $params = [
                'index' => $this->indexPrefix . '_user_activity_' . date('Y_m_d'),
                'body' => [
                    'action' => $action,
                    'user' => [
                        'id' => $user?->id,
                        'name' => $user?->name,
                        'email' => $user?->email,
                        'role' => $user?->role,
                    ],
                    'data' => $this->enrichLogData($action, $data),
                    'request' => [
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'method' => request()->method(),
                        'url' => request()->fullUrl(),
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ];

            $params['client'] = [
                'headers' => [
                    'Accept' => ['application/vnd.elasticsearch+json; compatible-with=8'],
                    'Content-Type' => ['application/vnd.elasticsearch+json; compatible-with=8']
                ],
                'timeout' => 2,
            ];

            $this->client->index($params);
        } catch (\Exception $e) {
            Log::debug('Elasticsearch activity logging failed', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enrichit les données de log selon le type d'action
     */
    protected function enrichLogData(string $action, array $data): array
    {
        $enriched = $data;

        // Enrichir les logs pour les projets
        if (str_contains($action, 'project') && isset($data['project'])) {
            $enriched['project_details'] = [
                'id' => $data['project']->id ?? null,
                'name' => $data['project']->name ?? null,
                'status' => $data['project']->status ?? null,
                'project_manager' => $data['project']->projectManager ? [
                    'id' => $data['project']->projectManager->id,
                    'name' => $data['project']->projectManager->name,
                ] : null,
            ];
            unset($enriched['project']);
        }

        // Enrichir les logs pour les user stories
        if (str_contains($action, 'user_story') && isset($data['user_story'])) {
            $enriched['user_story_details'] = [
                'id' => $data['user_story']->id ?? null,
                'title' => $data['user_story']->title ?? null,
                'status' => $data['user_story']->status ?? null,
                'sprint_id' => $data['user_story']->sprint_id ?? null,
                'product_backlog_id' => $data['user_story']->product_backlog_id ?? null,
            ];
            unset($enriched['user_story']);
        }

        // Enrichir les logs pour les tasks
        if (str_contains($action, 'task') && isset($data['task'])) {
            $enriched['task_details'] = [
                'id' => $data['task']->id ?? null,
                'title' => $data['task']->title ?? null,
                'status' => $data['task']->status ?? null,
                'user_story_id' => $data['task']->user_story_id ?? null,
                'assigned_to' => $data['task']->assignedUser ? [
                    'id' => $data['task']->assignedUser->id,
                    'name' => $data['task']->assignedUser->name,
                ] : null,
            ];
            unset($enriched['task']);
        }

        // Enrichir les logs pour les sprints
        if (str_contains($action, 'sprint') && isset($data['sprint'])) {
            $enriched['sprint_details'] = [
                'id' => $data['sprint']->id ?? null,
                'number' => $data['sprint']->number ?? null,
                'objective' => $data['sprint']->objective ?? null,
                'start_date' => $data['sprint']->start_date ?? null,
                'deadline' => $data['sprint']->deadline ?? null,
                'project_id' => $data['sprint']->project_id ?? null,
            ];
            unset($enriched['sprint']);
        }

        // Enrichir les logs pour les notifications
        if (str_contains($action, 'notification') && isset($data['notification'])) {
            $enriched['notification_details'] = [
                'id' => $data['notification']->id ?? null,
                'object' => $data['notification']->object ?? null,
                'message' => $data['notification']->message ?? null,
                'recipient' => $data['notification']->user ? [
                    'id' => $data['notification']->user->id,
                    'name' => $data['notification']->user->name,
                ] : null,
            ];
            unset($enriched['notification']);
        }

        // Enrichir les logs pour les messages
        if (str_contains($action, 'message') && isset($data['message'])) {
            $enriched['message_details'] = [
                'id' => $data['message']->id ?? null,
                'content_preview' => substr($data['message']->content ?? '', 0, 100),
                'chat_project_id' => $data['message']->chat_project_id ?? null,
                'sender' => $data['message']->user ? [
                    'id' => $data['message']->user->id,
                    'name' => $data['message']->user->name,
                ] : null,
            ];
            unset($enriched['message']);
        }

        // Enrichir les logs pour les utilisateurs
        if (str_contains($action, 'user') && isset($data['target_user'])) {
            $enriched['target_user_details'] = [
                'id' => $data['target_user']->id ?? null,
                'name' => $data['target_user']->name ?? null,
                'email' => $data['target_user']->email ?? null,
                'role' => $data['target_user']->role ?? null,
            ];
            unset($enriched['target_user']);
        }

        // Enrichir les logs pour les changements (before/after)
        if (isset($data['changes'])) {
            $enriched['changes'] = $data['changes'];
        }

        return $enriched;
    }

    /**
     * Log une performance
     */
    public function logPerformance(string $operation, float $duration, array $context = [])
    {
        if (!$this->isAvailable()) {
            return;
        }

        try {
            $params = [
                'index' => $this->indexPrefix . '_performance_' . date('Y_m_d'),
                'body' => [
                    'operation' => $operation,
                    'duration_ms' => $duration,
                    'context' => $context,
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ];

            $params['client'] = [
                'headers' => [
                    'Accept' => ['application/vnd.elasticsearch+json; compatible-with=8'],
                    'Content-Type' => ['application/vnd.elasticsearch+json; compatible-with=8']
                ],
                'timeout' => 2,
            ];

            $this->client->index($params);
        } catch (\Exception $e) {
            Log::debug('Elasticsearch performance logging failed', [
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Search logs by criteria
     */
    public function searchLogs(array $criteria, string $indexType = 'logs')
    {
        if (!$this->isAvailable()) {
            return ['hits' => ['hits' => []]];
        }

        try {
            $params = [
                'index' => $this->indexPrefix . '_' . $indexType . '_*',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => $criteria
                        ]
                    ],
                    'sort' => [
                        'timestamp' => ['order' => 'desc']
                    ],
                    'size' => 100,
                ],
            ];

            return $this->client->search($params);
        } catch (\Exception $e) {
            Log::warning('Elasticsearch search failed', [
                'error' => $e->getMessage()
            ]);
            return ['hits' => ['hits' => []]];
        }
    }
}
