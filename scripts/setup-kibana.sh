#!/bin/bash

KIBANA_URL="http://localhost:5601"
ELASTICSEARCH_URL="http://localhost:9200"

# Attendre que Kibana soit prêt
echo "Waiting for Kibana to be ready..."
until $(curl --output /dev/null --silent --head --fail $KIBANA_URL); do
    printf '.'
    sleep 5
done
echo "Kibana is ready!"

# Créer les index patterns
curl -X POST "$KIBANA_URL/api/saved_objects/index-pattern/scrum_app_logs" \
  -H 'kbn-xsrf: true' \
  -H 'Content-Type: application/json' \
  -d '{
    "attributes": {
      "title": "scrum_app_logs_*",
      "timeFieldName": "timestamp"
    }
  }'

curl -X POST "$KIBANA_URL/api/saved_objects/index-pattern/scrum_app_metrics" \
  -H 'kbn-xsrf: true' \
  -H 'Content-Type: application/json' \
  -d '{
    "attributes": {
      "title": "scrum_app_metrics_*",
      "timeFieldName": "timestamp"
    }
  }'

curl -X POST "$KIBANA_URL/api/saved_objects/index-pattern/scrum_app_user_activity" \
  -H 'kbn-xsrf: true' \
  -H 'Content-Type: application/json' \
  -d '{
    "attributes": {
      "title": "scrum_app_user_activity_*",
      "timeFieldName": "timestamp"
    }
  }'

curl -X POST "$KIBANA_URL/api/saved_objects/index-pattern/scrum_app_performance" \
  -H 'kbn-xsrf: true' \
  -H 'Content-Type: application/json' \
  -d '{
    "attributes": {
      "title": "scrum_app_performance_*",
      "timeFieldName": "timestamp"
    }
  }'

echo "Index patterns created successfully!"
