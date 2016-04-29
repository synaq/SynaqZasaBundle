<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2016/03/10
 * Time: 08:15
 */

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class GetDlTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    public $connector;

    /**
     * @test
     */
    public function shouldPostGetDistributionListRequestToZimbra()
    {
        $expected = '    <GetDistributionListRequest xmlns="urn:zimbraAdmin">' . "\n" .
                    '      <dl by="name">example@example.com</dl>' . "\n" .
                    '    </GetDistributionListRequest>';

        $this->connector->getDl('example@example.com');

        $this->client->shouldHaveReceived('post')->with(
            m::any(),
            m::on(function($actual) use ($expected) {

                return strstr($actual, $expected) !== false;
            }),
            array("Content-type: application/xml"),
            m::any(),
            m::any()
        )->once();
    }

    /**
     * @test
     */
    public function shouldReturnNormalizedArrayFromZimbraSoapResults()
    {
        $dl = $this->connector->getDl('some@example.com');
        $this->assertEquals('@example.com', $dl['zimbraMailCatchAllAddress']);
    }

    protected function setUp()
    {
        parent::setUp();

        $getDistributionListSoapResponse =
            '    <GetDistributionListResponse total="1" more="0" xmlns="urn:zimbraAdmin">'."\n".
            '      <dl name="example@example.com" dynamic="0" id="dummy-dl-id">'."\n".
            '        <a n="zimbraMailAlias">example@example.com</a>'."\n".
            '        <a n="zimbraHideInGal">TRUE</a>'."\n".
            '        <a n="uid">example</a>'."\n".
            '        <a n="zimbraMailCatchAllAddress">@example.com</a>'."\n".
            '        <a n="mail">example@example.com</a>'."\n".
            '        <a n="zimbraId">dummy-dl-id</a>'."\n".
            '        <a n="objectClass">zimbraDistributionList</a>'."\n".
            '        <a n="objectClass">zimbraMailRecipient</a>'."\n".
            '        <a n="zimbraMailHost">some-host.some-domain.com</a>'."\n".
            '        <a n="zimbraCreateTimestamp">20160302091859Z</a>'."\n".
            '        <a n="zimbraMailStatus">enabled</a>'."\n".
            '       <dlm>some-member@example.com</dlm>'."\n".
            '      </dl>'."\n".
            '    </GetDistributionListResponse>';

        $getDistributionListResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$getDistributionListSoapResponse.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($getDistributionListResponse)->byDefault();

        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__ . '/Fixtures/token');
    }
}