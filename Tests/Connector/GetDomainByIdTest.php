<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2017/09/05
 * Time: 15:06
 */

namespace Tests\Connector;


use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class GetDomainByIdTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function postsGetDomainRequestWithByIdAttributeAndGivenDomainIdInjected()
    {
        $expected = <<<XML
    <GetDomainRequest xmlns="urn:zimbraAdmin">
      <domain by="id">some-zimbra-domain-id</domain>
    </GetDomainRequest>
XML;

        $this->connector->getDomainById('some-zimbra-domain-id');

        $this->client->shouldHaveReceived('post')->with(
            m::any(),
            m::on(
                function ($actual) use ($expected) {

                    return strstr($actual, $expected) !== false;
                }
            ),
            array("Content-type: application/xml"),
            m::any(),
            m::any()
        )->once();
    }

    /**
     * @test
     */
    public function acceptsAnyDomainId()
    {
        $expected = <<<XML
    <GetDomainRequest xmlns="urn:zimbraAdmin">
      <domain by="id">any-zimbra-domain-id</domain>
    </GetDomainRequest>
XML;

        $this->connector->getDomainById('any-zimbra-domain-id');

        $this->client->shouldHaveReceived('post')->with(
            m::any(),
            m::on(
                function ($actual) use ($expected) {

                    return strstr($actual, $expected) !== false;
                }
            ),
            array("Content-type: application/xml"),
            m::any(),
            m::any()
        )->once();
    }

    /**
     * @test
     */
    public function returnsNodesFromTheResponseUsingTheirNameAttributeAsTheKey()
    {
        $response = $this->connector->getDomainById('any-zimbra-domain-id');
        $this->assertEquals('externalLdapAutoComplete', $response['zimbraGalAutoCompleteLdapFilter']);
    }

    protected function setUp()
    {
        parent::setUp();

        $getDomainResponseXml =
            <<<'XML'
<GetDomainResponse xmlns="urn:zimbraAdmin">
  <domain name="hbdc.co.za" id="96a66235-0c22-4d4d-8ec7-7ccc2f346e31">
    <a n="zimbraGalAutoCompleteLdapFilter">externalLdapAutoComplete</a>
    <a n="zimbraAdminConsoleDNSCheckEnabled">FALSE</a>
    <a n="zimbraReverseProxyClientCertMode">off</a>
    <a n="zimbraGalSyncTimestampFormat">yyyyMMddHHmmss'Z'</a>
    <a n="zimbraMailDomainQuota">0</a>
    <a n="zimbraDomainName">hbdc.co.za</a>
    <a n="zimbraFreebusyExchangeCachedInterval">60d</a>
    <a n="zimbraGalSyncLdapPageSize">1000</a>
    <a n="zimbraMailStatus">enabled</a>
    <a n="zimbraFreebusyExchangeCachedIntervalStart">7d</a>
    <a n="zimbraDomainAggregateQuotaPolicy">ALLOWSENDRECEIVE</a>
    <a n="zimbraAdminConsoleSkinEnabled">FALSE</a>
    <a n="zimbraDomainAggregateQuotaWarnPercent">80</a>
    <a n="zimbraDomainStatus">active</a>
    <a n="zimbraBasicAuthRealm">Zimbra</a>
    <a n="zimbraGalLdapPageSize">1000</a>
    <a n="zimbraGalAlwaysIncludeLocalCalendarResources">FALSE</a>
    <a n="zimbraAdminConsoleLDAPAuthEnabled">FALSE</a>
    <a n="zimbraWebClientMaxInputBufferLength">1024</a>
    <a n="zimbraAutoProvNotificationBody">Your account has been auto provisioned.  Your email address is ${ACCOUNT_ADDRESS}.</a>
    <a n="objectClass">dcObject</a>
    <a n="objectClass">organization</a>
    <a n="objectClass">zimbraDomain</a>
    <a n="objectClass">amavisAccount</a>
    <a n="zimbraZimletDataSensitiveInMixedModeDisabled">TRUE</a>
    <a n="zimbraFreebusyExchangeServerType">webdav</a>
    <a n="zimbraPublicServiceHostname">cloudmail.synaq.com</a>
    <a n="zimbraDomainAggregateQuota">0</a>
    <a n="zimbraDomainDefaultCOSId">b82ed78c-893e-434a-8be1-2af5df979848</a>
    <a n="zimbraGalGroupIndicatorEnabled">TRUE</a>
    <a n="zimbraGalMaxResults">100</a>
    <a n="zimbraGalSyncMaxConcurrentClients">2</a>
    <a n="zimbraReverseProxyExternalRouteIncludeOriginalAuthusername">FALSE</a>
    <a n="zimbraGalLdapAttrMap">(binary) userSMIMECertificate=userSMIMECertificate</a>
    <a n="zimbraGalLdapAttrMap">(certificate) userCertificate=userCertificate</a>
    <a n="zimbraGalLdapAttrMap">co=workCountry</a>
    <a n="zimbraGalLdapAttrMap">company=company</a>
    <a n="zimbraGalLdapAttrMap">description=notes</a>
    <a n="zimbraGalLdapAttrMap">displayName,cn=fullName,fullName2,fullName3,fullName4,fullName5,fullName6,fullName7,fullName8,fullName9,fullName10</a>
    <a n="zimbraGalLdapAttrMap">facsimileTelephoneNumber,fax=workFax</a>
    <a n="zimbraGalLdapAttrMap">givenName,gn=firstName</a>
    <a n="zimbraGalLdapAttrMap">homeTelephoneNumber,homePhone=homePhone</a>
    <a n="zimbraGalLdapAttrMap">initials=initials</a>
    <a n="zimbraGalLdapAttrMap">l=workCity</a>
    <a n="zimbraGalLdapAttrMap">mobileTelephoneNumber,mobile=mobilePhone</a>
    <a n="zimbraGalLdapAttrMap">msExchResourceSearchProperties=zimbraAccountCalendarUserType</a>
    <a n="zimbraGalLdapAttrMap">objectClass=objectClass</a>
    <a n="zimbraGalLdapAttrMap">ou=department</a>
    <a n="zimbraGalLdapAttrMap">pagerTelephoneNumber,pager=pager</a>
    <a n="zimbraGalLdapAttrMap">physicalDeliveryOfficeName=office</a>
    <a n="zimbraGalLdapAttrMap">postalCode=workPostalCode</a>
    <a n="zimbraGalLdapAttrMap">sn=lastName</a>
    <a n="zimbraGalLdapAttrMap">st=workState</a>
    <a n="zimbraGalLdapAttrMap">street,streetAddress=workStreet</a>
    <a n="zimbraGalLdapAttrMap">telephoneNumber=workPhone</a>
    <a n="zimbraGalLdapAttrMap">title=jobTitle</a>
    <a n="zimbraGalLdapAttrMap">whenChanged,modifyTimeStamp=modifyTimeStamp</a>
    <a n="zimbraGalLdapAttrMap">whenCreated,createTimeStamp=createTimeStamp</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResBuilding=zimbraCalResBuilding</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResCapacity,msExchResourceCapacity=zimbraCalResCapacity</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResContactEmail=zimbraCalResContactEmail</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResFloor=zimbraCalResFloor</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResLocationDisplayName=zimbraCalResLocationDisplayName</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResSite=zimbraCalResSite</a>
    <a n="zimbraGalLdapAttrMap">zimbraCalResType,msExchResourceSearchProperties=zimbraCalResType</a>
    <a n="zimbraGalLdapAttrMap">zimbraDistributionListSubscriptionPolicy=zimbraDistributionListSubscriptionPolicy</a>
    <a n="zimbraGalLdapAttrMap">zimbraDistributionListUnsubscriptionPolicy=zimbraDistributionListUnsubscriptionPolicy</a>
    <a n="zimbraGalLdapAttrMap">zimbraId=zimbraId</a>
    <a n="zimbraGalLdapAttrMap">zimbraMailDeliveryAddress,zimbraMailAlias,mail=email,email2,email3,email4,email5,email6,email7,email8,email9,email10,email11,email12,email13,email14,email15,email16</a>
    <a n="zimbraGalLdapAttrMap">zimbraMailForwardingAddress=member</a>
    <a n="zimbraGalLdapAttrMap">zimbraPhoneticCompany,ms-DS-Phonetic-Company-Name=phoneticCompany</a>
    <a n="zimbraGalLdapAttrMap">zimbraPhoneticFirstName,ms-DS-Phonetic-First-Name=phoneticFirstName</a>
    <a n="zimbraGalLdapAttrMap">zimbraPhoneticLastName,ms-DS-Phonetic-Last-Name=phoneticLastName</a>
    <a n="zimbraDomainType">local</a>
    <a n="zimbraMailSSLClientCertPrincipalMap">SUBJECT_EMAILADDRESS=name</a>
    <a n="zimbraAutoProvNotificationSubject">New account auto provisioned</a>
    <a n="zimbraLdapGalSyncDisabled">FALSE</a>
    <a n="zimbraPreAuthKey">56e0d40171f771ff8d7023971886c473ebb9c95a2cec39e19ef36ef65369f88b8</a>
    <a n="description">Permanent UAT Reseller | Honey Badger Don't Care</a>
    <a n="zimbraAdminConsoleCatchAllAddressEnabled">FALSE</a>
    <a n="zimbraGalTokenizeAutoCompleteKey">and</a>
    <a n="zimbraCommunityUsernameMapping">uid</a>
    <a n="zimbraExternalShareInvitationUrlExpiration">0</a>
    <a n="zimbraGalLdapValueMap">zimbraAccountCalendarUserType: Room|Equipment RESOURCE</a>
    <a n="zimbraGalLdapValueMap">zimbraCalResType: Room Location</a>
    <a n="zimbraDomainMandatoryMailSignatureEnabled">FALSE</a>
    <a n="zimbraAutoProvBatchSize">20</a>
    <a n="zimbraCreateTimestamp">20170905122604Z</a>
    <a n="zimbraInternalSharingCrossDomainEnabled">TRUE</a>
    <a n="zimbraGalAccountId">c092a70e-08aa-4805-b94b-685051eb4211</a>
    <a n="zimbraVirtualHostname">cloudmail.synaq.com</a>
    <a n="zimbraGalInternalSearchBase">DOMAIN</a>
    <a n="zimbraCommunityHomeURL">/integration/zimbracollaboration</a>
    <a n="o">hbdc.co.za domain</a>
    <a n="zimbraFileUploadMaxSizePerFile">2147483648</a>
    <a n="zimbraGalTokenizeSearchKey">and</a>
    <a n="zimbraPrefTimeZoneId">Africa/Harare</a>
    <a n="zimbraId">96a66235-0c22-4d4d-8ec7-7ccc2f346e31</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -adminLoginAs</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -set.account.zimbraDomainAdminMaxMailQuota</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -get.account.zimbraDomainAdminMaxMailQuota</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -get.account.zimbraIsDomainAdminAccount</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -set.account.zimbraIsDomainAdminAccount</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp -set.account.zimbraIsDelegatedAdminAccount</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp getAccount</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp getAccountInfo</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp getMailboxInfo</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp getDomainQuotaUsage</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp adminConsoleAliasRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp adminConsoleResourceRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp adminConsoleDLRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp adminConsoleAccountRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp adminConsoleDomainRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp domainAdminConsoleRights</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp set.account.zimbraCOSId</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp get.account.zimbraCOSId</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp changeAccountPassword</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp modifyDomain</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp modifyAccount</a>
    <a n="zimbraACE">799d21c5-47f2-4cb6-b548-3f7867a02065 grp domainAdminAccountRights</a>
    <a n="zimbraAggregateQuotaLastUsage">0</a>
    <a n="zimbraMobileMetadataMaxSizeEnabled">FALSE</a>
    <a n="dc">hbdc</a>
  </domain>
</GetDomainResponse>
XML;

        $getDomainResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$getDomainResponseXml.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($getDomainResponse)->byDefault();

        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__.'/Fixtures/token');
    }
}
