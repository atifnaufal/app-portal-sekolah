web: bash start-web.sh
reverb: php artisan reverb:start --host=0.0.0.0 --port=${REVERB_SERVER_PORT:-6001}
worker: php artisan queue:work --sleep=1 --tries=3
