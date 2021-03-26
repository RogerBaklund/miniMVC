## MiniMVC ##

This is a lightweight MVC framework for PHP. Classes for models, views, controllers and routes 
are all defined within the single file `miniMVC.php` within 350 lines of code. 

Everything is loosely coupled, you are not required to use the View classes with the Model classes or vice versa.
You can use these classes for other things than MVC applications.

Some additional utilities are provided to aid rapid application development, distributed in separate (and independant) files:

- BootstrapUI.php
- DBModel.php (extends Model class)
- FormBuilder.php
- SimpleInput.php
- SimpleMenu.php

This distribution also contains documentation, example code and three demo applications:

- Database manager
- Project wizard
- Bootstrap demo

The Database manager lets you browse and modify data in any MySQL database. See below for
a security consideration if you choose to test this. Note that this is a demo only, it is
not properly tested and not suited for editing content of a database. You can however use
it to browse and create new tables. **Note**: The Update record function is buggy, do not
use on important data!

The Project wizard can be used to create a new project based on miniMVC. Fill in a simple
form, select which features you will need, and the application will create the directory
structure and copy the PHP files you need. It will also let you select additional tools
from a list of external libraries.

The Bootstrap demo is just that, a demonstration of using the BootstrapUI helper class.

## Installation instructions ##

Install the distribution on a (local) web server. If you put it in a folder named  `/miniMVC`
it should work out of the box.

You should modify `index.php`, you will find some basic application configuration there,
in particular you can modify APP_PATH to point to any desired path on the web server.

If you run the Database manager you will be able to provide host, user, password and database
name. Default is 'localhost', 'root' user, blank password and database 'test'. This might fail
with an error. Just press the `Config` button and enter your settings. These settings are stored
in `json/config.json`. You can also modify them with a text editor.

### IMPORTANT: SECURITY ISSUE 

By default a web server will allow access to `json/config.json` if you do not configure it to
do otherwise. **The password will be visible!**

### NOTE: v0.1a is old code (2015)

Current version: [v0.1a-2](//github.com/RogerBaklund/miniMVC/releases/tag/v0.1a-2releases/tag/v0.1a-2) (Same old code, just bugfix)

This code is compatible with PHP 5.3 or later.

2021: Tested with PHP 7.4 & MySQL 8
