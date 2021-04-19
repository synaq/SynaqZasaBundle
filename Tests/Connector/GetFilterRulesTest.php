<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class GetFilterRulesTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     * @throws SoapFaultException
     */
    public function delegatesAuthenticationOnce()
    {
        $this->connector->getFilterRules(null);
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function delegatesAuthenticationForTheGivenAccountName()
    {
        $this->connector->getFilterRules('foo@bar.com');
        $this->connector->shouldHaveReceived('delegateAuth')->with('foo@bar.com');
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyAccountName()
    {
        $this->connector->getFilterRules('bar@bar.com');
        $this->connector->shouldHaveReceived('delegateAuth')->with('bar@bar.com');
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function postsGetFilterRulesRequest()
    {
        $this->connector->getFilterRules(null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<GetFilterRulesRequest.*\\/>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function returnsFormattedFilterRules()
    {
        $rules = $this->connector->getFilterRules(null);
        $this->assertEquals([
            [
                'name' => 'Archive_Read',
                'active' => true,
                'test_condition' => 'any',
                'tests' =>
                [
                    [
                        'test' => 'header',
                        'stringComparison' => 'matches',
                        'header' => 'from',
                        'index' => '0',
                        'value' => '*'
                    ]
                ],
                'actions' => [
                    [
                        'action' => 'flag',
                        'flagName' => 'read',
                        'index' => '0'
                    ]
                ]
            ]
        ], $rules);
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function formatsAnyResponseFromZimbra()
    {
        $xmlResponse = '
      <GetFilterRulesResponse xmlns="urn:zimbraMail">
        <filterRules>
          <filterRule name="Forward some stuff" active="1">
            <filterTests condition="anyof">
              <headerTest stringComparison="contains" header="subject" index="0" value="[forward.me]"/>
            </filterTests>
            <filterActions>
              <actionRedirect a="foo@bar.com" index="0" copy="0"/>
              <actionStop index="1"/>
            </filterActions>
          </filterRule>
          <filterRule name="Some message body" active="1">
            <filterTests condition="anyof">
              <bodyTest index="0" value="Some text"/>
            </filterTests>
            <filterActions>
              <actionDiscard index="0"/>
              <actionStop index="1"/>
            </filterActions>
          </filterRule>
          <filterRule name="Some compound rule" active="1">
            <filterTests condition="allof">
              <addressTest stringComparison="contains" part="all" header="from" index="0" value="foo@bar.com"/>
              <headerTest stringComparison="contains" header="X-Zimbra-DL" index="1" value="bar@bar.com"/>
              <bodyTest index="2" value="some text"/>
            </filterTests>
            <filterActions>
              <actionDiscard index="0"/>
              <actionStop index="1"/>
            </filterActions>
          </filterRule>
        </filterRules>
      </GetFilterRulesResponse>';

        $response = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$xmlResponse.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($response);

        $rules = $this->connector->getFilterRules(null);
        $this->assertEquals([
            [
                'name' => 'Forward some stuff',
                'active' => true,
                'test_condition' => 'any',
                'tests' =>
                    [
                        [
                            'test' => 'header',
                            'stringComparison' => 'contains',
                            'header' => 'subject',
                            'index' => '0',
                            'value' => '[forward.me]'
                        ]
                    ],
                'actions' => [
                    [
                        'action' => 'redirect',
                        'a' => 'foo@bar.com',
                        'index' => '0',
                        'copy' => '0'
                    ],
                    [
                        'action' => 'stop',
                        'index' => '1'
                    ]
                ]
            ],
            [
                'name' => 'Some message body',
                'active' => true,
                'test_condition' => 'any',
                'tests' =>
                    [
                        [
                            'test' => 'body',
                            'index' => '0',
                            'value' => 'Some text'
                        ]
                    ],
                'actions' => [
                    [
                        'action' => 'discard',
                        'index' => '0'
                    ],
                    [
                        'action' => 'stop',
                        'index' => '1'
                    ]
                ]
            ],
            [
                'name' => 'Some compound rule',
                'active' => true,
                'test_condition' => 'all',
                'tests' =>
                    [
                        [
                            'test' => 'address',
                            'stringComparison' => 'contains',
                            'part' => 'all',
                            'header' => 'from',
                            'index' => '0',
                            'value' => 'foo@bar.com'
                        ],
                        [
                            'test' => 'header',
                            'stringComparison' => 'contains',
                            'header' => 'X-Zimbra-DL',
                            'index' => '1',
                            'value' => 'bar@bar.com'
                        ],
                        [
                            'test' => 'body',
                            'index' => '2',
                            'value' => 'some text'
                        ]
                    ],
                'actions' => [
                    [
                        'action' => 'discard',
                        'index' => '0'
                    ],
                    [
                        'action' => 'stop',
                        'index' => '1'
                    ]
                ]
            ]
        ], $rules);
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
