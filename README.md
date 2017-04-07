# elastic-bridge

Use composer to install dependecies:
```
composer install --no-dev
```

Setup crontab command:
```
* * * * * sh /var/www/html/run.sh >> /dev/null 2>&1
```
---

Or run bridge container with docker:
```
$ docker run -d -p 80:80 --link elasticsearch --link photobank-db --name elastic-bridge -v "$PWD":/var/www/html php:7.0-apache
```

Enter container:
```
docker exec -i -t elastic-bridge /bin/bash
```
Then install dependencies:
```
composer install --no-dev
```
Then run bridge manually:
```
sh /var/www/html/run.sh
```

Or setup crontab command:
```
* * * * * docker exec elastic-bridge sh /var/www/html/run.sh >> /dev/null 2>&1
```
