# snippets
Tool to manage local HTML snippets

DIRECTORY STRUCTURE
-------------------

      app/assets/             contains assets definition
      app/config/             contains application configurations
      app/controllers/        contains Web controller classes
      app/models/             contains model classes
      app/runtime/            contains files generated during runtime
      app/views/              contains view files for the Web application
      data/snippets           contains all the snippets
      data/themes             contains all the themes of snippets
      vendor/                 contains dependent 3rd-party packages
      web/                    contains the entry script and Web resources


INSTALLATION
------------

### 1. Application and dependencies

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this application template using the following command:

~~~
git clone https://github.com/goncaloe/snippets
cd snippets
composer install
~~~

### 2. Configs

Copy the file local.php-orig to `.php` without `-orig` and adjust to your needs.

### 3. File Permissions

Give write permissions to following folders:
    app/runtime
    data/
    web/

### 4. Database

Create a database. By this moment you should have `config/local.php`. Specify your database connection there.

Then apply migrations by running:

```
yii migrate
```

### 5. Build Indexes

Each time you create a new snippet or theme, you should rebuild the index.
Enter in the tools link and click in "Rebuild Index" button

SNIPPETS
------------

Each snippet is a folder in data/snippets/[SNIPPET_DIR] and have the following structure:
      snippet.json           contains the snippet meta data
      index.html             contains the snippet html
      index.css              [optional] contains the styles of the snippet
      index.js               [optional] contains the javascript of the snippet

The snippet.json can have the following data:
```
{
    "name": "Creative User Profile",
    "tags": ["tags", "profile", "bootstrap3"],
    "date": "2017-09-04 17:16:18",
    "framework": "bs3"
}
```

THEMES
------------

Each theme is a folder in data/themes/[THEME_DIR] and have the following structure:
      theme.json           contains the snippet meta data
      css/                 [optional] contains all the css files of the theme
      js/                  [optional] contains the javascript files of the theme


The theme.json can have the following data:
```
{
    "name": "BS3 default",
    "framework": "bs3",
    "css": [
        "css/bootstrap.css"
    ],
    "js": [
        "js/jquery.min.js",
        "js/bootstrap.min.js"
    ]
}
```
