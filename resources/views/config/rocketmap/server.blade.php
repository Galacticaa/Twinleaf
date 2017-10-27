@php
extract($config->toArray())
@endphp
host: 0.0.0.0
port: {{ 8000 + $map->id }}

gmaps-key: {{ $gmaps_key }}

location: {{ $map->location }}

gym-info
only-server
print-status: logs

@if ($captcha_solving !== null)
captcha-solving
manual-captcha-domain: {{ $map->url }}
@endif
@if ($captcha_solving === 1 && $captcha_key)
captcha-key: {{ $captcha_key }}
@endif

db-type: mysql
db-host: localhost
db-name: {{ $map->db_name }}
db-user: {{ $map->db_user }}
db-pass: {{ $map->db_pass }}
db-port: 3306
db-threads: 1
