### System requirements.

Use the exact versions to avoid difficulties in running npm.
1. Laravel 5.3 with Passport v1.0.8. Default v1.0 has an issue, v1.0.7 to be exact.
2. Node.js - tested with v4.2.6
3. npm - tested with v3.5.2

#### Installation from cloned repo.

1. $ sudo npm install
2. $ composer install
3. cp -p .env.example .env
4. php artisan key:generate
5. This one has always been a headache
	
	5.1 Always copy the Apache2 config found in the pse-screener/public (i.e. the front-end).

	5.2 In your pse-screener type
```
		$ sudo chmod 755 -R api
		$ sudo chmod -R o+w api/storage
		$ php artisan cache:clear
```
To fix the internal server error.
Note: 6, 7 are now optional since Route '/' already redirects to /public. So if you want to follow, change it to return view('welcome');

6. In order to check if Laravel installation is working, with your browser, http://<your_site>.
7. Check also the http://<your_site>/login and http://<your_site>/register.
8. Now to start with, go to http://<your_site>/public
9. Run php artisan passport:client --password
10. Register an account in http://<your_site>/public/#/registration
11. If this is uploaded in AWS, set the timezone to +8:00. If you have super privilege: mysql> SET GLOBAL time_zone = "+08:00:00".

#### Common Issues

1. [sudo] apt-get install php5.6-xml - If using PHP5.6.
2. [sudo] apt install zip unzip php5.6-zip - if using PHP5.6.
3. make sure mod_rewrite is enabled. To enable run sudo a2enmod rewrite and [sudo] service apache2 restart.
4. You may change the client_id and client_secret in your http://<mysite>/public/#/. Run gulp of course.


##### Some of the commands I used during installation for the public, api, and admin resources.

mkdir -p /opt/nodejs
tar -xvzf https://nodejs.org/dist/v4.2.6/node-v4.2.6-linux-x64.tar.gz -C /opt/nodejs/
mv node-v4.2.6-linux-x64 4.2.6
cd /opt/nodejs
ln -s 6.11.3 current
ln -s /opt/nodejs/current/bin/node /bin/node
node -v

sudo apt-get update
sudo apt-get install npm

sudo apt-get update
sudo apt-get install apache2
sudo apache2ctl configtest

sudo add-apt-repository ppa:ondrej/php
sudo apt-get install software-properties-common
sudo apt-get update
sudo apt-get install php5.6
sudo apt-get install php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml php5.6-zip

sudo apt-get install mysql-server
mysql_secure_installation
GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';
vim /etc/mysql/mysql.conf.d/mysqld.cnf
bind-address            = 0.0.0.0
systemctl restart mysql.service
CREATE USER 'username'@'%' IDENTIFIED BY 'password';

If "everyone" is allowed to read and execute composer, you don't need to use sudo:
sudo chmod 755 FOLDER/FILE
sudo chown -R lamp:lamp /home/lamp/.composer


/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
/sbin/mkswap /var/swap.1
/sbin/swapon /var/swap.1

When running gulp produce this error: Error: ENOENT: no such file or directory, scandir '/var/www/production/api/node_modules/node-sass/vendor'
run sudo npm install node-sass@^4.8.3

This one causes black response at all. Just run this:
$ /var/www/production/api$ sudo chmod -R o+w storage

php composer.phar require laravel/passport=~1.0.8

sudo chown -R www-data:www-data bootstrap/cache/

$ /var/www/production/api$ sudo chown -R www-data:www-data storage/oauth-private.key
$ /var/www/production/api$ sudo chown -R www-data:www-data storage/oauth-public.key

CURL
curl -d "grant_type=password&client_id=X&client_secret=X&username=email@y.com&password=000000" http://www.pse-screener.com/oauth/token > /d/tmp/error.html

##### Restarting the server (production)
1. Clear and cache the config: php artisan config:cache;
2. Check the current timezone of mysql. I haven't actually checked if restarting reset the timezone to +-0.
3. Seems like the environment variable is wiped out when restarting. `$ export NODE_ENV=production`. Look for way to make this permanent.
4. Try to check if timezone is reset to UTC, otherwise follow resolution of [Issue 36](https://github.com/pse-screener/api/issues/36).

##### Git pull
1. `sudo su`
2. `# eval $(ssh-agent -s)`
3. `# ssh-add /home/ubuntu/.ssh/id_rsa`
4. `# git pull`
5. `# exit`
6. `$ php artisan config:cache`
