# PHP MyPlex Client
PHP client library for accessing myPlex and Plex Media Centre metadata using the MyPlex API.

In it's very infant stages at the moment but I will be extending functionality as I can.

[![Code Climate](https://codeclimate.com/github/Cheezykins/PHPMyPlexClient/badges/gpa.svg)](https://codeclimate.com/github/Cheezykins/PHPMyPlexClient)

API Documentation
=================

Full [API documentation](https://cheezykins.github.io/PHPMyPlexClient/docs) from ApiGen is available.

Basic Usage
===========

### Log in and retrieve servers.

```php
use PHPMyPlex\MyPlex;
use PHPMyPlex\DirectoryViews as DirectoryViews;

$myPlex = new MyPlex('MyPlex username', 'MyPlex password');

$myServers = $myPlex->getServers();

foreach ($myServers as $myServer)
{
  $myServer->getURL(); // Get the URL for the server.
  $myServer->name; // Get the friendly name for the server.
}
```

### Library sections
```php
// Get all sections (Libraries) within the Plex Server
$sections = $myServer->getSections();
// Get all items in the Movies library.
$library = $myServer->getSection($sections['Movies'], DirectoryViews\MovieDirectoryView::ALL);
$movies = $library->movies();
foreach ($movies as $movie) {
  $movie->title; // Get the movie title.
}
```

### Semantic syntax and Lazy Loading
```php
$library = $myServer->getSection('TV Shows', DirectoryViews\TVDirectoryView::RECENTLY_VIEWED_SHOWS);

$show = $library->show('The Big Bang Theory')->load();
printf("%s has %s seasons available", $show->title, count($show->seasons()));

$seasons = $show->seasons()->loadAll();
foreach ($seasons as $season) {
  $episodes = $season->episodes();
  foreach ($episodes as $episode) {
    $season->title; // Season title
    $episode->index; // episode number
    $episode->title; // episode title
  }
}

$show = $library->show('Game of Thrones')->load();
printf("%s contains %s episodes", $show->season('Season 5')->title, $show->season('Season 5')->leafCount);
```

### Current sessions
```php
$sessionContainer = $server->getSessions();

foreach ($sessionContainer->sessions() as $session)
{
  if ($session->type == 'episode') {
    $session->child('User')->title; // Display name of session user
    $session->grandparentTitle; // Show name
    $session->parentIndex; // Season number
    $session->index; // Episode number
  }
  else if ($session->type == 'movie')
  {
    $session->child('User')->title; // Display name of session user
  }
  $session->title; // Title of epsiode or movie.
  $session->child('TranscodeSession')->progress // Current percentage through the transcode session
}
```

Installation
============

Install using [composer](https://getcomposer.org/), you can require cheezykins/phpmyplexclient in your composer.json and it will manage installation automatically. It uses the standard PSR-0 autoloader for classes.

Dependencies
============

Depends upon the following:
 + [Httpful](https://github.com/nategood/httpful)
 + [Key-Value-Store](https://github.com/webmozart/key-value-store)

These should be satisfied automatically if installed using composer.

License
=======

This code is released under the [MIT License](http://opensource.org/licenses/MIT)