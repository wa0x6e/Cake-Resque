php-resque: PHP Resque Worker (and Enqueue) with phpredis
===========================================

Based on [PHP-Resque](https://github.com/chrisboulton/php-resque) by chrisboulton, this is an alternate version using [phpredis](https://github.com/nicolasff/phpredis) instead of [redisent](https://github.com/jdp/redisent) to interact with [Redis](http://redis.io/).


By using redisent, php-resque was working out of the box if you've already Redis, a convenience costing a little performance (see [benchmark](http://nosql.mypopescu.com/post/2704310211/redis-and-php-what-library-to-use)). 
Phpredis is a little bit faster, since it's a native PHP extension written in C. 

Details for installing phpredis can be found at https://github.com/nicolasff/phpredis