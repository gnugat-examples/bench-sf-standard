# Benchmarking Symfony Standard

We, developers, *love* meaningless benchmarks.
Here's one for the [Symfony Standard Edition](https://github.com/symfony/symfony-standard).

## Usage

First prepare the environment:

    rm -rf var/cache/* var/logs/* vendor
    composer install -o --no-dev
    vendor/bin/aerys -c app/config/aerys.php
    curl http://bench-sf-standard.example.com/

And use [Apache Benchmark](https://httpd.apache.org/docs/2.2/programs/ab.html)
for 10 seconds with 10 concurrent clients:

    ab -c 10 -t 10 'http://bench-sf-standard.example.com/'

## Results

| Metric                                            | Value        |
|---------------------------------------------------|--------------|
| Requests per second                               | 1475.28#/sec |
| Time per request                                  | 6.778ms      |
| Time per request (across all concurrent requests) | 0.678ms      |

> Benchmarks run with:
>
> * PHP 7 (`7.0.3-5+deb.sury.org~trusty+1`)
>   with [Zend OPcache](http://php.net/manual/en/book.opcache.php) enabled
>   and *without* [Xdebug](https://xdebug.org/)
> * Linux 3.13.0-77-generic, Ubuntu 14.04 LTS, x86_64
> * [HP Compaq 8510p](http://www.cnet.com/products/hp-compaq-8510p-15-4-core-2-duo-t7700-vista-business-2-gb-ram-120-gb-hdd-series/specs/), with a SSD

> **Note**: Profiling using blackfire failed.

## Web Server Configuration

Since [PHP built in server](http://php.net/manual/en/features.commandline.webserver.php)
runs on a single-threaded process, we need to use something else for our benchmarks.

We've picked [nginx](https://www.nginx.com/) with [PHP-FPM](http://php-fpm.org/),
but feel free to use another one. Here's the configuration used:

```
server {
    listen 80;
    server_name bench-sf-standard.example.com;
    root /home/foobar/bench-sf-standard/web;

    location / {
        proxy_pass http://bench-sf-standard.example.com:5000;
    }

    error_log /home/foobar/bench-sf-standard/var/logs/nginx_error.log;
    access_log /home/foobar/bench-sf-standard/var/logs/nginx_access.log;
}
```
