#!/bin/sh

# Install Fish automatically
# Automatic installation requires cURL, unzip and composer.

V=1.0 # Specify version

wget -O fish.zip https://codeload.github.com/nkreer/Fish/zip/1.0
unzip fish.zip
cd Fish-$V
composer install