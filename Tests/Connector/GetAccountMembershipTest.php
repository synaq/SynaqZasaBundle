<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class GetAccountMembershipTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsGetAccountMembershipRequestForGivenId()
    {
        $this->connector->getAccountMembership('some-account-id');
        $this->client->shouldHaveReceived('post')->with(m::any(), '/<GetAccountMembershipRequest xmlns="urn:zimbraAdmin">.*<account by="id">some-account-id<\/account>.*<\/GetAccountMembershipRequest>/s', m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyGivenId()
    {
        $this->connector->getAccountMembership('any-account-id');
        $this->client->shouldHaveReceived('post')->with(m::any(), '/<GetAccountMembershipRequest xmlns="urn:zimbraAdmin">.*<account by="id">any-account-id<\/account>.*<\/GetAccountMembershipRequest>/s', m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function returnsAnArrayWithTheListOfDls()
    {
        $getAccountMembershipSoapResponse =
            <<<'XML'
<GetAccountMembershipResponse xmlns="urn:zimbraAdmin">
  <dl name="foo@bar.com" dynamic="0" id="some-id"/>
  <dl name="bar@bar.com" dynamic="0" id="some-other-id" via="foo@bar.com"/>
</GetAccountMembershipResponse>
XML;

        $this->zimbraReturnsResponse($getAccountMembershipSoapResponse);
        $result = $this->connector->getAccountMembership('any-account-id');
        $this->assertEquals([
            [
                'name' => 'foo@bar.com',
                'id' => 'some-id'
            ],
            [
                'name' => 'bar@bar.com',
                'id' => 'some-other-id',
                'via' => 'foo@bar.com'
            ]
        ], $result);
    }

    protected function setUp()
    {
        parent::setUp();

        $getAccountMembershipSoapResponse =
            <<<'XML'
<GetAccountMembershipResponse xmlns="urn:zimbraAdmin">
  <dl name="foo@bar.com" dynamic="0" id="some-id"/>
  <dl name="bar@bar.com" dynamic="0" id="some-other-id" via="foo@bar.com"/>
</GetAccountMembershipResponse>
XML;

        $getAccountMembershipResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$getAccountMembershipSoapResponse.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($getAccountMembershipResponse)->byDefault();

        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__ . '/Fixtures/token');
    }

    /**
     * @param $getAccountMembershipSoapResponse
     */
    private function zimbraReturnsResponse($getAccountMembershipSoapResponse)
    {
        $getAccountMembershipResponse = new Response(
            $this->httpOkHeaders . $this->soapHeaders . $getAccountMembershipSoapResponse . $this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($getAccountMembershipResponse);
    }
}
