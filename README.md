# Benchmarking Symfony Standard

We, developers, *love* meaningless benchmarks.
Here's one for the [Symfony Standard Edition](https://github.com/symfony/symfony-standard).

## Usage

First prepare the environment:

    rm -rf var/cache/* var/logs/* vendor
    composer install -o --no-dev
    bin/console cache:clear -e=prod
    sudo /opt/appserver/server.php -s prod
    sudo /etc/init.d/appserver restart
    curl http://127.0.0.1:9080/bench-sf-standard/hello.do/

Then use [Apache Benchmark](https://httpd.apache.org/docs/2.2/programs/ab.html)
for 10 seconds with 10 concurrent clients:

    ab -c 10 -t 10 'http://127.0.0.1:9080/bench-sf-standard/hello.do/'

Finally use [blackfire](https://blackfire.io/) to profile the request:

    blackfire curl http://bench-sf-standard.example.com/

## Results

| Metric                                            | Value        |
|---------------------------------------------------|--------------|
| Requests per second                               |   72.85#/sec |
| Time per request                                  |  137.270ms   |
| Time per request (across all concurrent requests) |   13.727ms   |

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

for this example, we use [appserver](http://appserver.io/). Here's the
configuration used:

```
<virtualHosts xmlns="http://www.appserver.io/appserver">
    <virtualHost name="bench-sf-standard.example.com">
        <params>
            <param name="documentRoot" type="string">webapps/bench-sf-standard</param>
        </params>
        <rewrites>
            <rewrite condition="-d{OR}-f{OR}-l" target="" flag="L" />
        </rewrites>
        <accesses>
            <access type="allow">
                <params>
                    <param name="X_REQUEST_URI" type="string">^.*
                    </param>
                </params>
            </access>
        </accesses>
    </virtualHost>
</virtualHosts>
```
