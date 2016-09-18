# pse

System requirements.

1. Linux 14.04
2. Apache2
3. Laravel 5.3 with Passport.
4. Node.js v.6.5.0
5. npm -v 3.10.3

Installation from cloned repo.

1. npm install
2. composer install
3. cp -p .env.example .env
4. php artisan key:generate

Thereafter follow https://laravel.com/docs/5.3/passport.

Please note that there are common issues in the environment like the following if you're using Apache2

1. Blank page loading the document root folder.
2. Permission denied on <main>/vendor and <main>/...laravel.log.
3. Bank page on GET <hostame>/login or GET <hostname>/register
4. Entry module not found: Error: Can\'t resolve \'buble\' in ...
5. /laravel-elixir-webpack-official/node_modules/webpack/node_modules/loader-runner/lib/loadLoader.js:35 throw new Error("Module '" + loader.path + "' is not a loader (must have normal or pitch function


Check on the following with respect to the number above.

1. Check the permission. chmod -R 777 <folder>. Do not use this in production.
2. Check again the the permission but for the /vendor and laravel.log. chmod -R 777 <folder>. Do not use this in production.
3. Enable the mod_rewite and write this on your virtual host.
<Directory "/var/www/pmorcilladev/pse_screener/api/public">
	Options Indexes FollowSymLinks MultiViews
	AllowOverride All
	Order allow,deny
	allow from all
</Directory>
4. 

**When using Laravel/Passport**

1. $ npm install --global gulp
2. $ gulp or $ sudo gulp
3. Run npm rebuild node-sass is encountered with this error.
3. npm install --save-dev laravel-elixir-vue
4. npm install --save-dev laravel-elixir-vue
5. sudo npm install laravel-elixir-webpack-official --save-dev

```
p.morcilla@JTBosUPMorcilla:/var/www/pmorcilladev/pse_screener/api$ sudo gulp
[10:07:46] Using gulpfile /var/www/pmorcilladev/pse_screener/api/gulpfile.js
[10:07:46] Starting 'all'...
[10:07:46] Starting 'sass'...
[10:07:47] Finished 'sass' after 1.85 s
[10:07:47] Starting 'webpack'...
[10:07:56]
[10:07:56] Finished 'webpack' after 8.6 s
[10:07:56] Finished 'all' after 10 s
[10:07:56] Starting 'default'...
....
[10:07:56] Finished 'default' after 10 ms
```

Apache2 config
```
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName www.pse-screener.com
        DocumentRoot /var/www/pmorcilladev/pse_screener/api/public

        # this is where our front-end is
        Alias "/public" "/var/www/pmorcilladev/pse_screener/public"
        # this is where the admin pages are
        Alias "/admin" "/var/www/pmorcilladev/pse_screener/admin/app"

        <Directory "/var/www/pmorcilladev/pse_screener/api/public">
               Options Indexes FollowSymLinks MultiViews
               AllowOverride All
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        # ServerAlias *.pse-screener.com
        ServerName pse-screener.com
        RedirectMatch permanent ^/(.*) http://www.pse-screener.com/$1

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

# HTTP Requests
**Requesting token**

curl http://www.pse-screener.com/oauth/token -d 'grant_type=password&username=test@gmail.com.com&password=123456&client_id=1&client_secret=mjy4eilKhSJPd8y4IkHUPxiYvzB3UMShxNyJGZVz'

**New registration**

$ curl -H "Accept: application/json " http://www.pse-screener.com/register -d "fName=Jhunex&lName=Jun&gender=M&email=test1@gmail.com&password=123456&mobileNo=09206939093&password_confirmation=123456" > /d/tmp/error.html