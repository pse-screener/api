### System requirements.

1. Linux - tested with 14.04
2. Apache2
3. Laravel 5.3 with Passport.
4. Node.js - tested with v4.2.6
5. npm - tested with v3.5.2

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
		$ sudo chmod -R o+w storage
		$ php artisan cache:clear
```
		to fix the internal server error.

6. In order to check if Laravel installation is working, with your browser, http://<your_site>.
7. Check also the http://<your_site>/login and http://<your_site>/register.
8. Now to start with, go to http://<your_site>/public
9. Run php artisan passport:client --password
10. Register an account in http://<your_site>/public/#/registration