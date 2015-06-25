Install Application Q&A

    File with database dump is in data/database.sql

    Run php composer.phar install

    Update information information required to connect to the database web/index.php

        'db.options' => array(
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'databasename',
            'user' => 'user', 
            'password' => 'password',
            'charset' => 'utf8', ),

    In web/ change file .htaccess:
        Options -MultiViews
        RewriteEngine On
        RewriteBase {YourPath}
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]

    Run chmod 777 web/upload/

    Login and password to admin account. You should change them after first login.

        login: Admin
        password: jTE7cm666Xk6
