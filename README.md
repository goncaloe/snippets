# Snippets Manager
Tool to manage local HTML snippets written in PHP (based on yii2).

With this tool you can edit, save, organize, and preview all your small HTML snippets in one place.
This is the missing tool for designers to organize their snippets.

![Snippets Manager](https://webzop.com/images/pages/1fb8d70e-e33d-4fe7-986d-a56b5260cf2d.jpg)

DIRECTORY STRUCTURE
-------------------

```
app/
      assets/             contains assets resource files
      components          containing reusable user components
      config/             contains application configurations
      controllers/        contains web controller classes
      migrations          contains database migrations
      models/             contains model classes
      runtime/            contains files generated during runtime
      views/              contains view files for the Web application
      widgets/            contains application widgets
data/
      snippets/           contains all the snippets
      themes/             contains all the themes of snippets
vendor/                   contains dependent 3rd-party packages
web/                      contains the entry script and Web resources
```

INSTALLATION
------------

### 1. Application and dependencies

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).
You can then install this application using the following commands:

~~~
git clone https://github.com/goncaloe/snippets
cd snippets
composer install
~~~

### 2. File Permissions

Give write permissions to following folders:

```
app/runtime/
data/
web/assets
```

### 3. Configs

Copy the file app/config/local.php-orig to `.php` without `-orig` and adjust to your needs.
You should specify your database connection there.

```php
    //...
    'components' => [
        //...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:' . APP_BASE_PATH . '/data/database.sqlite',
        ],
    ],
```

### 4. Database

Then apply migrations by running:

```
yii migrate
```

### 5. Import Snippets

You can import some snippets and themes from [github.com/goncaloe/snippets-data](https://github.com/goncaloe/snippets-data) repository to data folder:

~~~
git clone https://github.com/goncaloe/snippets-data data
~~~

### 6. Build Indexes

Each time you create a new snippet or theme, you should rebuild the index.
Enter in the tools link and click in "Rebuild Index" button

SNIPPETS
------------

Each snippet is a folder in data/snippets/[SNIPPET_DIR] and has the following structure:

      snippet.json           contains the snippet meta data
      index.html             contains the snippet html
      index.css              [optional] contains the styles of the snippet
      index.js               [optional] contains the javascript of the snippet

## snippet.json

The metadata stored in snippet.json can have the following data:
```
{
    "name": "navbar sticky",
    "framework": "bs3",
    "tags": ["navbar", "sticky", "bootstrap3"],
    "date": "2017-09-04"
}
```

## index.html

You can store all the html in the <body> that you will have in the snippet.
If you want any external resources as css or javascript, you can put the content as described:

```html
<head>
    <script src="http://example.com/external.js"></script>
    <script src="http://example.com/external.js"></script>
</head>
<body>
    content of snippet will be here
<body>
```

THEMES
------------

A theme define the context in which snippets are to be showed.
In the application there is only one theme selected, and only shown snippets whose framework belongs to this theme.
Each theme is a folder in data/themes/[THEME_DIR] and have the following structure:

```
theme.json           contains the snippet meta data
css/                 [optional] contains all the css files of the theme
js/                  [optional] contains the javascript files of the theme
```

The theme.json can have the following data:
```
{
    "name": "lithium",
    "framework": "bs3",
    "css": [
        "css/styles.css"
    ],
    "js": [
        "js/jquery.min.js",
        "js/bootstrap.min.js"
    ]
}
```