# Exposure #

**Exposure** is a web application that helps project owners and organisations gain exposure by enabling the former to pitch their project and the latter to sponsor these projects and obtain publicity in return.

Exposure was built using PHP, JavaScript and MongoDB, and relies on a number of libraries (Doctrine-ODM, Twig, Monolog, Swiftmailer etc. for PHP; jQuery, Noty etc. for JavaScript).

The application also depends on a custom and lightweight PHP MVC framework called **Sociable**.

**Warning** – Make sure you read the Caveats section below before attempting to run this application. 

## Prerequisites ##

Running this application requires the following:

- [nginx](http://nginx.org/) (tested with version 1.4) or [Apache](http://www.apache.org/) web server. 

- [PHP](http://php.net/) (tested with version 5.4, should work fine with earlier versions of PHP 5).

- [MongoDB](http://www.mongodb.org/) (tested with version 2.4) and the [MongoDB PHP driver](http://www.php.net/manual/en/mongo.installation.php) for PHP.

If you want to run the unit tests, you will also need to install [phpunit](http://phpunit.de).

This code is known to work on Windows 7 and on GNU/Linux (CentOS).

## Installation guidelines ##

### Get the source code ###

Clone this repository in an installation directory, which we'll call `$INSTALL_DIR`.

Update submodules (retrieving the Sociable framework).

Install dependencies by running [Composer](http://getcomposer.org/) in `$INSTALL_DIR`:

	$ composer install

### Configure the web server ###

Configure your web server to execute `*.php` files as PHP, and with `$INSTALL_DIR/httpdocs` as the root directory.

Additionally, you need to define rewrite rules to prepend `index.php/` (i.e. to run the application's front controller) to any URL requests that do not match an actual file (e.g. `/maintenance.html` will be served as is, `/project` will be rewritten as `/index.php/project`).

#### nginx ####

If using nginx, add the following lines to the `location /` section:

    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php last; break;
    }

Also add the following line in the `server` section to enable "large" file uploads: 

	client_max_body_size 10M;

#### Apache ####

If using Apache and the mod_rewrite module, a `.htaccess` file such as this one in the `$INSTALL_DIR/httpdocs` directory will do the trick:

    RewriteEngine on
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    RewriteRule ^(.*)$ /index.php [L,QSA]

### Configure PHP ###

Activate the following PHP extensions in your `php.ini` file:

- mbstring,
- openssl,
- fileinfo,
- intl.

**Note** – It is assumed that the mongo extension has already been activated as part of the installation of the MongoDB PHP driver.

Raise the default file size upload limits by changing the `upload_max_filesize` configuration directive to:
  
	upload_max_filesize = 5M

### Configure the database ###

Create a MongoDB database to store the application's data, and add a user with the following permissions: `readWrite`, `dbAdmin`.

### Configure the application ###

Copy the `$INSTALL_DIR/sys/config/config.inc.php.orig` file to `$INSTALL_DIR/sys/config/config.inc.php`, and edit all lines marked as `// customise this` to reflect your configuration.

**Note** – If you want to run the object document mapping tests (in `/tests/odm`), you'll also need a `config-test.inc.php` file.

### Populate the database ###

Go to the `$INSTALL_DIR/data` directory.

Generate the indexes:

	$ php ensure_indexes.php

**Note** – If you want to run the object document mapping tests, you'll also need to generate the indexes for the test database: change the `ensure_indexes.php` file to point to the `config-test.inc.php` file that you created previously.

Import all static data:

	$ php import_languages.php languages.tsv
	$ php import_countries.php countries.tsv
	$ php import_currencies.php currencies.txt
	$ php import_business_sectors.php business_sectors.tsv
	$ php import_moderation_reasons.php moderation_reasons.tsv
	$ php import_country_locations.php country_locations.tsv
	$ php import_themes.php themes.tsv
	$ php import_themes.php subthemes.tsv
	$ php import_sponsor_return_types.php sponsor_return_types.tsv

Initialise the admin "singleton":

	$ php init_administration.php ADMIN

Create a super-administrator account

	$ php new_superadmin.php <email> <password>

Create subscriptions (edit data beforehand if required, e.g. to make sure they use supported currencies):

	$ php init_subscriptions.php

### Create required directories ###

Create the following directories:

- `$INSTALL_DIR/sys/log`: application log directory.
- `$INSTALL_DIR/model/cache`: model cache directory, containing proxy and hydrator classes.
- `$INSTALL_DIR/templates/cache`: Twig cache directory.

## Run the application ##

Start the database server and the web server, navigate to the root URL, and feel free to look around.

If the root URL is `$ROOT_URL` then the admin web interface, which you can sign in to with the super-administrator account you created previously, is located at `$ROOT_URL/admin`. 

## Caveats ##

The application essentially works **BUT** it hasn't been thoroughly tested or reviewed so even the working parts should be treated as beta- or even alpha-grade: the source code is known to contain smelly or dirty code, inconsistencies, probably a fair share of bugs, and generally speaking some sections are likely to make you cringe one way or another. I should mention that this is my first attempt at writing a web app: I've learnt a lot on the way and in hindsight I would have done quite a few things very differently (using a mature MVC framework for starters).

As I'm moving on to other projects I will not be supporting or maintaining this application so please dive in and fork away if you want to take over and modify the code.

**Hint** – The application follows the MVC architecture pattern, so start with the `/httpdocs/index.php` file (the front controller) and work your way forward from there, everything should make sense pretty quickly. 

Integration with a payment platform has not been included to avoid dependencies on proprietary APIs and services.

You may want to have a look at the `IMPROVEMENTS.md` file for a list of ideas to improve this application.

## Trivia ##

The 'x' in the blue triangle in the Exposure logo (`httpdocs/assets/images/logo.png`) is actually the '+' half of a camera's exposure button.

## Licence ##

Copyright 2013 Sébastien Pujadas under the [MIT license](LICENCE), with the following exceptions:

- [Bootstrap](http://getbootstrap.com/) (parts of which are included in `httpdocs/assets/css`) is copyright 2013 Twitter, Inc under the [Apache 2.0 license](LICENCE.Apache-2.0). This licence also covers the GLYPHICONS Halflings font (located in `httpdocs/assets/fonts`), which was created by [GLYPHICONS.com](http://glyphicons.com/). 

- [jQuery](http://jquery.com/), [jQuery-UI](http://jqueryui.com/) (parts of which are included in `httpdocs/assets/js`) are copyright 2013 jQuery Foundation and other contributors (see individual files for details) under the MIT license.

- [noty](http://needim.github.io/noty/) (parts of which are included in `httpdocs/assets/js/noty`) is copyright 2012 Nedim Arabacı (see individual files for details) under the MIT license. 

- The [jQuery File Upload plugin](http://blueimp.github.io/jQuery-File-Upload/) (included in `httpdocs/assets/js`) is copyright 2010 Sebastian Tschan (see individual files for details) under the MIT license. 

The flag icons in `httpdocs/assets/images/flags` are public domain, and were created by [famfamfam.com's Mark James](http://www.famfamfam.com/lab/icons/flags/).