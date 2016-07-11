<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2016/07/11
 * Time: 10:39
 */

namespace Tests\Connector;


use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class GetAccountTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function shouldReturnSingleAttributeAsArrayKey()
    {
        $accountDetails = $this->connector->getAccount('any@domain.com');
        $this->assertEquals('Sample Mailbox', $accountDetails['cn']);
    }

    /**
     * @test
     */
    public function shouldReturnMultipleAttributeAsArrayOfValues()
    {
        $accountDetails = $this->connector->getAccount('any@domain.com');
        $this->assertEquals(array('address@domain.com', 'alias1@domain.com'), $accountDetails['mail']);
    }

    protected function setUp()
    {
        parent::setUp();

        $getAccountResponseXml =
            '<GetAccountResponse xmlns="urn:zimbraAdmin">
              <account name="willemv@synaq.com" id="5cb6c0f8-187f-4839-b20b-d12fbda4e158">
                <a n="uid">sample</a>
                <a n="cn">Sample Mailbox</a>
                <a n="mail">address@domain.com</a>
                <a n="mail">alias1@domain.com</a>
                <a n="zimbraId">5cb6c0f8-187f-4839-b20b-d12fbda4e158</a>
                <a n="sn">Sample</a>
                <a n="zimbraCOSId">23a9199f-ba28-4526-9a4c-4c9fa1ff44be</a>
              </account>
            </GetAccountResponse>';

        $getAccountResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$getAccountResponseXml.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($getAccountResponse)->byDefault();

        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__ . '/Fixtures/token');

    }
}
