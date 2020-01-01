
# Cronnit

A free service for making scheduled posts to Reddit. It's available at
https://cronnit.us or you can download the code and host it yourself!

## Installation

### Quick Install

If you have a fresh server you can run the install script:

    wget https://raw.githubusercontent.com/krisives/cronnit.us/master/install.sh
    nano install.sh # Enter your hostname, client_id, etc.
    sudo ./install.sh

This will:

* Install all the packages needed for Apache, MySQL, PHP, etc.
* Create a 2GB swapfile to ensure the server doesn't run out of memory
* Create the Linux user `cronnit`
* Create a `cronnit` MySQL database
* Create a `cronnit` MySQL user (with a secure random 30 character password)
* Grant permissions for `cronnit` to use the database
* Import the Cronnit SQL schema
* Disable insecure Apache modules (`mod_status`, `mod_userdir`)
* Acquire an SSL certificate from LetsEncrypt using `certbot`
* Configure Apache to run Cronnit including the SSL certificate
* Downloads the latest version of Cronnit from GitHub
* Generates a `config.php` script for Cronnit
* Installs all of the composer dependencies
* Creates a CRON job to run the `cron.sh` script
* Creates a CRON job to update from GitHub every day
* Configures the system to install security updates automatically

Before running the script you should ensure the DNS record for your domain name
point to the IP running this server.

For `client_id` and `client_secret` you will need to
[create a Reddit app](https://www.reddit.com/prefs/apps) using a redirect URI
of `https://example.com/authorize` ensure you are using `https://` in the URI.

You can skip using `https://` and `certbot` if you want by running the script
with the `HTTP_ONLY` environment variable set:

    sudo HTTP_ONLY=1 ./install.sh

### Development Server

If you want to run Cronnit using the PHP development server:

    sudo apt install php-cli php-sqlite3 composer
    git clone git@github.com:/krisives/cronnit.us.git
    cd cronnit
    composer update
    cp config.php.example config.php
    nano config.php
    cd public_html/
    php -S localhost:8080

## Donate

If you find Cronnit useful as a tool or source please consider
[making a donation](https://cronnit.us/donate)!
