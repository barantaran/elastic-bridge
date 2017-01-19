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

Run bridge:
```
php run_index_all.php
```
