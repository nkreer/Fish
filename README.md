# Fish 

![TravisCI build](https://travis-ci.org/nkreer/Fish.svg)
![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)
![Version](https://img.shields.io/badge/Latest%20Version-1.0-lightgrey.svg)

Fish is an IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3 License](LICENSE).

## Getting started

### Requirements

* PHP 7
* Unix-like OS
* Composer

Fish has been tested to work well on unix systems and PHP 7.
It does not work with PHP 5 or Windows at the moment. HHVM hasn't been tested. 
To install and use Fish, make sure you also have [composer](https://getcomposer.org) installed. 

### Installation

For the best compatibility with plugins, you should always use [the latest release](https://github.com/nkreer/Fish/releases). 
Just clone this repository, extract it, navigate there and run:

`$ composer install`

### Start

Open a terminal, navigate to the bot's source files and run

`$ php Start.php <address> [port] [arguments]`

The arguments you can use are:

```
--no-ssl true           for connecting without TLS (Don't use this unless really needed)

--password <password>   for connecting to a passworded server
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

Several plugins have already been written for Fish: 

| Plugin | Description |
| ------ | ----------- |
|[PluginTools](https://github.com/nkreer/PluginTools)| Helps you with the packaging of your plugins |
|[Permissions](https://github.com/nkreer/Permissions)| Changes users' permissions on IRC |
|[Scripts](https://github.com/nkreer/Fish-Scripts)| Enables you to add simple custom commands to the bot |

If you want me to add your plugin to the list, open a pull request or message me.

## API Documentation

You can find a really simple tutorial on how to write your own plugins in this repository's [wiki](https://github.com/nkreer/Fish/wiki).
If you have any questions regarding the API, feel free to send me an E-Mail.