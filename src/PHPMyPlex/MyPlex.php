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

use PHPMyPlex\Exceptions as Exceptions;

/**
 * The MyPlex class is used to provide connectivity to the MyPlex API. It handles the login and authentication tokens
 * needed for subsequent calls. It can also provide a list of servers available within the account.
 *
 * TODO: Add support for playlists, queues and home management provided by MyPlex.
 * 
 * @author Chris Stretton <cstretton@gmail.com>
 */
class MyPlex
{

    private $email;
    private $id;
    private $thumb;
    private $username;
    private $title;
    private $cloudSyncDevice;
    private $locale;
    private $authenticationToken;
    private $pin;
    private $restricted;
    private $home;
    private $queueEmail;
    private $queueUid;
    private $maxHomeSize;
    private $subscription;
    private $roles;
    private $allEntitlements;
    private $entitlements;
    private $proxy = false;

    /**
     * Defines a connection to the MyPlex services, requires your myplex username and password
     * 
     * Optionally takes a Proxy object defining proxy connection details and an alternative endpoint
     * URL for the myPlex login endpoint.
     * 
     * @param string $userName
     * @param string $password
     * @param Proxy|boolean $proxy = false
     * @param string $myPlexURL = 'https://plex.tv/users/sign_in.xml'
     */
    public function __construct($userName, $password, $proxy = false, $myPlexURL = 'https://plex.tv/users/sign_in.xml')
    {
        $this->proxy = $proxy;
        $request = new Request($myPlexURL, $proxy);
        $request->clientIdentifier = uniqid('PHPMyPlex_');
        $request->setAuthentication($userName, $password);

        $response = $request->send('post');

        $data = $response->body;

        foreach ($data->attributes() as $key => $value) {
            $this->{$key} = (string) $value;
        }

        $this->subscription = $this->parseAttributes($data->subscription->attributes());

        $this->subscription['features'] = $this->parseIDs($data->subscription->feature);
        $this->roles = $this->parseIDs($data->roles->role);
        $this->entitlements = $this->parseIDs($data->entitlements->entitlement);
        $this->allEntitlements = (bool) $data->entitlements->attributes()['all'];
    }

    /**
     * Returns a collection of the myplex servers you have access to
     * This includes both your own and shared plex servers.
     * @param type $endPoint
     * @return \PHPMyPlex\PlexServerCollection
     * @throws Exceptions\MyPlexAuthenticationException
     */
    public function getServers($endPoint = 'https://plex.tv/pms/servers.xml')
    {
        if (!$this->authenticationToken) {
            throw new Exceptions\MyPlexAuthenticationException("No authentication token exists, have you signed in to MyPlex?");
        }

        $request = new Request($endPoint);
        $request->token = $this->authenticationToken;

        $response = $request->send('get');

        $data = $response->body;

        $servers = [];

        foreach ($data->Server as $serverData) {
            $attributes = [];
            foreach ($serverData->attributes() as $key => $value) {
                $attributes[$key] = (string) $value;
            }

            $server = new PlexServer($this->proxy);
            $server->attributes = $attributes;
            $servers[\strtolower($server->name)] = $server;
        }

        return new PlexServerCollection($servers);
    }

    /**
     * Helper method to retrieve attributes of the plex server.
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    /**
     * Parses all of the attributes returned from myplex and puts them into
     * the appropriate member variables.
     * @param array $attributes
     * @return array
     */
    protected function parseAttributes($attributes)
    {
        $return = [];
        foreach ($attributes as $key => $value) {
            $return[$key] = (string) $value;
        }
        return $return;
    }

    /**
     * Gets the IDs of the servers under MyPlex.
     * @param \SimpleXMLElement $elements
     */
    protected function parseIDs($elements)
    {
        $return = [];
        foreach ($elements as $element) {
            $return[] = (string) $element->attributes()['id'];
        }
    }
}
