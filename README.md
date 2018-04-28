# Acquired-api-sdk-php

## Description ##
The Acquired API Library for PHP enables you to work with Acquired APIs.

## Directory ##
```html
|--example  
    auth_.html
    auth_.php
    refund.html
    refund.php
    ...
|--lib  
    AcquiredCommon.php
    AcquiredConfig.php
    AcquiredException.php
    |--service
        HandlePub.php
        AuthHandle.php
        AuthOnlyHandle.php
        CaptureHandle.php
        AuthCaptureHandle.php
        ...
|--public  
    |--css
        general.css
    |--js
|--logs
readme.md  
index.php
``` 

## Documentation  ##
https://docs.acquired.com/api.php

## Installation ##
You can use Composer or simply Download the Release

## Composer ##
The preferred method is via composer. Follow the [installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have composer installed.
Once composer is installed, execute the following command in your project root to install this library:

```php
composer require Acquired/php-api-library:dev-master
```

## Examples ##
#### Get start

1. set config parameters in AcquiredConfig.php.
2. move the example directory to you web root.
3. require the below file in the example files if you use composer.

```php
require_once __DIR__ . '/../vendor/autoload.php';
```

#### How to use
It is very simply to use like this:
1. new a obj accoding to your transaction type.
```php
use Acquired\Service\AuthHandle;
$auth = new AuthHandle();
```
2. set parameters.
```php
$auth->setParam("amount",1);
```
3. post parameters.
```php
$result = $auth->postJson();
```
4. deal response.
```php
$response_hash = $auth->generateResHash($result);
if($reponse_hash == $result['response_hash']){
    
    // do your job.
    
}
```

## Requirements

PHP 5.3+  
Curl
