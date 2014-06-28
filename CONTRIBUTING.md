# How to contribute

You can make CakeResque documentation better by updating the documentation, or adding more translations.

## Requirements

The documentation website is powered by the [Slim framework](http://www.slimframework.com/), written in PHP.
But worry not, all the contents are wrtitten in pure [Markdown](http://daringfireball.net/projects/markdown/) and a little bit of HTML.

You only have to edit the markdown files, and you don't need a php server unless you want to run the entire website locally.

## Making changes

### Editing the contents

All the markdown files are located inside `src/CakeResque/Pages`.

You will find various `.md` files, corresponding to each page of the website.  
Files in that folder root are in english, and files inside the `fr` subfolder are for the french version.

Files are written in Markdown, and HTML. 

Usually, you only have to edit these files, and submit a pull request when updating the documentation.

### Editing the website structure

All assets (images, css, javascript) are located inside `src/CakeResque/webroot`, and templates files are located inside `src/CakeResque/Templates`.

We use [LESS](http://lesscss.org/) to pre-process CSS.

## How to run the website locally

The markdown files are designed to render beautifully by themselves in a markdown editor. But if you wish to have the exact website on your end, follow these steps:

* Download the gh-pages source
* Extract and open the folder
* [Install composer](https://getcomposer.org/download/), then run `composer install`
* Redirect your http server to `src/CakeResque/webroot` directory (or create virtual host)