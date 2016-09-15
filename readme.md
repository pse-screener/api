# pse

System requirements.
1. Linux 14.04
2. Apache2
3. Laravel 5.3 with Passport.
4. Node.js v.6.5.0
5. npm -v 3.10.3


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

When using Laravel/Passport
1. $ npm install --global gulp
2. $ gulp or $ sudo gulp
3. Run npm rebuild node-sass is encountered with this error.
3. npm install --save-dev laravel-elixir-vue
4. npm install --save-dev laravel-elixir-vue
5. sudo npm install laravel-elixir-webpack-official --save-dev

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
┌───────────────┬───────────────────────────────┬────────────────────────────────┬────────────────────┐
│ Task          │ Summary                       │ Source Files                   │ Destination        │
├───────────────┼───────────────────────────────┼────────────────────────────────┼────────────────────┤
│ mix.sass()    │ 1. Compiling Sass             │ resources/assets/sass/app.scss │ public/css/app.css │
│               │ 2. Autoprefixing CSS          │                                │                    │
│               │ 3. Concatenating Files        │                                │                    │
│               │ 4. Writing Source Maps        │                                │                    │
│               │ 5. Saving to Destination      │                                │                    │
├───────────────┼───────────────────────────────┼────────────────────────────────┼────────────────────┤
│ mix.webpack() │ 1. Transforming ES2015 to ES5 │ resources/assets/js/app.js     │ public/js/app.js   │
│               │ 2. Writing Source Maps        │                                │                    │
│               │ 3. Saving to Destination      │                                │                    │
└───────────────┴───────────────────────────────┴────────────────────────────────┴────────────────────┘
[10:07:56] Finished 'default' after 10 ms