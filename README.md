# Benchmarking Symfony Standard

We, developers, *love* meaningless benchmarks.
Here's one for the [Symfony Standard Edition](https://github.com/symfony/symfony-standard).

## Usage

First prepare the environment:

    rm -rf var/cache/* var/logs/* vendor
    composer install -o --no-dev
    bin/console cache:clear -e=prod --no-debug
    curl http://bench-sf-standard.example.com/

Then use [Apache Benchmark](https://httpd.apache.org/docs/2.2/programs/ab.html)
for 10 seconds with 10 concurrent clients:

    ab -c 10 -t 10 'http://bench-sf-standard.example.com/'

Finally use [blackfire](https://blackfire.io/) to profile the request:

    curl -H 'X-Blackfire-Query: enable' http://bench-sf-standard.example.com/

## Results

| Metric                                            | Value        |
|---------------------------------------------------|--------------|
| Requests per second                               | 3735.28#/sec |
| Time per request                                  | 2.677ms      |
| Time per request (across all concurrent requests) | 0.268ms      |

> Benchmarks run with:
>
> * PHP 7 (`7.0.4-7+deb.sury.org~trusty+2`)
>   with [Zend OPcache](http://php.net/manual/en/book.opcache.php) enabled
>   and *without* [Xdebug](https://xdebug.org/)
> * Linux 3.13.0-83-generic, Ubuntu 14.04.4 LTS, x86_64
> * [Lenovo Yoga 13](http://shop.lenovo.com/il/en/laptops/lenovo/yoga/yoga-13/#tab-tech_specs), with core i7

### Profiling

| Metric              | Value        |
|---------------------|--------------|
| Requests per second | 3735.28#/sec |
| Wall Time           | 1.09ms       |
| CPU Time            |    1ms       |
| I/O Time            | 1.08ms       |
| Memory              |   65kB       |

Profiling reveals that the most of the time is spent in event listeners, which is
to be expected since all the Symfony logic is here.

## Web Server Configuration

Since [PHP built in server](http://php.net/manual/en/features.commandline.webserver.php)
runs on a single-threaded process, we need to use something else for our benchmarks.

We've picked [nginx](https://www.nginx.com/) with [Supervisord](http://supervisord.org/),
but feel free to use another one. Here's the configuration used:

```
upstream backend  {
    server 127.0.0.1:5500 max_fails=1 fail_timeout=5s;
    server 127.0.0.1:5501 max_fails=1 fail_timeout=5s;
    server 127.0.0.1:5502 max_fails=1 fail_timeout=5s;
    server 127.0.0.1:5503 max_fails=1 fail_timeout=5s;
}

server {
    root /home/foobar/bench-sf-standard/web/;
    server_name bench-sf-standard.example.com;
    location / {
        try_files $uri @backend;
    }
    location @backend {
        proxy_pass http://backend;
        proxy_next_upstream http_502 timeout error;
        proxy_connect_timeout 1;
        proxy_send_timeout 5;
        proxy_read_timeout 5;
    }
}
```

And:

```
[program:bench-sf-standard]
command=php bin/react.php
environment=PORT=55%(process_num)02d
process_name=%(program_name)s-%(process_num)d
numprocs=4
directory=/home/foobar/bench-sf-standard
umask=022
user=loic.chardonnet
stdout_logfile=/var/log/supervisord/%(program_name)s-%(process_num)d.log              ; stdout log path, NONE for none; default AUTO
stderr_logfile=/var/log/supervisord/%(program_name)s-%(process_num)d-error.log        ; stderr log path, NONE for none; default AUTO
autostart=true
autorestart=true
startretries=3
```
