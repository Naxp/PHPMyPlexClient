# PHPMyPlexClient
PHP client library for accessing myPlex and Plex Media Centre metadata using the MyPlex API.

In it's very infant stages at the moment but I will be extending functionality as I can.

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
$section = $myServer->getSection($sections['Movies'], DirectoryViews\MovieDirectoryView::ALL);
```

**Installation**

Install using [composer](https://getcomposer.org/), you can require cheezykins/phpmyplexclient in your composer.json and it will manage installation automatically. It uses the standard PSR-0 autoloader for classes.
