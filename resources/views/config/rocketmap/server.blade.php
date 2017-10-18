host: 0.0.0.0
port: {{ 8000 + $map->id }}

gmaps-key: {{ $config->keys->gmaps }}

location: {{ $map->location }}

gym-info
only-server
print-status: logs

captcha-solving
manual-captcha-domain: {{ $map->url }}

db-type: mysql
db-host: localhost
db-name: {{ $map->db_name }}
db-user: {{ $map->db_user }}
db-pass: {{ $map->db_pass }}
db-port: 3306
db-threads: 1
