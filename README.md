# Fish

Fish is an IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3](LICENSE).

## Installation

Fish has been tested on 

* Mac OS X 10.11 El Capitan
* Debian 8 (Jessie)

and is working with PHP 7.0.x. I am not providing support for PHP 5.

To install Fish, make sure you have composer installed. Simply clone the repository and run
> composer install

## Extend

Several plugins have already been written for Fish: 

- [Scripts](https://github.com/nkreer/Fish-Scripts) enables you to add simple custom commands to the bot, written in any language
- Many more, closed source plugins

If you want me to add your plugin to the list, open a pull request or message me

## Features

Fish offers various (already usable!) features for interaction with one or more IRC channels or servers: 

- Multi-Server support
- Fully object oriented plugin API to extend it to your needs
- Event-System
- Scheduler
- and more!

## TODO

- Support the entire IRC Protocol
- Add more events for plugins
- Add user authentication
- Add a real commands system
- Implement "help" commands right into the bot
- Implement full plugin reloading without having to restart the software

## Contribute

All contributions and Pull Requests are welcome. Just stick to the original coding style, please.

## Plugins

Fish was designed with plugins in mind.
Using Fish's very simple plugin API, you can extend the software to your needs.
You can find a small tutorial on plugins [here](http://nkreer.github.io/Fish).
I aim to provide backwards-compatibility for all plugins throughout all minor releases of the software.