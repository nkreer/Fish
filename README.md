# Fish 

![TravisCI build](https://travis-ci.org/nkreer/Fish.svg)
![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)

Fish is a **libre** and open source IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3](LICENSE).

## Getting started

### Requirements

* PHP 7 (Tested on 7.0.x, should work on 7.1 as well)
* Unix-like OS (Tested on macOS Sierra and Debian Linux)
* Composer

Fish has been tested to work well on unix systems and PHP 7.
It does not work with PHP 5 or Windows at the moment. HHVM hasn't been tested. 
To install and use Fish, make sure you also have [composer](https://getcomposer.org) installed. 

### Installation

For the best compatibility with plugins, you should always use [the latest stable release](https://github.com/nkreer/Fish/releases).
It's not recommended to use the source-code from this repository in production, as it may contain bugs or be unstable.
 
#### Normal installation

Either directly use composer to download and install the newest version:

`$ composer create-project nkreer/fish`
 
or download the latest codebase and let composer install the software using

`$ composer install`
 
#### Installation for use as a library

Fish is available on Packagist. You can require it for your own projects like this.

`$ composer require nkreer/fish`

Please keep in mind that the software isn't designed to be used as a library. Some features may not work in your environment. 

### Configuration

Configuration is done in the fish.json file. You can find help with setting Fish up in [CONFIGURATION.md](CONFIGURATION.md).

### Start

Open a terminal, navigate to the bot's source files and run

`$ php Start.php <address> [arguments]`

The available arguments are:

```
--port <port>           for using a port other than 6697

--no-ssl true           for connecting without TLS (Don't use this unless really needed)

--password <password>   for connecting to a passworded server

--config <path>         for using a different config file
```

## Features

Fish offers many features for interaction with an IRC Server out of the box: 

* Super simple and powerful plugin API (The most simple you will find in a PHP IRC-Bot to date!)
* Support for multiple connections in one process
* Built-in and adapting management-commands (join, part, help, etc.)
* Built-in user authentication features
* Built-in permission management
* Many more

## Extend

Fish can be extended by plugins. Several have already been written: 

| Plugin | Description |
| ------ | ----------- |
|[PluginTools](https://github.com/nkreer/PluginTools)| Helps you with  packaging of your plugins |
|[Permissions](https://github.com/nkreer/Permissions)| Changes users' permissions on IRC |
|[Scripts](https://github.com/nkreer/Fish-Scripts)| Enables you to add simple custom commands to the bot |

More is in the works.

## API Documentation

You can find a simple tutorial on how to write your own plugins in this repository's [wiki](https://github.com/nkreer/Fish/wiki).
If you have any questions regarding the API, feel free to send me an E-Mail.