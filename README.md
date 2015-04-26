# PHP MyPlex Client
PHP client library for accessing myPlex and Plex Media Centre metadata using the MyPlex API.

In it's very infant stages at the moment but I will be extending functionality as I can.

[![Code Climate](https://codeclimate.com/github/Cheezykins/PHPMyPlexClient/badges/gpa.svg)](https://codeclimate.com/github/Cheezykins/PHPMyPlexClient)

**Basic usage**

```php
use PHPMyPlex\MyPlex;
use PHPMyPlex\DirectoryViews as DirectoryViews;

$myPlex = new MyPlex('MyPlex username', 'MyPlex password');
printf("Signed in as %s", $myPlex->username);

list($myServer) = $myPlex->getServers();
printf("Server %s at %s", $myServer->name, $myServer->getURL());

// Get all sections (Libraries) within the Plex Server
$sections = $myServer->getSections();
// Get all items in the Movies library.
$library = $myServer->getSection($sections['Movies'], DirectoryViews\MovieDirectoryView::ALL);
$movies = $library->movies();
foreach ($movies as $movie) {
  echo $movie->title;
}

// Syntax is flexible and semantic.
// Lazy loading is used where possible to reduce the web service calls required to plex

$library = $myServer->getSection('TV Shows', DirectoryViews\TVDirectoryView::RECENTLY_VIEWED_SHOWS);

$show = $library->show('The Big Bang Theory')->load();
printf("%s has %s seasons available", $show->title, count($show->seasons()));

$seasons = $show->seasons()->loadAll();
foreach ($seasons as $season) {
  $episodes = $season->episodes();
  foreach ($episodes as $episode) {
    printf("%s episode %s - %s", $season->title, $episode->index, $episode->title);
  }
}

$show = $library->show('Game of Thrones')->load();
printf("%s contains %s episodes", $show->season('Season 5')->title, $show->season('Season 5')->leafCount);
```



**Installation**

Install using [composer](https://getcomposer.org/), you can require cheezykins/phpmyplexclient in your composer.json and it will manage installation automatically. It uses the standard PSR-0 autoloader for classes.

**Dependencies**

Depends upon [Httpful](https://github.com/nategood/httpful) - this should be satisfied automatically if installed with composer.