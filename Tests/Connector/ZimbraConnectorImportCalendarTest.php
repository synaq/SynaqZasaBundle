<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2017/09/21
 * Time: 15:27
 */

namespace Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class ZimbraConnectorImportCalendarTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     */
    public function performsDelegatedAuthOnce()
    {
        $this->connector->importCalendar(null, null);
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    /**
     * @test
     */
    public function performsDelegatedAuthOnTheGivenAccount()
    {
        $this->connector->importCalendar('foo@bar.com', null);
        $this->connector->shouldHaveReceived('delegateAuth')->with('foo@bar.com');
    }

    /**
     * @test
     */
    public function acceptsAnyAccountForDelegatedAuth()
    {
        $this->connector->importCalendar('bar@baz.com', null);
        $this->connector->shouldHaveReceived('delegateAuth')->with('bar@baz.com');
    }

    /**
     * @test
     * @expectedException \Synaq\ZasaBundle\Exception\DelegatedAuthDeniedException
     * @expectedExceptionMessage Could not delegate authentication for foo@bar.com
     */
    public function throwsDelegatedAuthDeniedExceptionIfDelegatedAuthFails()
    {
        $this->connector->shouldReceive('delegateAuth')->andReturn(false);
        $this->connector->importCalendar('foo@bar.com', null);
    }

    /**
     * @test
     * @expectedException \Synaq\ZasaBundle\Exception\DelegatedAuthDeniedException
     * @expectedExceptionMessage Could not delegate authentication for bar@baz.com
     */
    public function accuratelyReportsAccountNameIfDelegatedAuthFails()
    {
        $this->connector->shouldReceive('delegateAuth')->andReturn(false);
        $this->connector->importCalendar('bar@baz.com', null);
    }

    /**
     * @test
     */
    public function sendsRawHttpRequestOnce()
    {
        $this->connector->importCalendar(null, null);
        $this->client->shouldHaveReceived('request')->once();
    }

    /**
     * @test
     */
    public function sendsPostRequest()
    {
        $this->connector->importCalendar(null, null);
        $this->client->shouldHaveReceived('request')->with('POST', m::any(), m::any());
    }

    /**
     * @test
     */
    public function sendsRequestToAccountCalendarServiceUrlUnderConfiguredRestHostWithDelegatedAuthToken()
    {
        $this->connector = new ZimbraConnector(
            $this->client,
            null,
            null,
            null,
            true,
            __DIR__.'/Fixtures/token',
            'https://some-store.some-domain.com'
        );
        $this->expectDelegatedAuthAndReturnToken('some-delegated-auth-token');
        $this->connector->importCalendar('foo@bar.com', null);
        $this->client->shouldHaveReceived('request')->with(
            m::any(),
            'https://some-store.some-domain.com/service/home/foo@bar.com/calendar?fmt=ics&auth=qp&zauthtoken=some-delegated-auth-token',
            m::any()
        );
    }

    /**
     * @test
     */
    public function acceptsAnyRestUrlAndAccountAndAuthTokenCombination()
    {
        $this->connector = new ZimbraConnector(
            $this->client,
            null,
            null,
            null,
            true,
            __DIR__.'/Fixtures/token',
            'http://any-store.any-domain.com'
        );
        $this->expectDelegatedAuthAndReturnToken('any-delegated-auth-token');
        $this->connector->importCalendar('bar@baz.com', null);
        $this->client->shouldHaveReceived('request')->with(
            m::any(),
            'http://any-store.any-domain.com/service/home/bar@baz.com/calendar?fmt=ics&auth=qp&zauthtoken=any-delegated-auth-token',
            m::any()
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->connector = m::mock(
            '\Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]',
            array($this->client, null, null, null, true, __DIR__.'/Fixtures/token')
        );
        $this->connector->shouldReceive('delegateAuth')->andReturn(
            array(
                'authToken' => null,
                'lifetime' => null
            )
        )->byDefault();
        $this->connector->shouldIgnoreMissing();
    }

    private function expectDelegatedAuthAndReturnToken($token)
    {
        $this->expectSuccessfulPostWithResponseBody(
            "<DelegateAuthResponse xmlns=\"urn:zimbraAdmin\">
                <authToken>
                    {$token}
                </authToken>
                <lifetime>3600000</lifetime>
            </DelegateAuthResponse>"
        );
    }

    private function expectSuccessfulPostWithResponseBody($body)
    {
        $response = $this->buildSuccessfulSoapResponseWithBody($body);
        $this->client->shouldReceive('post')->once()->andReturn($response);
    }

    private function buildSuccessfulSoapResponseWithBody($body)
    {
        $response = $this->buildRawHttpOkHeader();
        $response .=
            '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Header>
                <context xmlns="urn:zimbra">
                    <change token="14213"/>
                </context>
            </soap:Header>
            <soap:Body>';
        $response .= $body;
        $response .=
            '    </soap:Body>
             </soap:Envelope>';

        return new Response($response);
    }

    private function buildRawHttpOkHeader()
    {
        $httpHead = "HTTP/1.1 200 OK\r\n";
        $httpHead .= "Date: Wed, 07 Aug 2013 11:09:37 GMT\r\n";
        $httpHead .= "Expires: Thu, 01 Jan 1970 00:00:00 GMT\r\n";
        $httpHead .= "Content-Type: text/xml;charset=UTF-8\r\n";
        $httpHead .= "Cache-Control: no-store, no-cache\r\n";
        $httpHead .= "Content-Length: 519\r\n";
        $httpHead .= "\r\n";

        return $httpHead;
    }
}