SynaqZasaBundle
===============

A Symfony2 wrapper for the Zimbra SOAP Admin API (ZASA). 
From Zimbra 7 and below, no wsdl document is provided for using the Zimbra Admin SOAP API. This bundle uses a custom XML builder to post SOAP requests over curl to solve this problem.

This bundle works with Zimbra 7 & 8

This bundle was written to work specifically with our business model, so some functions return non-standard ouput. However, it should work with many use cases.

As of Zimbra 8 a wdsl is provided, so I will be updating this bundle to use native php Soap functions going forward.

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

##Usage
You can use the Zimbra Connector by getting the 'synaq_zasa.connector' service
```php
class SomeController extends Controller
{
    public function someAction()
    {
        //...
        $connector = $this->get('synaq_zasa.connector');
        //...
    }
}
```

You can then make requests using the controller
```php
$account = $connector->getAccount('user@domain.com');
```

The following calls are available:
* countAccount
* createDomain
* deleteDomain
* getDomainId
* modifyDomain
* createAccount
* getAccount
* deleteAccount
* modifyAccount
* getAccountId
* addAccountAlias
* removeAccountAlias
* getDlId
* addDlMember
* removeDlMember
* createDl
* deleteDl
* getCosId
* getAllCoses
* grantRight
* revokeRight
* enableArchive
* delegateAuth
* addArchiveReadFilterRule
* folderGrantAction
* getFolder
* createMountPoint
* disableArchive
* createGalSyncAccount
