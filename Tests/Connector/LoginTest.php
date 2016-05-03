<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2016/03/14
 * Time: 08:13
 */

namespace Synaq\ZasaBundle\Tests\Connector;


use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class LoginTest extends ZimbraConnectorTestCase
{
    /**
     * @test
     */
    public function shouldRequestAuthTokenIfAuthTokenNotPresentInClass()
    {
        $authResponse = '<AuthResponse xmlns="urn:zimbraAdmin">
                                <authToken>0_a503cf41a251d0468edc9f2ce885c31c939668f7_69643d33363a65306661666438392d313336302d313164392d383636312d3030306139356439386566323b6578703d31333a313435373937393739383633343b61646d696e3d313a313b747970653d363a7a696d6272613b7469643d393a3330393336323831393b</authToken>
                                <lifetime>43200000</lifetime>
                            </AuthResponse>';

        $this->client->shouldReceive('post')->andReturn(
            new Response($this->httpOkHeaders.$this->soapHeaders.$authResponse.$this->soapFooters)
        );

        $expected = '    <AuthRequest xmlns="urn:zimbraAdmin">'. "\n" .
                    '      <name>admin-user</name>' ."\n" .
                    '      <password>admin-pass</password>' . "\n" .
                    '    </AuthRequest>';

        $connector = new ZimbraConnector($this->client, null, 'admin-user', 'admin-pass');
        $connector->login();
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function($actual) use ($expected) {

            return strstr($actual, $expected) !== false;
        }), m::any(), m::any(), m::any());
    }
}