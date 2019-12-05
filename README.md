1. install composer dependencies: ```$ composer install```
2. start mysql, create database and provide credentials to .env file e.g: ```DATABASE=mysql://user:password@localhost:3306/database_name```
3. update database migrations: ```./vendor/bin/doctrine orm:schema-tool:update --force```
4. start development server: ```php -S localhost:8888 -t public public/index.php```
5. go to [localhost](http://localhost:8888) and check the result
