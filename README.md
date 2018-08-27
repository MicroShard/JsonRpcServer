[![Build Status](https://travis-ci.org/MicroShard/JsonRpcServer.svg?branch=master)](https://travis-ci.org/MicroShard/JsonRpcServer)
[![codecov](https://codecov.io/gh/MicroShard/JsonRpcServer/branch/master/graph/badge.svg)](https://codecov.io/gh/MicroShard/JsonRpcServer)

# JsonRpcServer
A lightweight JsonRpc Server Bundle for PHP

### Usage

To setup the server you need to create an instance of a Server which requires two parameters. 
First parameter is a Directory object which holds all request handlers. 
The second parameter is an authenticator object implementing the Security\AuthenticatorInterface.

```php
$directory = new Directory();
// add handlers to the directory

$authenticator = new AllowAllAuthenticator();

$server = new Server($directory, $authenticator);
```

To run the server and handle any incoming request you just have to pass a valid Psr7 http request that implements the Psr\Http\Message\ServerRequestInterface object.
Most Frameworks like Symfony, Laravel or Phalcon already use a compatible Request implementation. 
Otherwise you can use guzzlehttp/guzzle for an implementation of the ServerRequestInterface.    

```php
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$server->run($request);
```

#### Request Handler

Request handlers have to implement the HandlerInterface which only requires the implementation of one `handle(Request $request)` method.
All handlers need to be registered at a Directory for the Server to use it. There are two ways of registering a handler.  
Possibility one is to register an existing handler instance.

```php
$handler = new ExampleHandler();
$directory->addHandler('resource', 'method', 1, $handler);
```
The alternative is to register a callback that creates the handler if it's actually requested, which is the better way so you don't have to create a bunch of handler objects, from which only one is usually used per request. 
```php
$directory->addHandlerDefinition('resource', 'method', 1, function(){
    return new ExampleHandler();
});
```
In both cases you have to define 3 additional parameters when registering a handler: **resource**, **method** and **version**.
resource and method are simple identifiers which are used in the requests to identify which handler needs to be picked from the Directory to handle the incoming request.
The version is an integer which allows you to versionize your handlers if you want/need to. If you don't need it, just put a default 1 in it. If you ever have to change a handler and have multiple clients that send requests to your server, it will come in handy.   

### Request Format
The request format is very simple and for the most part universal for all requests. As the name indicates, requests are expected to be in json format, an object with the following fields:
<table>
<tr><td><b>Field</b></td><td><b>Required</b></td><td><b>Description</b></td></tr>
<tr><td>resource</td><td>Yes</td><td>name of the resource to select the right handler</td></tr>
<tr><td>method</td><td>Yes</td><td>name of the method for the resource to select the right handler</td></tr>
<tr><td>version</td><td>No</td><td>if the request does not contain a version, the server will use the latest available for the given resource and method</td></tr>
<tr><td>id</td><td>No</td><td>a simple identifier that will be send back in the response</td></tr>
<tr><td>auth</td><td>No</td><td>authentication data is not required by default, it depends on the Authenticator you want to use in the Server</td></tr>
<tr><td>payload</td><td>No</td><td>this is the part where all the data the handler needs to handle the request, should be stored.</td></tr>
</table>

an example could be:
```json
{
  "resource": "email",
  "method": "send",
  "auth": {
    "token": "some_auth_token"
  },
  "payload": {
    "recipient": "some@mail.com",
    "subject": "some subject",
    "body": "some message body"
  }
}
```

### Response Format
Like the request format, the response is a json object with the following fields:
<table>
<tr><td><b>Field</b></td><td><b>Description</b></td></tr>
<tr><td>resource</td><td>the resource from the request</td></tr>
<tr><td>method</td><td>the method from the request</td></tr>
<tr><td>version</td><td>the version from the request, if non was given it will contain the value <b>latest</b> which is alo a valid value for requests but has the same effect as ommiting the version in the request</td></tr>
<tr><td>id</td><td>the id from the request, if one was given</td></tr>
<tr><td>status</td><td>a http status code - <b>200</b> if everything was ok</td></tr>
<tr><td>message</td><td>a response message - <b>OK</b> if everything was ok, otherwise a short info describing the error</td></tr>
<tr><td>error</td><td>this field is only present if the status is not 200. If so, it contains an error code</td></tr>
<tr><td>payload</td><td>the result from the handler which processed the request.</td></tr>
</table>

### Client
You don't have to write your own client to communicate with the server, you can use the `microshard/jsonrpcclient` bundle.<br/>
[github](https://github.com/MicroShard/JsonRpcClient)  