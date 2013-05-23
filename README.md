Akamai Purger
=======================

Usage
-----

Create the instance

    $purger = new Mw\Cdn\Purger(
        {username is a string REQUIRED},
        {password is a string REQUIRED},
        {server is a string OPTIONAL},
        {logger instance of Mongolog Logger OPTIONAL}
    );

Set notifications

    $purger->setNotificationEmail({email address});

Add the url to purge

    $purger->addUrl('htp://cdn11.my-wardrobe.com/images/products/9/2/928786/t_928786.jpg');

And purge!

    $purger->purge();

Requirements
------------

* PHP 5.3+
* Internet connection

Authors
-------

my-wardrobe - <sysadmin@my-wardrobe.com>