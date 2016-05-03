<?php
/**
 * Created by PhpStorm.
 * User: nicholasp
 * Date: 2016/04/29
 * Time: 1:19 PM
 */

namespace Synaq\ZasaBundle\Tests\Connector;


use Synaq\CurlBundle\Curl\Response;;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Mockery as m;

class StoreSessionKeyTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function shouldNotAuthOnConstructionIfSessionFileIsPresent()
    {
        $this->constructConnectorWithSessionFile(__DIR__ . '/Fixtures/token');
        $this->client->shouldNotHaveReceived('post');
    }

    /**
     * @test
     */
    public function shouldStoreAuthTokenInSessionFile()
    {
        $sessionFilePath = '/tmp/test-token';

        $token = '0_a503cf41a251d0468edc9f2ce885c31c939668f7_69643d33363a65306661666438392d313336302d313164392d383636312d3030306139356439386566323b6578703d31333a313435373937393739383633343b61646d696e3d313a313b747970653d363a7a696d6272613b7469643d393a3330393336323831393b';
        $authResponse = "<AuthResponse xmlns=\"urn:zimbraAdmin\">
                                <authToken>$token</authToken>
                                <lifetime>43200000</lifetime>
                            </AuthResponse>";

        $this->client->shouldReceive('post')->andReturn(
            new Response($this->httpOkHeaders.$this->soapHeaders.$authResponse.$this->soapFooters)
        );

        $this->constructConnectorWithSessionFile($sessionFilePath);
        $this->connector->login();
        $this->assertEquals($token, file_get_contents($sessionFilePath));
    }

    /**
     * @test
     */
    public function shouldUseAuthTokenFromSessionFile()
    {
        $getAccountResponse = '<GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                    <account name="test@test.com" id="bc85eaf1-dfe0-4879-b5e0-314979ae0009">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">value-2</a>
                    </account>
                </GetAllAccountsResponse>';
        $this->client->shouldReceive('post')->andReturn(
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponse.$this->soapFooters)
        );

        $this->constructConnectorWithSessionFile(__DIR__ . '/Fixtures/token');
        $this->connector->getAccounts('test.com');

        $expected = "    <context xmlns=\"urn:zimbra\">\n" .
                    "      <authToken>dummy-auth-token</authToken>\n" .
                    "    </context>\n";
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function($actual) use ($expected) {

            return strstr($actual, $expected) !== false;
        }), m::any(), m::any(), m::any())->once();
    }

    /**
     * @test
     */
    public function shouldRetryAuthIfTokenExpired()
    {
        $getAccountResponseExpired = '<soap:Fault>
                                    <soap:Code>
                                        <soap:Value>soap:Sender</soap:Value>
                                    </soap:Code>
                                    <soap:Reason>
                                        <soap:Text>auth credentials have expired</soap:Text>
                                    </soap:Reason>
                                    <soap:Detail>
                                        <Error xmlns="urn:zimbra">
                                            <Code>service.AUTH_EXPIRED</Code>
                                            <Trace>
                                                qtp509886383-477388:https://10.1.5.145:7071/service/admin/soap:1461933712084:de2caf7d060bf3ee:SoapEngine266
                                            </Trace>
                                        </Error>
                                    </soap:Detail>
                                </soap:Fault>';
        $authResponse = "<AuthResponse xmlns=\"urn:zimbraAdmin\">
                                <authToken>dummy-token</authToken>
                                <lifetime>43200000</lifetime>
                            </AuthResponse>";
        $getAccountResponse = '<GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                    <account name="test@test.com" id="bc85eaf1-dfe0-4879-b5e0-314979ae0009">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">value-2</a>
                    </account>
                </GetAllAccountsResponse>';
        $this->client->shouldReceive('post')->andReturnValues(array(
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponseExpired.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$authResponse.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponse.$this->soapFooters)
        ));

        $this->constructConnectorWithSessionFile(__DIR__ . '/Fixtures/retry-token');
        $this->connector->getAccounts('test.com');

        $expected = '    <AuthRequest xmlns="urn:zimbraAdmin">'. "\n" .
            '      <name>admin@my-server.com</name>' ."\n" .
            '      <password>my-password</password>' . "\n" .
            '    </AuthRequest>';
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function($actual) use ($expected) {

            return strstr($actual, $expected) !== false;
        }), m::any(), m::any(), m::any())->once();
    }

    /**
     * @test
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: auth credentials have expired
     */
    public function shouldOnlyRetryAuthOnce()
    {
        $getAccountResponseExpired = '<soap:Fault>
                                    <soap:Code>
                                        <soap:Value>soap:Sender</soap:Value>
                                    </soap:Code>
                                    <soap:Reason>
                                        <soap:Text>auth credentials have expired</soap:Text>
                                    </soap:Reason>
                                    <soap:Detail>
                                        <Error xmlns="urn:zimbra">
                                            <Code>service.AUTH_EXPIRED</Code>
                                            <Trace>
                                                qtp509886383-477388:https://10.1.5.145:7071/service/admin/soap:1461933712084:de2caf7d060bf3ee:SoapEngine266
                                            </Trace>
                                        </Error>
                                    </soap:Detail>
                                </soap:Fault>';
        $authResponse = "<AuthResponse xmlns=\"urn:zimbraAdmin\">
                                <authToken>dummy-token</authToken>
                                <lifetime>43200000</lifetime>
                            </AuthResponse>";
        $getAccountResponse = '<GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                    <account name="test@test.com" id="bc85eaf1-dfe0-4879-b5e0-314979ae0009">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">value-2</a>
                    </account>
                </GetAllAccountsResponse>';
        $this->client->shouldReceive('post')->andReturnValues(array(
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponseExpired.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$authResponse.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponseExpired.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$authResponse.$this->soapFooters),
            new Response($this->httpOkHeaders.$this->soapHeaders.$getAccountResponse.$this->soapFooters),
        ));

        $this->constructConnectorWithSessionFile(__DIR__ . '/Fixtures/retry-token');
        $this->connector->getAccounts('test.com');

        $expected = '    <AuthRequest xmlns="urn:zimbraAdmin">'. "\n" .
            '      <name>admin@my-server.com</name>' ."\n" .
            '      <password>my-password</password>' . "\n" .
            '    </AuthRequest>';
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function($actual) use ($expected) {

            return strstr($actual, $expected) !== false;
        }), m::any(), m::any(), m::any())->once();
    }

    protected function constructConnectorWithSessionFile($sessionFile)
    {
        $server = 'https://my-server.com:7071/service/admin/soap';
        $username = 'admin@my-server.com';
        $password = 'my-password';

        $this->connector = new ZimbraConnector($this->client, $server, $username, $password, true, $sessionFile);
    }
}
