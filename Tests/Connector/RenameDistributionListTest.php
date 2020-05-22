<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class RenameDistributionListTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function sendsOnePostRequestToZimbra()
    {
        $this->connector->renameDistributionList(null, null);
        $this->client->shouldHaveReceived('post')->once();
    }

    /**
     * @test
     */
    public function sendsTheRequestForTheGivenId()
    {
        $expectedMessage = '<id>some-id</id>';
        $this->connector->renameDistributionList('some-id', null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) use ($expectedMessage) {
            return strpos($body, $expectedMessage) > 0;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     */
    public function acceptsAnyId()
    {
        $expectedMessage = '<id>any-old-id</id>';
        $this->connector->renameDistributionList('any-old-id', null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) use ($expectedMessage) {
            return strpos($body, $expectedMessage) > 0;
        }), m::any(), m::any(), m::any());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->client->shouldReceive('post')->andReturn($this->genericResponse())->byDefault();
        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__.'/Fixtures/token');
    }

    private function genericResponse()
    {
        $message = <<<EOF
<RenameDistributionListResponse xmlns="urn:zimbraAdmin">
  <dl name="some.list@domain.com" dynamic="0" id="some-id">
    <a n="zimbraMailAlias">some.ignored.alias@domain.com</a>
    <a n="zimbraHideInGal">FALSE</a>
    <a n="uid">some.list</a>
    <a n="mail">some.list@domain.com</a>
    <a n="mail">some.ignored.alias@domain.com</a>
    <a n="zimbraId">some-id</a>
    <a n="objectClass">zimbraDistributionList</a>
    <a n="objectClass">zimbraMailRecipient</a>
    <a n="zimbraMailHost">some.host.domain.com</a>
    <a n="zimbraCreateTimestamp">20200514140427Z</a>
    <a n="zimbraMailStatus">enabled</a>
  </dl>
</RenameDistributionListResponse>
EOF;

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
