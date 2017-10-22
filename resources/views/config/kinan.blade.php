# 2 Captcha API Key
twoCaptcha.key={{ $config->captcha_key }}

# List of proxies
#proxies=
#proxy=
# Dump results 0=never, 1=onError, 2=Always
dumpResult=1

# Cancel 2captcha requests longer then 600s
captchaMaxTotalTime=600

# Use a custom time frame for rolling policy evey 11 minutes instead of 16 minutes
# proxyPolicy.custom.period=11

# Prevent from using the same IP twice in less then N seconds (default to 15s)
# proxy.bottleneck=15

#dbHost=localhost
dbHost={{ $database->host }}
dbName={{ $database->database }}
dbUser={{ $database->username }}
dbPass={{ $database->password }}
dbPort={{ $database->port }}
