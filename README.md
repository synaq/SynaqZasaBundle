SynaqZasaBundle
===============

A Symfony2 wrapper for the Zimbra SOAP Admin API (ZASA). 
This bundle uses a custom XML builder to post SOAP requests over CURL or using the fopen() wrappers.

This bundle works with Zimbra 7 & 8

This bundle was written to work specifically with our business model, so some functions return non-standard output. However, it should work with many use cases.

## Requirements

* PHP 5.3 with curl support
* Symfony 2.7 or greater

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
    use_fopen: true|false
    auth_token_path: /path/to/optional/existing/auth/token
    rest_base_url: http://your-zimbra-server-rest-url-without-service-endpoint.some-host.com
    
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

Please see the ZimbraConnector class for available classes.

##Development

A Docker image with the full development environment required to
develop on this bundle can be built using the included Dockerfile
and a ready-made build script:

```
./scripts/docker/build.sh
```

This builds the synaq/zimbra-connector-dev image, which has XDebug
pre-configured for remote debugging to a local XDebug client.

For an interactive terminal session inside a container based on the
image, with the project working directory mounted as /opt/project, run:

```
./scripts/docker/terminal.sh
```