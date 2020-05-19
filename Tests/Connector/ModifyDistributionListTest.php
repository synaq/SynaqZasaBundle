<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class ModifyDistributionListTest extends ZimbraConnectorTestCase
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
        $this->connector->modifyDistributionList(null, []);
        $this->client->shouldHaveReceived('post')->once();
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
<ModifyDistributionListResponse xmlns="urn:zimbraAdmin">
  <dl name="foo@bar.com" dynamic="0" id="some-id">
    <a n="uid">foo</a>
    <a n="zimbraHideInGal">FALSE</a>
    <a n="mail">foo@bar.com</a>
    <a n="zimbraId">some-id</a>
    <a n="objectClass">zimbraDistributionList</a>
    <a n="objectClass">zimbraMailRecipient</a>
    <a n="zimbraMailHost">some-host.com</a>
    <a n="zimbraCreateTimestamp">20200514140427Z</a>
    <a n="zimbraMailStatus">enabled</a>
  </dl>
</ModifyDistributionListResponse>
EOF;

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
