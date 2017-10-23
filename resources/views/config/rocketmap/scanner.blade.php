hash-key: {{ $config->hash_key }}
gmaps-key: {{ $config->gmaps_key }}

location: {{ $area->location ?? '42.421,141.1' }}
@if ($area->radius)
step-limit: {{ $area->radius }}
@endif

status-name: {{ $area->name }}
accountcsv: config/{{ $area->map->code }}/{{ $area->slug }}.csv
proxy-file: config/{{ $area->map->code }}/{{ $area->slug }}.txt


@if ($area->speed_scan)
speed-scan
@endif
@if ($area->beehive)
beehive
@endif

@if ($area->workers)
workers: {{ $area->workers }}
@endif
@if ($area->workers_per_hive)
workers-per-hive: {{ $area->workers_per_hive }}
@endif

@if ($area->scan_duration)
account-scan-interval: {{ $area->scan_duration * 60 }}
@endif
@if ($area->rest_interval)
account-rest-interval: {{ $area->rest_interval * 60 }}
@endif
@if ($area->max_empty)
max-empty: {{ $area->max_empty }}
@endif
@if ($area->max_failures )
max-failures: {{ $area->max_failures }}
@endif
@if ($area->max_retries)
bad-scan-retry: {{ $area->max_retries }}
@endif

gym-info
no-server
print-status: logs
#use-altitude-cache

@if ($config->captcha_solving !== null)
captcha-solving
manual-captcha-domain: {{ $area->map->url }}
@endif
@if ($config->captcha_solving === 1 && $config->captcha_key)
captcha-key: {{ $config->captcha_key }}
@endif

db-type: mysql
db-host: localhost
db-name: {{ $area->map->db_name }}
db-user: {{ $area->map->db_user }}
db-pass: {{ $area->map->db_pass }}
db-port: 3306
@if ($area->db_threads)
db-threads: {{ $area->db_threads }}
@endif
