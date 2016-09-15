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

**To request token**
curl http://www.pse-screener.com/oauth/token -d "grant_type=password&client_id=2&client_secret=uOibj06UNXXufKJXOd8rWMdPoWzxLwYCpjEwa3o7&username=test@gmail.com&password=123456"

**To register i.e. create new user**
curl http://www.pse-screener.com/register -d "name=Jhunexjun&email=test1@gmail.com&password=123456&mobileNo=09206939093&password_confirmation=123456&_token=fL4iYs3opaLmjgKmYvp6gAv8KAGyVLnQMvuI3RmR"