# Benchmarking Symfony Standard

We, developers, *love* meaningless benchmarks.
Here's one for the [Symfony Standard Edition](https://github.com/symfony/symfony-standard).

## Usage

First prepare the environment:

    rm -rf var/cache/* var/logs/* vendor
    composer install -o --no-dev
    bin/console cache:clear -e=prod --no-debug
    REACT_PORT=1337 php bin/react.php
    REACT_PORT=1338 php bin/react.php
    REACT_PORT=1339 php bin/react.php
    REACT_PORT=1340 php bin/react.php
    curl http://bench-sf-standard.example.com/

And use [Apache Benchmark](https://httpd.apache.org/docs/2.2/programs/ab.html)
for 10 seconds with 10 concurrent clients:

    ab -c 10 -t 10 'http://bench-sf-standard.example.com/'

## Results

| Metric                                            | Value        |
|---------------------------------------------------|--------------|
| Requests per second                               | 3730.07#/sec |
| Time per request                                  | 2.681ms      |
| Time per request (across all concurrent requests) | 0.268ms      |

> Benchmarks run with:
>
> * PHP 7 (`7.0.4-7+deb.sury.org~trusty+2`)
>   with [Zend OPcache](http://php.net/manual/en/book.opcache.php) enabled
>   and *without* [Xdebug](https://xdebug.org/)
> * Linux 3.13.0-83-generic, Ubuntu 14.04.4 LTS, x86_64
> * [Lenovo Yoga 13](http://shop.lenovo.com/il/en/laptops/lenovo/yoga/yoga-13/#tab-tech_specs), with core i7

> **Note**: Profiling using blackfire failed.

## Web Server Configuration

Since [PHP built in server](http://php.net/manual/en/features.commandline.webserver.php)
runs on a single-threaded process, we need to use something else for our benchmarks.

We've picked [nginx](https://www.nginx.com/) with [PHP-FPM](http://php-fpm.org/),
but feel free to use another one. Here's the configuration used:

```
upstream workers {
    server localhost:1337;
    server localhost:1338;
    server localhost:1339;
    server localhost:1340;
}

server {
    listen 80;
    server_name bench-sf-standard.example.com;
    root /home/gnucat/Projects/tmp/bench-sf-standard/web;

    location / {
        proxy_pass http://workers;
    }

    error_log /home/gnucat/Projects/tmp/bench-sf-standard/var/logs/nginx_error.log;
    access_log /home/gnucat/Projects/tmp/bench-sf-standard/var/logs/nginx_access.log;
}
```
