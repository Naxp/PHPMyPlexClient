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

/**
 * Description of PlexServer
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
    
    public function __set($name, $value)
    {
        if ($name == 'attributes')
        {
            foreach ($value as $attribute => $attributeValue)
            {
                $this->{$attribute} = $attributeValue;
            }
        }
        else if (isset($this->{$name}))
        {
            $this->{$name} = $value;
        }
    }
    
    public function getUrl($includeToken = false)
    {
        $url = $this->scheme;
        $url .= '://';
        $url .= $this->address;
        $url .= ':';
        $url .= $this->port;
        $url .= '/';
        if ($includeToken)
        {
            $url .= '?auth_token=' . urlencode($this->accessToken);
        }
        return $url;
    }
    
    public function __toString()
    {
        return $this->name . ' - ' . $this->getUrl();
    }
    
    public function getSections()
    {
        
    }
}
