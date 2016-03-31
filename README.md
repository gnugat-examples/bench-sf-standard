# Benchmarking Symfony Standard

We, developers, *love* meaningless benchmarks.
Here's one for the [Symfony Standard Edition](https://github.com/symfony/symfony-standard).

## Usage

First prepare the environment:

    rm -rf var/cache/* var/logs/* vendor
    composer install -o --no-dev
    bin/console cache:clear -e=prod
    curl http://bench-sf-standard.example.com/

Then use [Apache Benchmark](https://httpd.apache.org/docs/2.2/programs/ab.html)
for 10 seconds with 10 concurrent clients:

    ab -c 10 -t 10 'http://bench-sf-standard.example.com/'

Finally use [blackfire](https://blackfire.io/) to profile the request:

    blackfire curl http://bench-sf-standard.example.com/

## Results

| Metric                                            | Value        |
|---------------------------------------------------|--------------|
| Requests per second                               |  690.84#/sec |
| Time per request                                  | 14.475ms     |
| Time per request (across all concurrent requests) | 1.448ms      |

> Benchmarks run with:
>
> * PHP 7 (`7.0.4-7+deb.sury.org~trusty+2`)
>   with [Zend OPcache](http://php.net/manual/en/book.opcache.php) enabled
>   and *without* [Xdebug](https://xdebug.org/)
> * Linux 3.13.0-83-generic, Ubuntu 14.04.4 LTS, x86_64
> * [Lenovo Yoga 13](http://shop.lenovo.com/il/en/laptops/lenovo/yoga/yoga-13/#tab-tech_specs), with core i7

### Profiling

| Metric              | Value       |
|---------------------|-------------|
| Requests per second | 242.25#/sec |
| Wall Time           | 27.1ms      |
| CPU Time            | 20.2ms      |
| I/O Time            | 6.88ms      |
| Memory              | 2.09MB      |

Profiling reveals that the most expensive part is Autoloading, via `spl_autoload_call`:

* 80 times
* inclusive wall time of 10ms (36.99%)
    * inclusive I/O time of 4.5ms
    * inclusive CPU time of 5.53ms
    * inclusive memory use of 435KB

Its main callers are:

* Swiftmailer
* ContainerAware EventDispatcher
* Dependency Injection Container
* AppKernel (`registerBundles`)

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
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args;
    }

    location ~ ^/app\.php(/|$) {
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;

        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/app.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    error_log /home/foobar/bench-sf-standard/var/logs/nginx_error.log;
    access_log /home/foobar/bench-sf-standard/var/logs/nginx_access.log;
}
```
