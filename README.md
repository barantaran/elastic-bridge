# elastic-bridge

Run bridge container with docker:
```
$ docker run -d -p 80:80 --link elasticsearch --link photobank-db --name elastic-bridge -v "$PWD":/var/www/html php:7.0-apache
```

Enter container:
```
docker exec -i -t elastic-bridge /bin/bash
```
Install git:
```
$ apt-get update
$apt-get install git
```
Install [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) then install dependencies:
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
