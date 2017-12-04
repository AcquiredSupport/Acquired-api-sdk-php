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
    Acquired.Common.php
    Acquired.Config.php
    Acquired.Exception.php
    Acquired.Helper.php
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
You can simply Download the Release

## Examples ##
#### Get start

1. set config parameters in Acquired.config.php.
2. require the below file in the example files.

```php
require_once('../lib/Acquired.Helper.php');
```

#### How to use
It is very simply to use like this:
1. new a obj accoding to your transaction type.
```php
$auth = new Auth_pub();
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
