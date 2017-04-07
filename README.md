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
sh /var/www/html/run.sh
```

Or setup crontab command:
```
* * * * * docker exec elastic-bridge sh /var/www/html/run.sh >> /dev/null 2>&1
```
