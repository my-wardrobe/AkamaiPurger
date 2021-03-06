Akamai Purger
=======================

Remove or invalidate assets on your Akamai CDN.

[![Build Status](https://secure.travis-ci.org/my-wardrobe/AkamaiPurger.png?branch=master)](https://travis-ci.org/my-wardrobe/AkamaiPurger)

Usage
-----

Create the instance

    $purger = new Akamai\Cdn\Purger(
        {username is a string REQUIRED},
        {password is a string REQUIRED},
        {server is a string OPTIONAL},
        {logger instance of Monolog Logger OPTIONAL}
    );
see Monolog documentation [here](https://github.com/Seldaek/monolog)

Set notifications

    $purger->setNotificationEmail({email address});

Add the url to purge

    $purger->addUrl('http://www.example.com/acme/asset.png');

And purge!

    $purger->purge();

Requirements
------------

* PHP 5.3+
* Internet connection

Authors
-------

My Wardrobe - <sys.admin@my-wardrobe.com>
