# WebAuth Angular Laravel

# Required
- Apache
    - Authenticated SSL required
    - Unauthenticated certificate can be used for localhost
- MariaDB
- Redis (option)
- php7.3 or later
- nodejs 10.16 or later
- angular 9 or later

All the WebAuth implementation parts are described in export, so should they be readable with vue.js?

Please execute with "--recursive" option to include submodule when git clone


# Supported attestation
* fido-u2f
* packed


# configulation
### client configuration

~~~cmd
app/home/home.component.ts

#34 ~ 35
private server = 'your server domain';
private path = 'Document root to server path';
~~~

### server configuration
~~~cmd
server/.env

#10 ~ 14
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your database name
DB_USERNAME=access user name
DB_PASSWORD=access user password

~~~

# setup Server

### 1:: install angular9 and npm modules
~~~cmd
cd [project top]
npm install
~~~

### 2:: install laravel and php modules
~~~cmd
cd server/
composer install
~~~

### 3:: database migration
~~~cmd
php artisan migrate
~~~


# start sample

### build and start
~~~cmd
cd [project top]
ng serve --host [your domain] --ssl true --ssl-key 'private key path' --ssl-cert 'cert path'
~~~
