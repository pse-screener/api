# pse
1. npm install
2. composer install
3. cp -p .env.example .env
4. php artisan key:generate

Thereafter follow https://laravel.com/docs/5.3/passport.

Please note that there are common issues in the environment like the following if you're using Apache2
1. Blank page loading the document root folder.
2. Permission denied on <main>/vendor and <main>/...laravel.log.
3. Bank page on GET <hostame>/login or GET <hostname>/register

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