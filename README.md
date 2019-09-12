# Form server

PHP app that generates HTML forms from YAML descriptions. It will save and optionally email the submitted data.

The goal is to gather user-specific data. The initial submissions may be incomplete and can be updated at a later time without the need for additional authentication mechanisms or databases.

Each form is accessed via a direct link, which contains the form ID. 

## Usage

All forms are served from subdirectories of `data/`. Create a

The [reference](doc/index.md) details all available options.

## Current limitations

 * The YAML files are not validated. If your configuration cannot be translated into an HTML form, you will simply see an error on the form page.
 * Only one signature field per form will work.

## Install

```bash
composer install
```

Configure your server: the `public` directory should be your document root.

## Dev

Test run on the built-in PHP server

```bash
composer start
```

Go to `http://localhost:8181`
