# elastic-bridge

Install:
```
composer install --no-dev
```

Run bridge container with docker:
```
$ docker run -d -p 80:80 --link elasticsearch --link photobank-db --name elastic-bridge -v "$PWD":/var/www/html php:7.0-apache
```

Enter container:
```
docker exec -i -t elastic-bridge /bin/bash
```

Then run bridge manually:
```
$php run/run_reindex.php
$php run/run_index.php
```

Or setup crontab command:
```
*/2 * * * * docker exec elastic-bridge /usr/bin/php /var/www/html/run/run_reindex.php >> /dev/null 2>&1
1-59/2 * * * * docker exec elastic-bridge /usr/bin/php /var/www/html/run/run_index.php >> /dev/null 2>&1
```
