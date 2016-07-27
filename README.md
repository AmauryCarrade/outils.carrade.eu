# [amaury.carrade.eu](https://amaury.carrade.eu)

Repository for my website. License: CeCILL-B.


## Installation & update

This website is created using Silex, and the dependencies are managed by Composer.


### Installation

1. Clone the repository.
   
   ```bash
   git clone --recursive https://github.com/AmauryCarrade/Website.git
   ```
2. [Install Composer](https://getcomposer.org).
3. Install the dependencies.
    
    ```bash
    php composer.phar install -a
    ```
4. Setup URL rewriting like in the `.htaccess` file in this repository, basically redirecting all non-existant files to `/index.php`, to avoid URLs like `root/index.php/path.html`.

The `/web/` directory must be exposed by the webserver.

For the chat highlighter ([`/chat_highlighter`](https://amaury.carrade.eu/chat_highlighter)) to work, you'll have to install the submodules (`git submodule init`), have the `exec` PHP function enabled in your settings, and have Python 3.3 or later available on your system through the `python3` command.


### Update

1. Update the repository.

    ```bash
    git pull
    ```
2. Update the dependencies and the optimized autoloader.

    ```bash
    php composer.phar install -a
    ```
