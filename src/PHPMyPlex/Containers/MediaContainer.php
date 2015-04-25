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
namespace PHPMyPlex\Containers;

use PHPMyPlex\Exceptions as Exceptions;

/**
 * Parses the response from plex and manages children and attributes of a Plex Media Container.
 *
 * @author Chris Stretton <cstretton@gmail.com>
 */
class MediaContainer
{

    protected $detailStruct;
    protected $xml;

    public function __construct(\SimpleXMLElement $data)
    {
        $this->xml = $data;
        $this->detailStruct = $this->parseMediaContainer($data);
    }

    public function children()
    {
        $children = [];
        if ($this->xml->count() > 0) {
            foreach ($this->xml->children() as $child) {
                $name = __NAMESPACE__ . '\\' . $child->getName();
                if (\class_exists($name)) {
                    $children[] = new $name($child);
                } else {
                    throw new Exceptions\MyPlexDataException("No handler exists for container {$name}");
                }
            }
        }
        return $children;
    }

    public function hasKey()
    {
        return \array_key_exists('key', $this->detailStruct);
    }

    public function getKey()
    {
        if (!$this->hasKey()) {
            throw new Exceptions\MyPlexDataException('Current ' . __CLASS__ . 'contains no key');
        }

        return $this->detailStruct['key'];
    }

    public function getDetailStruct()
    {
        return $this->detailStruct->getArrayCopy();
    }

    public function getDetailStructJSON()
    {
        return json_encode($this->detailStruct->getArrayCopy());
    }

    public function __get($name)
    {
        if (\array_key_exists($name, $this->detailStruct)) {
            return $this->detailStruct[$name];
        }
    }

    protected function parseMediaContainer(\SimpleXMLElement $node)
    {
        $response = [];

        foreach ($node->attributes() as $attributeKey => $attributeValue) {
            $response[$attributeKey] = (string) $attributeValue;
        }

        return new DetailStruct($response);
    }
}
