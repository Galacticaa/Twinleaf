hash-key: {{ $config->hash_key }}
gmaps-key: {{ $config->gmaps_key }}

location: {{ $area->location ?? '42.421,141.1' }}
status-name: {{ str_replace('&', 'and', $area->name) }}
@if ($area->radius)
step-limit: {{ $area->radius }}
@endif

@if ($area->speed_scan)
speed-scan
@endif
@if ($area->beehive)
beehive
@endif
@if ($area->geofence)
geofence-file: geofences/{{ $area->map->code }}_{{ $area->slug }}.csv
@endif
@if ($area->workers)
workers: {{ $area->workers }}
@endif
@if ($area->workers_per_hive)
workers-per-hive: {{ $area->workers_per_hive }}
@endif

accountcsv: config/{{ $area->map->code }}/{{ $area->slug }}.csv
@if ($config->login_delay)
login-delay: {{ $config->login_delay }}
@endif
@if ($config->login_retries)
login-retries: {{ $config->login_retries }}
@endif
@if ($area->scan_duration)
account-search-interval: {{ $area->scan_duration * 60 }}
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

gym-info
no-server
print-status: logs
@if ($config->altitude_cache)
use-altitude-cache
@endif
min-seconds-left: 15
@if ($config->long_lures)
lure-duration: 360
@endif
@if ($area->spin_pokestops)
pokestop-spinning
@if ($area->max_stop_spins)
account-max-spins: {{ $area->max_stop_spins }}
@endif
@endif

proxy-file: config/{{ $area->map->code }}/{{ $area->slug }}.txt
proxy-display: full

@if ($config->automatic_captchas || $config->manual_captchas)
captcha-solving
@endif
@if ($config->automatic_captchas)
captcha-key: {{ $config->captcha_key }}
@endif
@if ($config->manual_captchas)
manual-captcha-domain: {{ $area->map->url }}
@if ($config->captcha_refresh)
manual-captcha-refresh: {{ $config->captcha_refresh }}
@endif
@if ($config->captcha_timeout)
manual-captcha-timeout: {{ $config->captcha_timeout }}
@endif
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

@if ($config->disable_version_check)
no-version-check
@endif
