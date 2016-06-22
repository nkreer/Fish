# Fish 

![Image of a fish](http://orig07.deviantart.net/1524/f/2013/023/0/9/fish_png_by_heidyy12-d5sg0z8.png)

![TravisCI build](https://travis-ci.org/nkreer/Fish.svg)
[![Chat](https://img.shields.io/badge/Chat%20on%20irc.rizon.net-%23fish--irc-brightgreen.svg)](http://qchat.rizon.net/?randomnick=1&channels=fish-irc&prompt=1&uio=d4)
![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)
![Supported OSes](https://img.shields.io/badge/platform-MacOS%2C%20Linux-lightgrey.svg)

Fish is an IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3 License](LICENSE)

## Installation

Fish has been tested on 

* Mac OS X 10.11 (El Capitan)
* Debian 8 (Jessie)

and is working with PHP 7.0.x. It does not work with PHP5.

To install Fish, make sure you have [composer](https://getcomposer.org) installed. Simply clone the repository and run

```$ composer install```

```$ mv fish.example.json fish.json```

and then edit the config. Fish will generate a new config on startup if it can't find one.

## Features

Fish offers many features for interaction with an IRC Server out of the box: 

* Super simple and powerful plugin API (The most simple you will find in a PHP IRC-Bot to date!)
* Support for multiple networks at a time
* Built-in and adapting management-commands (join, part, help, etc.)
* Built-in user authentication features
* Built-in permission management
* Many more

## Extend

Several plugins have already been written for Fish: 

| Plugin | Description |
| ------ | ----------- |
|[PluginTools](https://github.com/nkreer/PluginTools)| Helps you with thh packaging of your plugins |
|[Permissions](https://github.com/nkreer/Permissions)| Changes users' permissions on IRC |
|[Scripts](https://github.com/nkreer/Fish-Scripts)| Enables you to add simple custom commands to the bot |
| Many more, closed source plugins | Weather, Reminders, YouTube, etc. (I might open source them later) |

If you want me to add your plugin to the list, open a pull request or message me

## API Documentation

You can find a tutorial on how to write your own plugins in this repository's [wiki](https://github.com/nkreer/Fish/wiki).
If you have any questions regarding the API, feel free to send me an E-Mail.

## Contribute

All contributions and Pull Requests are welcome. Just stick to the original coding style wherever possible.