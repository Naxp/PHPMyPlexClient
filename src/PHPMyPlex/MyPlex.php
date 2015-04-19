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
 * Description of MyPlex
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

    public function __construct($userName, $password, $myPlexURL = 'https://plex.tv/users/sign_in.xml')
    {
        $request = new Request($myPlexURL);

        $request->clientIdentifier = uniqid('PHPMyPlex_');

        $request->setAuthentication($userName, $password);

        try {
            $response = $request->create('post')->send();
        } catch (Httpful\Exception\ConnectionErrorException $e) {
            throw new Exceptions\MyPlexDataException('Unable to connect to endPoint: ' . $e->getMessage(), 0, $e);
        }

        $this->errorCheck($response);

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

    public function errorCheck($response)
    {
        if ($response->hasErrors()) {
            if ($response->code == 401) {
                throw new Exceptions\MyPlexAuthenticationException((string) $response->body->error);
            }

            $error = 'Error code ' . $response->code . 'recieved from server';
            if (isset($response->body->error)) {
                $error .= ': ' . (string) $response->body->error;
            }
            throw new Exceptions\MyPlexDataException($error);
        }
    }

    public function getServers($endPoint = 'https://plex.tv/pms/servers.xml')
    {
        if (!$this->authenticationToken) {
            throw new Exceptions\MyPlexAuthenticationException("No authentication token exists, have you signed in to MyPlex?");
        }

        $request = new Request($endPoint);
        $request->setHeader('X-Plex-Token', $this->authenticationToken);

        try {
            $response = $request->create('get')->send();
        } catch (Httpful\Exception\ConnectionErrorException $e) {
            throw new Exceptions\MyPlexDataException('Unable to connect to endPoint: ' . $e->getMessage(), 0, $e);
        }

        $this->errorCheck($response);
        $data = $response->body;
        
        var_dump($data);
        die();

        $servers = [];

        foreach ($data->Server as $serverData) {
            $attributes = [];
            foreach ($serverData->attributes() as $key => $value) {
                $attributes[$key] = (string) $value;
            }

            $server = new PlexServer();
            $server->attributes = $attributes;
            $servers[] = $server;
        }

        return $servers;
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    protected function parseAttributes($attributes)
    {
        $return = [];
        foreach ($attributes as $key => $value) {
            $return[$key] = (string) $value;
        }
        return $return;
    }

    protected function parseIDs($elements)
    {
        $return = [];
        foreach ($elements as $element) {
            $return[] = (string) $element->attributes()['id'];
        }
    }
}
