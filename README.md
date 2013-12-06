SynaqZasaBundle
===============

A Symfony2 wrapper for the Zimbra SOAP Admin API (ZASA). 
From Zimbra 7 and below, no wsdl document is provided for using the Zimbra Admin SOAP API. This bundle uses a custom XML builder to post SOAP requests over curl to solve this problem.

This bundle should work with Zimbra 7 & 8

## Requirements

* PHP 5.3 with curl support
* Symfony 2.1 or greater

## Installation

### Step 1
Use composer to manage your dependencies and download SynaqZasaBundle:
``` bash
$ php composer.phar update synaq/zasa-bundle
```

### Step 2
Add the bundles to your AppKernel
```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Synaq\ZasaBundle\SynaqZasaBundle(),
        new Synaq\CurlBundle\SynaqCurlBundle(),
        // ...
    );
}
```

### Step 3
Add the bundle configuration
```yml
synaq_zasa:
    server: your-zimbra-server.com
    admin_user: your-admin-user
    admin_pass: your-admin-password
    
synaq_curl:
    cookie_file: false
    options: { CURLOPT_RETURNTRANSFER: true, CURLOPT_SSL_VERIFYPEER: false, CURLOPT_SSL_VERIFYHOST: false, CURLOPT_SSL_CIPHER_LIST: %curl_cipher_list% }
```
