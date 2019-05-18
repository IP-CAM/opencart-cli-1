## Opencart Command Line Interface

Version: 0.1

> Sometimes it is neccesseary to run repetable processes,
> for example: stock or price update, data exchange with various third party ERP systems, currency rate update,
> creating large site xml, exporting, importing etc...
> You can create feeds and call them using http requests but sometimes it's running time is too much.
> The CLI helps you for it, because it is a consistent tool and you could apply for many webshop versions.
> Download only a phar file, put it anywhere in the filesystem and run it with parameters:
> It has also an option to run your own logic from command line, see: run:class param.

- path => parameter (absolute path to opencart root directory), required
- cmd => command name, required
- context => (admin or catalog), optional

Compatible with the following Opencart versions:

- 1.5.x
- 2.x
- 3.x

Usage:

php opencart-cli.phar --path=directory --cmd=command

Return:

JSON answer {result: '', value: ''}

Examples:

``` cp opencart-cli.phar /usr/local/bin ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=product:set:price --id=123 --value=120.5 ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=product:set:quantity --id=45 --value=10 ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=product:set:quantity --sku=MBXZ123 --value=22 ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=admin:user:disable --username=testuser ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=config:cache:clean ```

``` php /usr/local/bin/opencart-cli.phar --path=/var/www/html/example-shop.com --cmd=run:class --name=ExampleClass ```

## Available commands:

> admin:user:create

Create an admin user \
Params: \
required: --username, --password, --user_group_id \
optional: --lastname, --firstname, --email

> admin:user:exist

Checking admin user \
Params: \
required: --username

> admin:user:enable

Enable an admin user \
Params: \
required: --username

> admin:user:disable

Disable an admin user \
Params: \
required: --username

> product:set:price

Set product base net price \
Params: \
required: --id | --sku && --value

> product:set:quantity

Set product quantity \
Params: \
required: --id | --sku && --value

> config:cache:clean

Clean all files from cache directory \
Params:

> run:class

Create a cli directory in your root folder and put your own class into it or set the path to the cli folder.
The class name and the file name should equal.

Params: \
required: --name=classname \
optional: --path=/path/to/the/directory