<?php
/*
 * The MIT License
 *
 * Copyright 2015 Chris Stretton <cstretton@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace PHPMyPlex;

use PHPMyPlex\Containers as Containers;
use PHPMyPlex\DirectoryViews as DirectoryViews;

/**
 * Models the plex server. Provides the basis of requesting sections and subsections within each server as well as
 * high level server details.
 * 
 * TODO: Add server control options and library management 
 *
 * @author Chris Stretton <cstretton@gmail.com>
 */
class PlexServer
{

    private $accessToken = false;
    private $name = false;
    private $address = false;
    private $port = false;
    private $version = false;
    private $scheme = false;
    private $host = false;
    private $localAddresses = false;
    private $machineIdentifier = false;
    private $createdAt = false;
    private $updatedAt = false;
    private $owned = false;
    private $synced = false;
    
    private $sectionMappings = [];

    public function __set($name, $value)
    {
        if ($name == 'attributes') {
            foreach ($value as $attribute => $attributeValue) {
                $this->{$attribute} = $attributeValue;
            }
        } else if (isset($this->{$name})) {
            $this->{$name} = $value;
        }
    }
    
    public function __get($name)
    {
        if ($name != 'accessToken' && isset($this->{$name}))
        {
            return $this->{$name};
        }
    }

    public function getUrl()
    {
        $url = $this->scheme;
        $url .= '://';
        $url .= $this->address;
        $url .= ':';
        $url .= $this->port;
        return $url;
    }

    public function __toString()
    {
        return $this->name . ' - ' . $this->getUrl();
    }

    public function loadContainer($path = '/library')
    {
        $url = $this->getUrl() . $path;

        $request = new Request($url);
        $request->token = $this->accessToken;

        $response = $request->send('get');

        return new Containers\MediaContainer($response->body);
    }
    
    public function loadSections($path = '/library/sections')
    {
        $response = $this->loadContainer($path);
        foreach ($response->children() as $child)
        {
            if ($child->hasKey()) {
                $this->sectionMappings[$child->getDetailStruct()['title']] = $child->getDetailStruct()['key'];
            }
        }
        return $this->sectionMappings;
    }
    
    public function loadSection($key, $directory = '', $path = '/library/sections')
    {
        if (!\ctype_digit($key)) {
            if (!\array_key_exists($key, $this->sectionMappings))
            {
                throw new Exceptions\MyPlexDataException("Provided key {$key} does not exist");
            }
            $key = $this->sectionMappings[$key];
        }
        $url = $path . '/' . $key;
        if ($directory != DirectoryViews\DirectoryView::NONE)
        {
            $url .= '/' . $directory;
        }
        return $this->loadContainer($url);
    }
}
