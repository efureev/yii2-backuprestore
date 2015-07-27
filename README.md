Yii2 Backup and Restore Database
===================
Database Backup and Restore functionality

This extension is base in:
https://github.com/efureev/yii2-backuprestore and other yii1 similar backup-restore extensions 
I converted to yii2 and made it more intuitive using the Kartik extensions.


Demo
-----
Simple demo to see the screens and a proof of concept
http://yii2.oe-lab.tk/



Installation
------------

Requirements

Either run

```
php composer.phar require --prefer-dist efureev/yii2-backuprestore "dev-master"
```

or add

```
"efureev/yii2-backuprestore": "dev-master"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply add it in your config by  :

Basic ```config/web.php```

Advanced ```[backend|frontend|common]/config/main.php```

>
        'backuprestore' => [
            'class' => '\oe\modules\backuprestore\Module',
            //'layout' => '@admin-views/layouts/main', or what ever layout you use
            ...
            ...
        ],

make sure you create a writable directory named _backup on app root directory.

Pretty Url's ```/backuprestore```