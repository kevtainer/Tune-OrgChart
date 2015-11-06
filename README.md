# Tune-OrgChart

Requirements
--

* A computer
* A modern Operating System (hint: not Windows)
* VirtualBox (OSX only)
* Docker Toolbox
* IPA

Installation
--

* Clone this Repository
  * I recommend putting it in `~/Tune`
* The next 3 steps are for OSX users, Linux users, you probably already know what to do (or rather what _not_ to do)
* `docker-machine create --driver virtualbox tune`
* `eval "$(docker-machine env tune)"` (you trust me, yes?)
* `docker-machine ip tune` Use this ip address to setup your vhost file
* Add `api.tune.dev` && `tune.dev` into your hosts file with the aforementioned ip address
* `cd ~/Tune` (you cloned the repository, right?)
* Build the custom images
  * `docker build -t tune/nginx ./docker/images/nginx`
  * `docker build -t tune/php ./docker/images/php`
  * `docker build -t tune/mysql ./docker/images/mysql`
  * `docker build -t tune/composer ./docker/images/composer`
* Install the composer dependencies
  * `docker run -v ~/Tune/api:/srv tune/composer update`
* Boot up the docker compose service (hold onto your butts)
  * `docker-compose up` (this is going to take a while the first time)

Stuff you can do
--

* http://tune.dev
* http://tune.dev:8080 (graphite/statsd dashboard)

Todo (maybe)
--

* ~~Figure out how to count subordinates without another pass over the tree~~
* ~~Write tests~~
