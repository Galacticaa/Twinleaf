hash-key: {{ $config->hash_key }}
gmaps-key: {{ $config->gmaps_key }}

location: {{ $area->location ?? '42.421,141.1' }}
@if ($area->radius)
step-limit: {{ $area->radius }}
@endif

status-name: {{ $area->name }}
accountcsv: config/{{ $area->map->code }}/{{ $area->slug }}.csv

gym-info
no-server
speed-scan
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
db-threads: 1
