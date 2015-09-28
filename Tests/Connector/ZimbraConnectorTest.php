<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class ZimbraConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Wrapper | m\Mock
     */
    private $httpClient;

    /**
     * @var ZimbraConnector
     */
    private $connector;


    public function setup()
    {
        $this->httpClient = \Mockery::mock('Synaq\CurlBundle\Curl\Wrapper');
        $this->httpClient->shouldReceive('post')->once()->andReturn($this->buildSuccessfulAdminAuthResponse());

        $server = 'https://my-server.com:7071/service/admin/soap';
        $username = 'admin@my-server.com';
        $password = 'my-password';
        $this->connector = new ZimbraConnector($this->httpClient, $server, $username, $password);
        //uncomment the below to use a real server,
        //replacing the credentials with with your server auth details.
        //You will have to comment the mocks in individual tests as well
//        $httpClient = new Wrapper(null, false, true, false, array(
//            'CURLOPT_RETURNTRANSFER' => true,
//            'CURLOPT_SSL_VERIFYPEER' => false,
//            'CURLOPT_SSL_VERIFYHOST' => false
//        ), array());
//        $this->connector = new ZimbraConnector($httpClient, 'https://mweb.synaq.com:7071/service/admin/soap', 'admin@demo.synaq.com', '!@synaq()');
    }

    private function buildSuccessfulAdminAuthResponse()
    {
        return $this->buildSuccessfulSoapResponseWithBody(
            '<AuthResponse xmlns="urn:zimbraAdmin">
                <authToken>
                    dummy_auth_token
                </authToken>
                <lifetime>43200000</lifetime>
            </AuthResponse>');
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

    public function testDelegateAuth()
    {
        $this->expectDelegatedAuth();
        $response = $this->connector->delegateAuth('user1@testdomain2.co.za.archive');
        $this->assertEquals('dummy_delegate_auth_token',
            $response['authToken']);
        $this->assertEquals('3600000', $response['lifetime']);
    }

    private function expectDelegatedAuth()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<DelegateAuthResponse xmlns="urn:zimbraAdmin">
                <authToken>
                    dummy_delegate_auth_token
                </authToken>
                <lifetime>3600000</lifetime>
            </DelegateAuthResponse>'
        );
    }

    private function expectSuccessfulPostWithResponseBody($body)
    {
        $response = $this->buildSuccessfulSoapResponseWithBody($body);
        $this->httpClient->shouldReceive('post')->once()->andReturn($response);
    }

    public function testAddDlToDl()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<GetDistributionListResponse total="8" more="0" xmlns="urn:zimbraAdmin">
                    <dl dynamic="0" id="3800ee3c-8fdc-4395-92c6-8ebf26399d0e" name="all_members@testdomain1.co.za">
                        <a n="uid">all_members</a>
                        <a n="mail">all_members@testdomain1.co.za</a>
                        <a n="zimbraMailStatus">enabled</a>
                        <a n="zimbraMailHost">cms-ah-zcs-cluster.synaq.com</a>
                        <a n="zimbraId">3800ee3c-8fdc-4395-92c6-8ebf26399d0e</a>
                        <a n="zimbraCreateTimestamp">20130725125042Z</a>
                        <a n="objectClass">zimbraDistributionList</a>
                        <a n="objectClass">zimbraMailRecipient</a>
                        <a n="zimbraMailAlias">all_members@testdomain1.co.za</a>
                        <dlm>all_members@testdomain2.co.za</dlm>
                        <dlm>user1@testdomain1.co.za</dlm>
                        <dlm>user2@testdomain1.co.za</dlm>
                        <dlm>user3@testdomain1.co.za</dlm>
                        <dlm>user4@testdomain1.co.za</dlm>
                        <dlm>user5@testdomain1.co.za</dlm>
                        <dlm>user6@testdomain1.co.za</dlm>
                        <dlm>user{1..}@testdomain1.co.za</dlm>
                    </dl>
                </GetDistributionListResponse>'
        );
        $this->expectSuccessfulPostWithResponseBody('<AddDistributionListMemberResponse xmlns="urn:zimbraAdmin"/>');

        $id = $this->connector->getDlId('all_members@testdomain1.co.za');
        $this->connector->addDlMember($id, 'all_members@testdomain2.co.za');
    }

    public function testGetAllCoses()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<GetAllCosResponse xmlns="urn:zimbraAdmin">
                    <cos id="150dbb00-ecba-431c-a239-98c69cf42b5f" name="amadeus">
                        <a n="zimbraFeatureNotebookEnabled">FALSE</a>
                        <a n="zimbraFeatureSkinChangeEnabled">TRUE</a>
                    </cos>
                    <cos id="150dbb00-ecba-431c-a239-98c69cf42b52" name="test-cos">
                        <a n="zimbraFeatureNotebookEnabled">FALSE</a>
                        <a n="zimbraFeatureSkinChangeEnabled">TRUE</a>
                    </cos>
                </GetAllCosResponse>'
        );

        $coses = $this->connector->getAllCoses();

        $this->assertEquals('amadeus', $coses[0]['name']);
        $this->assertEquals('test-cos', $coses[1]['name']);
    }

    public function testRevokeRight()
    {
        $this->expectSuccessfulPostWithResponseBody('<RevokeRightResponse xmlns="urn:zimbraAdmin"/>');
        $response = $this->connector->revokeRight('basic-pop-imap-2gb', 'cos',
            'zimbradomainadmins@fixture-test-portal.co.za', 'grp', 'listCos');
        $this->assertEquals('', $response);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: no such grant: [grantee name=zimbradomainadmins@fixture-test-portal.co.za, grantee id=19a65c8c-aa73-4014-9165-b535970d95f0, grantee type=grp, right=listCos]
     */
    public function testSoapFault()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<soap:Fault>
                    <soap:Code>
                        <soap:Value>soap:Sender</soap:Value>
                    </soap:Code>
                    <soap:Reason>
                        <soap:Text>no such grant: [grantee name=zimbradomainadmins@fixture-test-portal.co.za, grantee id=19a65c8c-aa73-4014-9165-b535970d95f0, grantee type=grp, right=listCos]
                        </soap:Text>
                    </soap:Reason>
                    <soap:Detail>
                        <Error xmlns="urn:zimbra">
                            <Code>account.NO_SUCH_GRANT</Code>
                            <Trace>
                                qtp1456226908-260:https://192.168.3.104:7071/service/admin/soap:1380612910563:4fa889d922e219b3
                            </Trace>
                        </Error>
                    </soap:Detail>
                </soap:Fault>'
        );

        $this->connector->revokeRight('basic-pop-imap-2gb', 'cos', 'zimbradomainadmins@fixture-test-portal.co.za',
            'grp', 'listCos');
    }


    public function testEnableArchive()
    {
        $this->expectSuccessfulPostWithResponseBody('<EnableArchiveResponse xmlns="urn:zimbraAdmin"/>');
        $response = $this->connector->enableArchive('user1@testdomain3.co.za', 'user1@testdomain3.co.za.archive',
            'zimbra-archive-cos');
        $this->assertEquals('', $response);
    }

    public function testAddArchiveReadFilterRule()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody('<ModifyFilterRulesResponse xmlns="urn:zimbraMail"/>');
        $this->connector->addArchiveReadFilterRule('user1@testdomain3.co.za.archive');
    }

    public function testGetFolder()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<GetFolderResponse xmlns="urn:zimbraMail">
                    <folder rev="1" i4next="3" i4ms="1" ms="1" n="0" activesyncdisabled="0" l="1" id="2" s="0"
                            name="Inbox"
                            uuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" view="message"
                            luuid="a9a09b64-dce6-495d-886a-355efc6d8055"/>
                </GetFolderResponse>'
        );

        $this->connector->getFolder('user1@testdomain3.co.za.archive', 2);
    }

    public function testCreateFolder()
    {
        $this->expectDelegatedAuth();
        $accountName = 'user01@testdomain3.co.za';
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateFolderResponse xmlns="urn:zimbraMail">
                  <folder i4ms="16" rev="16" i4next="266" ms="16" l="1" uuid="6009ed8b-fc82-489c-97e3-3bd4080670e0"
                    n="0" luuid="1dcfb61c-90e9-4a64-91e4-dd8b48fd6898" activesyncdisabled="0" absFolderPath="/Test"
                    s="0" name="Test" id="265" webOfflineSyncDays="0"/>
                </CreateFolderResponse>'
        );

        $id = $this->connector->createFolder($accountName, "Test", 1);
        $this->assertEquals(265, $id, "Incorrect folder ID returned");
    }

    public function testCreateMountPoint()
    {
        $this->expectDelegatedAuth();
        $raw = $this->buildRawHttpOkHeader();
        $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <session id="160">160</session>
                            <change token="1207"/>
                            <notify seq="1">
                                <created>
                                    <link reminder="0" rev="1207" oname="Inbox" ms="1207" n="0" activesyncdisabled="0" l="1"
                                          ruuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" perm="r" id="260" s="0" rid="2"
                                          zid="815e06c0-63d4-499e-bc47-69f7ae8171f2" name="Archive"
                                          owner="user1@testdomain3.co.za.archive" view="message"
                                          uuid="545114e4-f738-452d-afdb-d6bfdd26b052" luuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                                </created>
                                <modified>
                                    <folder id="1" uuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                                </modified>
                            </notify>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <CreateMountpointResponse xmlns="urn:zimbraMail">
                            <link reminder="0" rev="1207" oname="Inbox" ms="1207" n="0" activesyncdisabled="0" l="1"
                                  ruuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" perm="r" id="260" s="0" rid="2"
                                  zid="815e06c0-63d4-499e-bc47-69f7ae8171f2" name="Archive" owner="user1@testdomain3.co.za.archive"
                                  view="message" uuid="545114e4-f738-452d-afdb-d6bfdd26b052"
                                  luuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                        </CreateMountpointResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
        $response = new Response($raw);

        $this->httpClient->shouldReceive('post')->once()->andReturn($response);
        $this->connector->createMountPoint('user1@testdomain3.co.za', 0, 'Archive', '/Inbox',
            'user1@testdomain3.co.za.archive', 'message');
    }

    public function testDisableArchive()
    {
        $this->expectSuccessfulPostWithResponseBody('<DisableArchiveResponse xmlns="urn:zimbraAdmin"/>');
        $response = $this->connector->disableArchive('user1@testdomain2.co.za');
        $this->assertEquals('', $response);
    }

    public function testCreateGalSyncAccount()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateGalSyncAccountResponse xmlns="urn:zimbraAdmin">
                    <account id="224f142a-41ba-4aea-9005-ab1dcbc68f1c" name="galsync@test-cos.com"/>
                </CreateGalSyncAccountResponse>'
        );
        $response = $this->connector->createGalSyncAccount('galsync@test-cos.com', 'test-cos.com');
        $this->assertEquals(array(
            'account' => array(
                '@value' => '',
                '@attributes' => array(
                    'id' => '224f142a-41ba-4aea-9005-ab1dcbc68f1c',
                    'name' => 'galsync@test-cos.com'
                )
            )
        ), $response);
    }

    public function testCreateAliasDomain()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateDomainResponse xmlns="urn:zimbraAdmin">
                    <domain id="69e5e6c5-fb88-4ba3-acd3-c8139379b284" name="test-alias.com">
                        <a n="zimbraId">69e5e6c5-fb88-4ba3-acd3-c8139379b284</a>
                        <a n="zimbraMailCatchAllAddress">@test-alias.com</a>
                        <a n="zimbraDomainType">alias</a>
                        <a n="zimbraDomainAliasTargetId">d5c53785-889d-4e8e-b809-4b30c5b00ad9</a>
                    </domain>
                </CreateDomainResponse>'
        );
        $response = $this->connector->createAliasDomain('test-alias.com', 'test.com');
        $this->assertEquals('69e5e6c5-fb88-4ba3-acd3-c8139379b284', $response);
    }

    public function testGetDomain()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<GetDomainResponse xmlns="urn:zimbraAdmin">
                    <domain id="69e5e6c5-fb88-4ba3-acd3-c8139379b284" name="test-alias.com">
                        <a n="zimbraId">69e5e6c5-fb88-4ba3-acd3-c8139379b284</a>
                        <a n="zimbraDomainName">test-alias.com</a>
                        <a n="zimbraDomainStatus">active</a>
                        <a n="zimbraDomainType">alias</a>
                        <a n="zimbraDomainAliasTargetId">d5c53785-889d-4e8e-b809-4b30c5b00ad9</a>
                    </domain>
                </GetDomainResponse>'
        );
        $response = $this->connector->getDomain('test-alias.com');
        $this->assertEquals('69e5e6c5-fb88-4ba3-acd3-c8139379b284', $response['zimbraId']);
        $this->assertEquals('test-alias.com', $response['zimbraDomainName']);
        $this->assertEquals('active', $response['zimbraDomainStatus']);
        $this->assertEquals('alias', $response['zimbraDomainType']);
        $this->assertEquals('d5c53785-889d-4e8e-b809-4b30c5b00ad9', $response['zimbraDomainAliasTargetId']);
    }

    public function testGetAccountsOneAccount()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                    <account name="test-account@test-domain.com" id="bc85eaf1-dfe0-4879-b5e0-314979ae0009">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">TRUE</a>
                        <a n="attribute-1">value-2</a>
                    </account>
                </GetAllAccountsResponse>'
        );

        $response = $this->connector->getAccounts('synaq.com');
        $this->assertArrayHasKey('test-account@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account@test-domain.com']['attribute-2']);
    }

    public function testGetAccountMultiple()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                    <account name="test-account@test-domain.com" id="dummy-id">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">TRUE</a>
                        <a n="attribute-1">value-2</a>
                    </account>
                    <account name="test-account2@test-domain.com" id="dummy-id2">
                        <a n="attribute-1">value-1</a>
                        <a n="attribute-2">TRUE</a>
                        <a n="attribute-1">value-2</a>
                    </account>
                </GetAllAccountsResponse>'
        );

        $response = $this->connector->getAccounts('synaq.com');
        $this->assertArrayHasKey('test-account@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account@test-domain.com']['attribute-2']);

        $this->assertArrayHasKey('test-account2@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account2@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account2@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account2@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account2@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account2@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account2@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account2@test-domain.com']['attribute-2']);
    }

    public function testCreateDomain()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateDomainResponse xmlns="urn:zimbraAdmin">
                    <domain id="dummy-domain-id" name="dummy-domain.com"/>
                </CreateDomainResponse>'
        );
        $attr = array(
            'zimbraDomainStatus' => 'active',
            'zimbraPrefTimeZoneId' => '(GMT+02.00) Harare / Pretoria',
            'description' => 'domain description',
            'zimbraDomainDefaultCOSId' => 'DUMMY-COS-ID',
            'zimbraVirtualHostname' => 'mail.dummy-domain.com',
            'zimbraPublicServiceHostname' => 'mail.dummy-domain.com',
        );
        $id = $this->connector->createDomain('dummy-domain.com', $attr);
        $this->assertEquals('dummy-domain-id', $id);
    }

    public function testCreateDl()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateDistributionListResponse xmlns="urn:zimbraAdmin">
                    <dl id="dummy-dl-id" name="zimbradomainadmins@dummy-domain.com"/>
                </CreateDistributionListResponse>'
        );

        $attr = array(
            'zimbraHideInGal' => 'TRUE',
            'zimbraIsAdminGroup' => 'TRUE',
            'zimbraMailStatus' => 'disabled'
        );
        $views = array(
            'accountListView',
            'aliasListView',
            'resourceListView',
            'DLListView'
        );
        $id = $this->connector->createDl('zimbradomainadmins@dummy-domain.com', $attr, $views);
        $this->assertEquals('dummy-dl-id', $id);
    }

    public function testGrantRight()
    {
        $this->expectSuccessfulPostWithResponseBody('<GrantRightResponse xmlns="urn:zimbraAdmin"/>');
        $this->connector->grantRight('dummy-domain.com', 'domain', 'zimbradomainadmins@dummy-domain.com', 'grp',
            'getAccount', 0);
    }

    public function testCreateMailbox()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateAccountResponse xmlns="urn:zimbraAdmin">
                    <account id="dummy-account-id" name="test-account@dummy-domain.com">
                        <a n="zimbraMailHost">sample-host.sample-domain.com</a>
                        <a n="zimbraMailTrashLifetime">30d</a>
                    </account>
                </CreateAccountResponse>'
        );
        $attr = array(
            'displayName' => 'Joe Schmoe',
            'givenName' => 'Joe',
            'sn' => 'Schmoe',
            'zimbraPasswordMustChange' => 'TRUE',
            'zimbraIsDelegatedAdminAccount' => 'FALSE',
            'zimbraHideInGal' => 'FALSE',
            'zimbraCOSId' => 'dummy-cos-id',
            'description' => 'dummy description',
            'company' => 'Acme Ltd'
        );
        $id = $this->connector->createAccount('test-account@dummy-domain.com', 'dummy-password', $attr, $returnAttrs);
        $this->assertEquals('dummy-account-id', $id);
        $this->assertInternalType('array', $returnAttrs, "Return attributes not array");
        $this->assertArrayHasKey('zimbraMailHost', $returnAttrs, "Zimbra mail host not returned");
        $this->assertEquals('sample-host.sample-domain.com', $returnAttrs['zimbraMailHost'],
            "Incorrect Zimbra mail host returned");
        $this->assertArrayHasKey('zimbraMailTrashLifetime', $returnAttrs, "Zimbra trash lifetime not returned");
        $this->assertEquals('30d', $returnAttrs['zimbraMailTrashLifetime'], "Incorrect Zimbra trash lifetime returned");
    }

    public function testCreateMailboxIgnoreProperties()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateAccountResponse xmlns="urn:zimbraAdmin">
                    <account id="dummy-account-id" name="test-account@dummy-domain.com">
                        <a n="zimbraMailHost">sample-host.sample-domain.com</a>
                        <a n="zimbraMailTrashLifetime">30d</a>
                    </account>
                </CreateAccountResponse>'
        );
        $attr = array(
            'displayName' => 'Joe Schmoe',
            'givenName' => 'Joe',
            'sn' => 'Schmoe',
            'zimbraPasswordMustChange' => 'TRUE',
            'zimbraIsDelegatedAdminAccount' => 'FALSE',
            'zimbraHideInGal' => 'FALSE',
            'zimbraCOSId' => 'dummy-cos-id',
            'description' => 'dummy description',
            'company' => 'Acme Ltd'
        );
        $id = $this->connector->createAccount('test-account@dummy-domain.com', 'dummy-password', $attr);
        $this->assertEquals('dummy-account-id', $id);
    }

    public function testAddDlMember()
    {
        $this->expectSuccessfulPostWithResponseBody('<AddDistributionListMemberResponse xmlns="urn:zimbraAdmin"/>');
        $this->connector->addDlMember('dummy-dl-id', 'test-account@dummy-domain.com');
    }

    public function testGetAccountQuotaUsed()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<GetInfoResponse docSizeLimit="10485760" attSizeLimit="10240000" xmlns="urn:zimbraAccount">
                    <used>932</used>
                    <attrs>
                        <attr name="zimbraDeviceLockWhenInactive">FALSE</attr>
                        <attr name="zimbraMailQuota">0</attr>
                    </attrs>
                </GetInfoResponse>'
        );
        $quota = $this->connector->getAccountQuotaUsed('test@test-domain19.com');
        $this->assertEquals('932/0', $quota);
    }

    public function testCreateContact()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<GetFolderResponse xmlns="urn:zimbraMail">
                    <folder i4ms="1" rev="1" i4next="2" ms="1" l="11" uuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" n="0"
                            luuid="03bef865-57aa-44ca-bc85-922b03f742f5" activesyncdisabled="0" absFolderPath="/" s="0"
                            name="USER_ROOT" id="1" webOfflineSyncDays="0">
                        <folder i4ms="1" rev="1" i4next="17" ms="1" l="1" uuid="93d4cd09-c226-4606-92bc-407f27e6164d" n="0"
                                luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Briefcase"
                                view="document" s="0" name="Briefcase" id="16" webOfflineSyncDays="0"/>
                        <folder i4ms="171" rev="1" i4next="262" ms="1" l="1" uuid="63939fe0-7fe7-4e20-8cb9-dee09ab6e813" n="2"
                                luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Contacts"
                                view="contact" s="0" name="Contacts" id="7" webOfflineSyncDays="0"/>
                    </folder>
                </GetFolderResponse>'
        );

        $this->expectSuccessfulPostWithResponseBody(
            '<CreateContactResponse xmlns="urn:zimbraMail">
            <cn fileAsStr="last, first" rev="181" d="1424264251000" id="262" l="7">
                <a n="firstName">first</a>
                <a n="lastName">last</a>
                <a n="email">test@test.com</a>
            </cn>
        </CreateContactResponse>'
        );
        $id = $this->connector->createContact('test@test.com',
            array('firstName' => 'first', 'lastName' => 'last', 'email' => 'test@test.com'), null);
        $this->assertEquals('262', $id);
    }

    public function testCreateContactCustomFolder()
    {
        $this->expectDelegatedAuth();
        $accountName = 'test@test-domain19.com';
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateContactResponse xmlns="urn:zimbraMail">
                    <cn fileAsStr="last, first" rev="181" d="1424264251000" id="262" l="13">
                        <a n="firstName">first</a>
                        <a n="lastName">last</a>
                        <a n="email">test@test.com</a>
                    </cn>
                </CreateContactResponse>'
        );
        $this->connector->createContact($accountName,
            array('firstName' => 'first', 'lastName' => 'last', 'email' => 'test@test.com'), 13);
    }

    public function testCreateSignature()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateSignatureResponse xmlns="urn:zimbraAccount">
            <signature name="Primary" id="b7f7d8d2-da88-4da4-8572-84f1408f0696"/>
        </CreateSignatureResponse>'
        );
        $id = $this->connector->createSignature('test@test-domain19.com', 'Primary', 'text/plain', 'Signature content');
        $this->assertEquals('b7f7d8d2-da88-4da4-8572-84f1408f0696', $id);
    }

    public function testRenameAccount()
    {
        $this->expectSuccessfulPostWithResponseBody(
            '<RenameAccountResponse xmlns="urn:zimbraAccount">
            <account name="updated-test2@displayname2.com" id="dummy-id"/>
        </RenameAccountResponse>'
        );
        $id = 'dummy-id';
        $newAddress = 'updated-test2@displayname1.com';
        $this->connector->renameAccount($id, $newAddress);
    }

    public function testGetAllTags()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<GetTagResponse xmlns="urn:zimbraMail">
      <tag color="9" name="tag1" id="cc024fcf-ef49-4b71-9948-f66fb48a0252:264" n="1"/>
    </GetTagResponse>'
        );
        $tags = $this->connector->getAllTags('test@test.com');

        $this->assertArrayHasKey(0, $tags);
        $this->assertArrayHasKey('color', $tags[0]);
        $this->assertEquals('9', $tags[0]['color']);
        $this->assertArrayHasKey('name', $tags[0]);
        $this->assertEquals('tag1', $tags[0]['name']);
        $this->assertArrayHasKey('id', $tags[0]);
        $this->assertEquals('cc024fcf-ef49-4b71-9948-f66fb48a0252:264', $tags[0]['id']);
    }

    public function testGetAllTagsMultiple()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<GetTagResponse xmlns="urn:zimbraMail">
      <tag color="9" name="tag1" id="cc024fcf-ef49-4b71-9948-f66fb48a0252:264" n="1"/>
      <tag color="9" name="tag2" id="tag-id:265" n="1"/>
    </GetTagResponse>'
        );
        $tags = $this->connector->getAllTags('test@test.com');
        $this->assertArrayHasKey(0, $tags);
        $this->assertArrayHasKey('color', $tags[0]);
        $this->assertEquals('9', $tags[0]['color']);
        $this->assertArrayHasKey('name', $tags[0]);
        $this->assertEquals('tag1', $tags[0]['name']);
        $this->assertArrayHasKey('id', $tags[0]);
        $this->assertEquals('cc024fcf-ef49-4b71-9948-f66fb48a0252:264', $tags[0]['id']);

        $this->assertArrayHasKey(1, $tags);
        $this->assertArrayHasKey('color', $tags[1]);
        $this->assertEquals('9', $tags[1]['color']);
        $this->assertArrayHasKey('name', $tags[1]);
        $this->assertEquals('tag2', $tags[1]['name']);
        $this->assertArrayHasKey('id', $tags[1]);
        $this->assertEquals('tag-id:265', $tags[1]['id']);
    }

    public function testCreateTag()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateTagResponse xmlns="urn:zimbraMail">
      <tag name="tag4" id="tag-id:281"/>
    </CreateTagResponse>'
        );
        $tagId = $this->connector->createTag('test@test.com', 'tag4');
        $this->assertEquals('tag-id:281', $tagId);
    }

    public function testTagContact()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<ContactActionResponse xmlns="urn:zimbraMail">
<action op="tag" id="300"/>
</ContactActionResponse>'
        );
        $contactId = '300';
        $this->connector->tagContact('test@test.com', $contactId, '281');
    }

    public function testSetPassword()
    {
        $this->expectSuccessfulPostWithResponseBody('<SetPasswordResponse xmlns="urn:zimbraAdmin"/>');
        $this->connector->setPassword('cc024fcf-ef49-4b71-9948-f66fb48a0252', '!@synaq()ABC');
    }

    public function testCreateIdentity()
    {
        $this->expectDelegatedAuth();
        $this->expectSuccessfulPostWithResponseBody(
            '<CreateIdentityResponse xmlns="urn:zimbraAccount">
              <identity name="test2@test.com" id="2cb9fced-f39b-4c76-a5da-ca1ae13b20b7">
                <a name="zimbraPrefIdentityId">2cb9fced-f39b-4c76-a5da-ca1ae13b20b7</a>
                <a name="objectClass">zimbraIdentity</a>
                <a name="zimbraCreateTimestamp">20150925092836Z</a>
                <a name="zimbraPrefIdentityName">test2@test.com</a>
              </identity>
            </CreateIdentityResponse>'
        );
        $accountName = 'test@test.com';
        $name = 'Alias test22@test.com';
        $fromAddress = 'test22@test.com';
        $fromDisplay = 'Test Persona 22';
        $this->connector->createIdentity($accountName, $name, $fromAddress, $fromDisplay);
    }
}
