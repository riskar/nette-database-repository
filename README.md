# Nette Database Repository

Nette Database is a library that enhances the functionality of the popular Nette/Database package by adding support for typed
entities, queries (Selections) and repositories. It provides a convenient way to define and work with strongly-typed entities in your
database-driven applications. This extension aims to improve code readability, maintainability, and reduce the risk of errors related to
data types.

## Installation

The best way to install this extension is using [Composer](http://getcomposer.org/):

```sh
$ composer require efabrica/nette-database-repository
```

Register our 

Execute our repository code generation command:
```sh
$ php bin/console ndr:generate
```

## Usage

### Entity

The `Entity` class extends `ActiveRow` but is made to be used as a base class for your entities. 
It is supposed to be generated by our code generation tool, but you can also write it manually.

```php
