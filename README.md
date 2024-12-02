Vanilla-php
===

Vanilla-php is distributed under **GPL-3.0 license**.

# Development Specifications
The development has been done under this configuration:

- Debian 12
- php8 (8.2.24)
  - php8.2-mysql
  - php-xml
- apache2 (2.4.62)
  - libapache2-mod-php8.2
- mariadb 11.5.2
- composer

# Installation


This README explain how to install and configure this framework through the creation of a fictive project called **myProject**. This installation assumes that you have already apache2.

- **myProject** project will be located in */usr/share*.
- The fullpath for the project will be **/usr/share/myProject**.

## Project Directory
Copy this folder into your project location. I.E. if your project is located in */usr/share/myProject*

On your **/usr/share/myProject** folder you should see the following folders:

- Controllers
- Core
- includes
- logs
- ...

The installation is **DONE** ! But we need to do some configuration.

# Configuration

## Hosts

On your **/etc/hosts** file add the line:

```conf
# Add an alias pointing on myProject.local
127.0.0.1   myProject.local myProject
```

## apache2

Firstly the module rewrite has to be enabled:

```bash
# Activate the RewriteEngine mode on apache2
a2enmod rewrite
```

Then we have to create a new virtual host.

```bash
# Edit the myProject config file
vim /etc/apache2/sites-available/myProject.conf
```

Put this content (content by default) into your newly created conf file:

```xml
#
# You NEED to adapt the content of this file for your needs
#

<VirtualHost myProject.local:80> # how to reach your virtual host
  ServerName myProject.local
  ServerAdmin myProject@localhost
  DocumentRoot /usr/share/myProject/public/

  <Directory /usr/share/myProject/public/>
        RewriteEngine On

        # Explicitely serve existing files
        RewriteCond %{REQUEST_FILENAME} -f
        RewriteRule ^ - [L]

        # Rewrite non existing uris
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule (.*) index.php?uri=$1 [QSA,L]

    AllowOverride None
  </Directory>

  ErrorLog /usr/share/myProject/logs/error.log
  CustomLog /usr/share/myProject/logs/access.log combined

</VirtualHost>
```

Currently this apache configuration is done but not activated:

    ~# a2ensite myProject
    ~# systemctl restart apache2

## Web Configuration
Go back in your project folder and edit the **/usr/share/myProject/config.ini** file. Add your info on it.

```ini
[databases]
; List your databases here, separated by ','
; list=myProject,db2
list=myProject

; For each db in databases.list, create a section called:
; db_<name>, with the following params

; Feed your myProject db info on this section
[db_myProject]
dbengine=mysql
dbhost=localhost
dbport=3306
dbuser=myProject
dbpassword="myProjectPasswd"
dbname=myProject

; [db_db2]
; dbengine=mysql
; dbhost=localhost
; dbport=3306
; dbuser=db2
; dbpassword="db2_password"
; dbname=db2


[security]
; csrf token expires after 3600 secs after its generation
csrf_expiration=3600

; specify the password length required
password_min_len=8

[display]
; parameter used for pagination. maxperpage limits the number of rows selected and displayed a page.
maxperpage=20
```

All done, now you have to create your content.

## Composer

You just have to run the composer modules installation

```bash
composer install```
