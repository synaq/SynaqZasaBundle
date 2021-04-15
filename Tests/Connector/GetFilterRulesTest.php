<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class GetFilterRulesTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     */
    public function delegatesAuthenticationOnce()
    {
        $this->connector->getFilterRules('foo@bar.com');
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    protected function setUp()
    {
        parent::setUp();

        $defaultXmlResponse = '
          <GetFilterRulesResponse xmlns="urn:zimbraMail">
            <filterRules>
              <filterRule name="Archive_Read" active="1">
                <filterTests condition="anyof">
                  <headerTest stringComparison="matches" header="from" index="0" value="*"/>
                </filterTests>
                <filterActions>
                  <actionFlag flagName="read" index="0"/>
                </filterActions>
              </filterRule>
            </filterRules>
          </GetFilterRulesResponse>';

        $defaultResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$defaultXmlResponse.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($defaultResponse)->byDefault();

        $this->connector = m::mock(
            'Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]',
            [$this->client, null, null, null, true, __DIR__.'/Fixtures/token']
        );
        $this->connector->shouldIgnoreMissing();
    }
}
