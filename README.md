# Fish 

![TravisCI build](https://travis-ci.org/nkreer/Fish.svg)
![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)
![Version](https://img.shields.io/badge/Latest%20Version-1.0-lightgrey.svg)

Fish is an IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3 License](LICENSE).

## Getting started

### Installation

Fish has been tested to work well on unix systems and PHP 7.
It does not work with PHP5 or Windows. The software and some plugins require the [pthreads extension for PHP written by krakjoe](https://github.com/krakjoe/pthreads) and a PHP installation with thread safety enabled.

To install Fish, make sure you have [composer](https://getcomposer.org) installed. 
For the best compatibility with plugins, you should always use [the latest release](https://github.com/nkreer/Fish/releases). 
Just download the source code, extract it, navigate there and run:

```
$ composer install
$ pecl install pthreads
```

Unfortunately, if you can't install pthreads in your environment, you may not use the bot for now.

### Start

Open a new terminal (preferably in a screen), navigate to the bot's source files and run

```$ php Start.php <address> [port] [options]```

The options you can use are:

`--insecure true` for connecting without SSL
`--password <password>` for connecting to a passworded server

## Features

Fish offers many features for interaction with an IRC Server out of the box: 

* Super simple and powerful plugin API (The most simple you will find in a PHP IRC-Bot to date!)
* Support for multiple connections in one process
* Plugins can do stuff asynchronously!
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

If you want me to add your plugin to the list, open a pull request or message me

## API Documentation

You can find a tutorial on how to write your own plugins in this repository's [wiki](https://github.com/nkreer/Fish/wiki).
If you have any questions regarding the API, feel free to send me an E-Mail.

## Contribute

All contributions and Pull Requests are welcome. Just stick to the original coding style wherever possible.

## Test Fish

I don't have the resources to provide a testing instance of the bot or a help-channel on a public IRC-network at the moment. Sorry.