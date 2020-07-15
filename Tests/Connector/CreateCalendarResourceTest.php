<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class CreateCalendarResourceTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsOnePostRequestToZimbra()
    {
        $this->connector->createCalendarResource(null, null, null);
        $this->client->shouldHaveReceived('post')->once();
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsTheGivenNewNameAsAnAttribute()
    {
        $this->connector->createCalendarResource('foo@bar.com', null, null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/CreateCalendarResourceRequest.*name="foo@bar\.com"/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyGivenName()
    {
        $this->connector->createCalendarResource('bar@baz.com', null, null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/CreateCalendarResourceRequest.*name="bar@baz\.com"/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     * @noinspection SpellCheckingInspection
     */
    public function sendsTheGivenPasswordAsAnAttribute()
    {
        $this->connector->createCalendarResource(null, 'Passw0rd!', null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/CreateCalendarResourceRequest.*password="Passw0rd!"/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyGivenPassword()
    {
        $this->connector->createCalendarResource(null, 'what.Ever1@', null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/CreateCalendarResourceRequest.*password="what\.Ever1@"/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsTheGivenDisplayNameAsAnEmbeddedAttribute()
    {
        $this->connector->createCalendarResource(null, null, 'Some Resource');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<a n="displayName">Some Resource<\/a>/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyGivenDisplayName()
    {
        $this->connector->createCalendarResource(null, null, 'Any Old Resource');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<a n="displayName">Any Old Resource<\/a>/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsTheGivenCalendarResourceTypeAsAnEmbeddedAttribute()
    {
        $this->connector->createCalendarResource(null, null, null, 'Location');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<a n="zimbraCalResType">Location<\/a>/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyCalendarResourceType()
    {
        $this->connector->createCalendarResource(null, null, null, 'Equipment');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<a n="zimbraCalResType">Equipment<\/a>/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function 'sendsAnyOtherSuppliedAttributesAsEmbeddedAttributes'()
    {
        $this->connector->createCalendarResource(null, null, null, 'Location', ['zimbraCalResAutoAcceptDecline' => true]);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<a n="zimbraCalResAutoAcceptDecline">TRUE<\/a>/', $body) === 1;
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
        $message = '<CreateCalendarResourceResponse xmlns="urn:zimbraAdmin">
  <calresource name="some.resource@some.domain.com" id="some-resource-id">
    <a n="zimbraPrefIMLogChats">TRUE</a>
    <a n="zimbraDeviceLockWhenInactive">FALSE</a>
    <a n="zimbraPrefFileSharingApplication">briefcase</a>
    <a n="zimbraDataSourceTotalQuota">0</a>
    <a n="zimbraPrefCalendarWorkingHours">1:N:0800:1700,2:Y:0800:1700,3:Y:0800:1700,4:Y:0800:1700,5:Y:0800:1700,6:Y:0800:1700,7:N:0800:1700</a>
    <a n="zimbraFeatureOutOfOfficeReplyEnabled">TRUE</a>
    <a n="zimbraPrefCalendarViewTimeInterval">1h</a>
    <a n="zimbraPrefDefaultCalendarId">10</a>
    <a n="zimbraPrefComposeFormat">html</a>
    <a n="zimbraPrefZmgPushNotificationEnabled">FALSE</a>
    <a n="zimbraPrefDisplayTimeInMailList">FALSE</a>
    <a n="zimbraPrefIMNotifyStatus">TRUE</a>
    <a n="zimbraQuotaWarnPercent">90</a>
    <a n="zimbraPrefIMReportIdle">TRUE</a>
    <a n="zimbraMailHost">some.host.com</a>
    <a n="zimbraFeatureMailForwardingEnabled">FALSE</a>
    <a n="zimbraPrefSaveToSent">TRUE</a>
    <a n="zimbraPrefDisplayExternalImages">FALSE</a>
    <a n="zimbraPrefOutOfOfficeCacheDuration">7d</a>
    <a n="zimbraPrefConvReadingPaneLocation">bottom</a>
    <a n="zimbraPrefShowSearchString">FALSE</a>
    <a n="zimbraInterceptSubject">Intercepted message for ${ACCOUNT_ADDRESS}: ${MESSAGE_SUBJECT}</a>
    <a n="zimbraMailTrustedSenderListMaxNumEntries">500</a>
    <a n="zimbraPrefMailSelectAfterDelete">next</a>
    <a n="displayName">Some Resource</a>
    <a n="zimbraPrefAppleIcalDelegationEnabled">FALSE</a>
    <a n="zimbraDataSourceQuota">0</a>
    <a n="uid">rd.test.resource.1</a>
    <a n="zimbraPrefHtmlEditorDefaultFontFamily">arial, helvetica, sans-serif</a>
    <a n="zimbraMobilePolicyMinDevicePasswordComplexCharacters">0</a>
    <a n="zimbraPrefConvShowCalendar">FALSE</a>
    <a n="zimbraRevokeAppSpecificPasswordsOnPasswordChange">TRUE</a>
    <a n="zimbraZimletUserPropertiesMaxNumEntries">150</a>
    <a n="zimbraFeatureSMIMEEnabled">FALSE</a>
    <a n="zimbraPrefCalendarShowPastDueReminders">TRUE</a>
    <a n="zimbraMobilePolicyAllowPOPIMAPEmail">1</a>
    <a n="zimbraDataSourceMinPollingInterval">1m</a>
    <a n="zimbraMobilePolicyRequireSignedSMIMEMessages">0</a>
    <a n="zimbraPrefWarnOnExit">TRUE</a>
    <a n="zimbraFeatureMobileGatewayEnabled">FALSE</a>
    <a n="cn">Some Resource</a>
    <a n="zimbraFeaturePriorityInboxEnabled">TRUE</a>
    <a n="zimbraSpamApplyUserFilters">FALSE</a>
    <a n="zimbraFeatureBriefcaseSpreadsheetEnabled">FALSE</a>
    <a n="zimbraFeatureAddressVerificationExpiry">1d</a>
    <a n="zimbraQuotaWarnInterval">1d</a>
    <a n="zimbraMobilePolicyMaxEmailBodyTruncationSize">-1</a>
    <a n="zimbraMobileMetadataMaxSizeEnabled">FALSE</a>
    <a n="zimbraPrefIMToasterEnabled">FALSE</a>
    <a n="zimbraPrefOutOfOfficeStatusAlertOnLogin">TRUE</a>
    <a n="zimbraFeatureMailPriorityEnabled">TRUE</a>
    <a n="zimbraFreebusyExchangeCachedInterval">60d</a>
    <a n="zimbraFeatureManageZimlets">TRUE</a>
    <a n="zimbraPasswordMinNumericChars">0</a>
    <a n="zimbraWebClientShowOfflineLink">TRUE</a>
    <a n="zimbraMobileSmartForwardRFC822Enabled">FALSE</a>
    <a n="zimbraFeatureCalendarEnabled">TRUE</a>
    <a n="zimbraMailBlacklistMaxNumEntries">100</a>
    <a n="zimbraPrefVoiceItemsPerPage">25</a>
    <a n="zimbraMobilePolicyAllowRemoteDesktop">1</a>
    <a n="zimbraFeatureDiscardInFiltersEnabled">TRUE</a>
    <a n="zimbraFeatureTrustedDevicesEnabled">TRUE</a>
    <a n="zimbraMobilePolicyPasswordRecoveryEnabled">TRUE</a>
    <a n="zimbraMailHighlightObjectsMaxSize">70</a>
    <a n="zimbraPrefMailToasterEnabled">FALSE</a>
    <a n="zimbraFileAndroidCrashReportingEnabled">TRUE</a>
    <a n="zimbraPrefForwardReplyInOriginalFormat">TRUE</a>
    <a n="zimbraPrefBriefcaseReadingPaneLocation">right</a>
    <a n="zimbraPrefContactsPerPage">25</a>
    <a n="zimbraPrefMarkMsgRead">0</a>
    <a n="zimbraPrefMessageIdDedupingEnabled">TRUE</a>
    <a n="zimbraAccountStatus">active</a>
    <a n="zimbraPrefCalendarApptReminderWarningTime">5</a>
    <a n="zimbraSieveRejectMailEnabled">TRUE</a>
    <a n="zimbraPrefDeleteInviteOnReply">TRUE</a>
    <a n="zimbraPrefCalendarDefaultApptDuration">60m</a>
    <a n="zimbraPrefCalendarDayHourStart">8</a>
    <a n="zimbraMaxVoiceItemsPerPage">100</a>
    <a n="zimbraPrefPop3DeleteOption">delete</a>
    <a n="zimbraDataSourceMaxNumEntries">20</a>
    <a n="zimbraImapEnabled">TRUE</a>
    <a n="zimbraFeatureViewInHtmlEnabled">FALSE</a>
    <a n="zimbraCalendarMaxRevisions">1</a>
    <a n="zimbraPrefTabInEditorEnabled">FALSE</a>
    <a n="zimbraMailSignatureMaxLength">10240</a>
    <a n="zimbraPrefCalendarAutoAddInvites">TRUE</a>
    <a n="zimbraPasswordLockoutSuppressionCacheSize">1</a>
    <a n="zimbraCommunityUsernameMapping">uid</a>
    <a n="zimbraFeatureSignaturesEnabled">TRUE</a>
    <a n="zimbraPasswordRecoveryMaxAttempts">10</a>
    <a n="zimbraPrefExternalSendersType">ALL</a>
    <a n="zimbraPrefIMFlashTitle">TRUE</a>
    <a n="zimbraLogOutFromAllServers">FALSE</a>
    <a n="zimbraPasswordLockoutMaxFailures">10</a>
    <a n="zimbraCreateTimestamp">20200715063550Z</a>
    <a n="zimbraMobilePolicyDevicePasswordHistory">8</a>
    <a n="zimbraMobilePolicyAllowDesktopSync">1</a>
    <a n="zimbraCommunityHomeURL">/integration/zimbracollaboration</a>
    <a n="zimbraMobileTombstoneEnabled">TRUE</a>
    <a n="zimbraFeatureImapDataSourceEnabled">TRUE</a>
    <a n="zimbraPrefSentLifetime">0</a>
    <a n="zimbraFeatureSocialEnabled">FALSE</a>
    <a n="zimbraMobilePolicyAllowUnsignedInstallationPackages">1</a>
    <a n="zimbraContactRankingTableSize">200</a>
    <a n="zimbraPrefAutoCompleteQuickCompletionOnComma">TRUE</a>
    <a n="zimbraPrefMailFlashIcon">FALSE</a>
    <a n="zimbraPrefMailSoundsEnabled">FALSE</a>
    <a n="zimbraAuthTokenLifetime">2d</a>
    <a n="zimbraNewMailNotificationFrom">Postmaster &lt;postmaster@${RECIPIENT_DOMAIN}></a>
    <a n="zimbraPrefFolderColorEnabled">TRUE</a>
    <a n="zimbraFeatureMailSendLaterEnabled">TRUE</a>
    <a n="zimbraPortalName">example</a>
    <a n="zimbraSieveRequireControlEnabled">TRUE</a>
    <a n="zimbraDataSourceCaldavPollingInterval">12h</a>
    <a n="zimbraTwoFactorAuthEnablementTokenLifetime">1h</a>
    <a n="zimbraMobileSearchMimeSupportEnabled">FALSE</a>
    <a n="zimbraMobilePolicyAllowNonProvisionableDevices">TRUE</a>
    <a n="zimbraMailThreadingAlgorithm">references</a>
    <a n="zimbraFeatureContactBackupEnabled">FALSE</a>
    <a n="zimbraPrefIMSoundsEnabled">TRUE</a>
    <a n="zimbraPrefGalAutoCompleteEnabled">FALSE</a>
    <a n="zimbraPrefIMHideBlockedBuddies">FALSE</a>
    <a n="zimbraPrefUseSendMsgShortcut">TRUE</a>
    <a n="zimbraMobileOutlookSyncEnabled">TRUE</a>
    <a n="zimbraPrefCalendarReminderSoundsEnabled">TRUE</a>
    <a n="zimbraPrefCalendarShowDeclinedMeetings">TRUE</a>
    <a n="zimbraDeviceAllowedPasscodeLockoutDuration">10m</a>
    <a n="zimbraDeviceAllowedPasscodeLockoutDuration">1m</a>
    <a n="zimbraDeviceAllowedPasscodeLockoutDuration">2m</a>
    <a n="zimbraDeviceAllowedPasscodeLockoutDuration">30m</a>
    <a n="zimbraDeviceAllowedPasscodeLockoutDuration">5m</a>
    <a n="zimbraFeatureEwsEnabled">TRUE</a>
    <a n="zimbraFeatureContactsEnabled">TRUE</a>
    <a n="zimbraPrefIMInstantNotify">TRUE</a>
    <a n="zimbraFeatureComposeInNewWindowEnabled">TRUE</a>
    <a n="zimbraPasswordMaxAge">0</a>
    <a n="zimbraFeatureFlaggingEnabled">TRUE</a>
    <a n="zimbraFeatureContactsDetailedSearchEnabled">FALSE</a>
    <a n="zimbraPrefMailInitialSearch">in:inbox</a>
    <a n="userPassword">VALUE-BLOCKED</a>
    <a n="zimbraPrefIMNotifyPresence">TRUE</a>
    <a n="zimbraPrefMandatorySpellCheckEnabled">FALSE</a>
    <a n="zimbraAvailableSkin">mweb</a>
    <a n="zimbraFeatureSocialFiltersEnabled">Facebook</a>
    <a n="zimbraFeatureSocialFiltersEnabled">LinkedIn</a>
    <a n="zimbraFeatureSocialFiltersEnabled">SocialCast</a>
    <a n="zimbraFeatureSocialFiltersEnabled">Twitter</a>
    <a n="zimbraPrefDedupeMessagesSentToSelf">dedupeNone</a>
    <a n="zimbraPrefHtmlEditorDefaultFontSize">12pt</a>
    <a n="zimbraExternalShareDomainWhitelistEnabled">FALSE</a>
    <a n="zimbraMobilePolicyAllowSMIMEEncryptionAlgorithmNegotiation">2</a>
    <a n="zimbraInterceptBody">Intercepted message for ${ACCOUNT_ADDRESS}.${NEWLINE}Operation=${OPERATION}, folder=${FOLDER_NAME}, folder ID=${FOLDER_ID}.</a>
    <a n="zimbraIdentityMaxNumEntries">20</a>
    <a n="zimbraFeatureAdminMailEnabled">TRUE</a>
    <a n="zimbraBatchedIndexingSize">20</a>
    <a n="zimbraDataSourceImportOnLogin">FALSE</a>
    <a n="zimbraFeatureMAPIConnectorEnabled">FALSE</a>
    <a n="zimbraFeatureTwoFactorAuthRequired">FALSE</a>
    <a n="zimbraMailDeliveryAddress">some.resource@some.domain.com</a>
    <a n="zimbraMobilePolicyRequireSignedSMIMEAlgorithm">0</a>
    <a n="zimbraCalendarResourceDoubleBookingAllowed">TRUE</a>
    <a n="zimbraPrefSentMailFolder">sent</a>
    <a n="zimbraPrefCalendarApptVisibility">public</a>
    <a n="zimbraFileIOSCrashReportingEnabled">TRUE</a>
    <a n="zimbraPrefCalendarDayHourEnd">18</a>
    <a n="zimbraFeatureConversationsEnabled">TRUE</a>
    <a n="zimbraPasswordLockoutFailureLifetime">1h</a>
    <a n="zimbraFeatureDistributionListExpandMembersEnabled">TRUE</a>
    <a n="zimbraPrefShowComposeDirection">FALSE</a>
    <a n="zimbraPrefShowCalendarWeek">FALSE</a>
    <a n="mail">some.resource@some.domain.com</a>
    <a n="zimbraMobilePolicyRequireDeviceEncryption">0</a>
    <a n="zimbraFreebusyExchangeCachedIntervalStart">7d</a>
    <a n="zimbraFileShareLifetime">0</a>
    <a n="zimbraMobileNotificationEnabled">FALSE</a>
    <a n="zimbraMobilePolicyMaxCalendarAgeFilter">5</a>
    <a n="zimbraPasswordMinLowerCaseChars">0</a>
    <a n="zimbraPrefClientType">advanced</a>
    <a n="zimbraPrefIMAutoLogin">FALSE</a>
    <a n="zimbraNotebookMaxRevisions">0</a>
    <a n="zimbraPrefCalendarAlwaysShowMiniCal">TRUE</a>
    <a n="zimbraPrefChatPlaySound">FALSE</a>
    <a n="zimbraFeatureExternalFeedbackEnabled">FALSE</a>
    <a n="zimbraPrefHtmlEditorDefaultFontColor">#000000</a>
    <a n="zimbraMaxAppSpecificPasswords">25</a>
    <a n="zimbraFeatureBriefcaseDocsEnabled">TRUE</a>
    <a n="zimbraFilterSleepInterval">1ms</a>
    <a n="zimbraFeatureReadReceiptsEnabled">TRUE</a>
    <a n="zimbraExternalSharingEnabled">TRUE</a>
    <a n="zimbraPasswordLockoutSuppressionEnabled">TRUE</a>
    <a n="zimbraPrefTasksReadingPaneLocation">right</a>
    <a n="zimbraPrefItemsPerVirtualPage">50</a>
    <a n="zimbraSyncWindowSize">0</a>
    <a n="zimbraChatHistoryEnabled">TRUE</a>
    <a n="zimbraPrefSearchTreeOpen">TRUE</a>
    <a n="zimbraPrefStandardClientAccessibilityMode">FALSE</a>
    <a n="zimbraFeatureAntispamEnabled">TRUE</a>
    <a n="zimbraPrefUseRfc2231">FALSE</a>
    <a n="zimbraPrefCalendarNotifyDelegatedChanges">FALSE</a>
    <a n="zimbraFeatureChangePasswordEnabled">FALSE</a>
    <a n="zimbraPrefShowChatsFolderInMail">FALSE</a>
    <a n="zimbraMobilePolicyMaxDevicePasswordFailedAttempts">4</a>
    <a n="zimbraPrefConversationOrder">dateDesc</a>
    <a n="zimbraDeviceFileOpenWithEnabled">TRUE</a>
    <a n="zimbraFeatureMarkMailForwardedAsRead">FALSE</a>
    <a n="zimbraMobilePolicyAllowSimpleDevicePassword">FALSE</a>
    <a n="zimbraDataSourceRssPollingInterval">12h</a>
    <a n="zimbraPrefIncludeSharedItemsInSearch">FALSE</a>
    <a n="zimbraAttachmentsIndexingEnabled">TRUE</a>
    <a n="zimbraDumpsterPurgeEnabled">TRUE</a>
    <a n="zimbraPasswordLockoutEnabled">FALSE</a>
    <a n="zimbraArchiveAccountNameTemplate">${USER}-${DATE}@${DOMAIN}.archive</a>
    <a n="zimbraStandardClientCustomPrefTabsEnabled">FALSE</a>
    <a n="zimbraTwoFactorAuthTrustedDeviceTokenLifetime">30d</a>
    <a n="zimbraFeatureVoiceEnabled">FALSE</a>
    <a n="zimbraPrefShowSelectionCheckbox">FALSE</a>
    <a n="zimbraPrefDelegatedSendSaveTarget">owner</a>
    <a n="zimbraPrefPop3IncludeSpam">FALSE</a>
    <a n="zimbraFeatureBriefcaseSlidesEnabled">FALSE</a>
    <a n="zimbraMobileAttachSkippedItemEnabled">FALSE</a>
    <a n="zimbraPrefCalendarReminderFlashTitle">TRUE</a>
    <a n="zimbraFeatureMailForwardingInFiltersEnabled">TRUE</a>
    <a n="zimbraPrefDefaultPrintFontSize">12pt</a>
    <a n="zimbraFeatureSocialcastEnabled">FALSE</a>
    <a n="zimbraPrefMessageViewHtmlPreferred">TRUE</a>
    <a n="zimbraPrefMailFlashTitle">FALSE</a>
    <a n="zimbraFeatureCalendarUpsellEnabled">FALSE</a>
    <a n="zimbraMobilePolicyAllowSMIMESoftCerts">1</a>
    <a n="zimbraMobilePolicyMaxEmailAgeFilter">5</a>
    <a n="zimbraInterceptSendHeadersOnly">FALSE</a>
    <a n="zimbraMobileForceSamsungProtocol25">FALSE</a>
    <a n="zimbraPrefMailPollingInterval">300s</a>
    <a n="zimbraPrefFontSize">normal</a>
    <a n="zimbraId">2e27c7a3-a622-4129-81cc-a017ab101dbc</a>
    <a n="zimbraPrefIMLogChatsEnabled">TRUE</a>
    <a n="zimbraPrefReplyIncludeOriginalText">includeBody</a>
    <a n="zimbraFeatureGalSyncEnabled">TRUE</a>
    <a n="zimbraFeatureIdentitiesEnabled">TRUE</a>
    <a n="zimbraPrefIncludeTrashInSearch">FALSE</a>
    <a n="zimbraPrefSharedAddrBookAutoCompleteEnabled">FALSE</a>
    <a n="zimbraFeatureImportFolderEnabled">TRUE</a>
    <a n="zimbraFeatureOptionsEnabled">TRUE</a>
    <a n="zimbraPrefCalendarAllowCancelEmailToSelf">FALSE</a>
    <a n="zimbraFeatureChatEnabled">FALSE</a>
    <a n="zimbraFeatureResetPasswordSuspensionTime">1d</a>
    <a n="zimbraFeatureTasksEnabled">TRUE</a>
    <a n="zimbraMailPurgeUseChangeDateForTrash">TRUE</a>
    <a n="zimbraExternalAccountLifetimeAfterDisabled">30d</a>
    <a n="zimbraDevicePasscodeEnabled">FALSE</a>
    <a n="zimbraPrefCalendarAllowPublishMethodInvite">FALSE</a>
    <a n="zimbraSieveImmutableHeaders">Received,DKIM-Signature,Authentication-Results,Received-SPF,Message-ID,Content-Type,Content-Disposition,Content-Transfer-Encoding,MIME-Version,Auto-Submitted</a>
    <a n="zimbraPasswordLocked">FALSE</a>
    <a n="zimbraFeatureNewAddrBookEnabled">TRUE</a>
    <a n="zimbraMobilePolicyRequireEncryptedSMIMEMessages">0</a>
    <a n="zimbraMobilePolicyRefreshInterval">1440</a>
    <a n="zimbraFeatureAddressVerificationEnabled">FALSE</a>
    <a n="zimbraFeatureVoiceChangePinEnabled">TRUE</a>
    <a n="zimbraCalendarCalDavSharedFolderCacheDuration">1m</a>
    <a n="zimbraPrefIMIdleStatus">away</a>
    <a n="zimbraPasswordMinAlphaChars">0</a>
    <a n="zimbraMailSpamLifetime">0</a>
    <a n="zimbraPrefGroupMailBy">conversation</a>
    <a n="zimbraMailForwardingAddressMaxNumAddrs">100</a>
    <a n="zimbraNewMailNotificationSubject">New message received at ${RECIPIENT_ADDRESS}</a>
    <a n="zimbraMailQuota">26843545600</a>
    <a n="zimbraQuotaWarnMessage">From: Postmaster &lt;postmaster@${RECIPIENT_DOMAIN}>${NEWLINE}To: ${RECIPIENT_NAME} &lt;${RECIPIENT_ADDRESS}>${NEWLINE}Subject: Quota warning${NEWLINE}Date: ${DATE}${NEWLINE}Content-Type: text/plain${NEWLINE}${NEWLINE}Your mailbox size has reached ${MBOX_SIZE_MB}MB, which is over ${WARN_PERCENT}% of your ${QUOTA_MB}MB quota.${NEWLINE}Please delete some messages to avoid exceeding your quota.${NEWLINE}</a>
    <a n="zimbraFeatureZimbraAssistantEnabled">TRUE</a>
    <a n="zimbraShowClientTOS">FALSE</a>
    <a n="zimbraPrefCalendarAllowForwardedInvite">TRUE</a>
    <a n="zimbraCalResType">Location</a>
    <a n="zimbraFeatureGroupCalendarEnabled">TRUE</a>
    <a n="zimbraPrefZimletTreeOpen">FALSE</a>
    <a n="zimbraFilterBatchSize">10000</a>
    <a n="zimbraArchiveAccountDateTemplate">yyyyMMdd</a>
    <a n="zimbraSignatureMaxNumEntries">20</a>
    <a n="zimbraPrefCalendarUseQuickAdd">TRUE</a>
    <a n="zimbraPrefComposeInNewWindow">FALSE</a>
    <a n="zimbraAttachmentsBlocked">FALSE</a>
    <a n="zimbraPrefGalSearchEnabled">TRUE</a>
    <a n="zimbraPrefJunkLifetime">0</a>
    <a n="zimbraPrefSpellIgnoreAllCaps">TRUE</a>
    <a n="sn">rd.test.resource.1</a>
    <a n="zimbraFeatureManageSMIMECertificateEnabled">FALSE</a>
    <a n="zimbraMailDumpsterLifetime">30d</a>
    <a n="zimbraAppSpecificPasswordDuration">0</a>
    <a n="zimbraExportMaxDays">0</a>
    <a n="zimbraPrefUseTimeZoneListInCalendar">FALSE</a>
    <a n="zimbraCalendarKeepExceptionsOnSeriesTimeChange">FALSE</a>
    <a n="zimbraPrefCalendarAllowedTargetsForInviteDeniedAutoReply">internal</a>
    <a n="zimbraPrefOpenMailInNewWindow">FALSE</a>
    <a n="zimbraMobilePolicyAlphanumericDevicePasswordRequired">FALSE</a>
    <a n="zimbraAdminAuthTokenLifetime">12h</a>
    <a n="zimbraFileExternalShareLifetime">90d</a>
    <a n="zimbraFeatureTaggingEnabled">TRUE</a>
    <a n="zimbraCalendarShowResourceTabs">TRUE</a>
    <a n="zimbraMobilePolicyRequireStorageCardEncryption">FALSE</a>
    <a n="zimbraPrefMailSignatureStyle">outlook</a>
    <a n="zimbraTwoFactorAuthLockoutMaxFailures">10</a>
    <a n="zimbraMailIdleSessionTimeout">0</a>
    <a n="zimbraArchiveEnabled">FALSE</a>
    <a n="zimbraDeviceOfflineCacheEnabled">FALSE</a>
    <a n="zimbraPop3Enabled">TRUE</a>
    <a n="zimbraMailAllowReceiveButNotSendWhenOverQuota">FALSE</a>
    <a n="zimbraDataSourceCalendarPollingInterval">12h</a>
    <a n="zimbraPrefAdminConsoleWarnOnExit">TRUE</a>
    <a n="zimbraPrefTrashLifetime">0</a>
    <a n="zimbraMailMinPollingInterval">2m</a>
    <a n="zimbraPrefShowFragments">TRUE</a>
    <a n="zimbraResetPasswordRecoveryCodeExpiry">10m</a>
    <a n="zimbraMobilePolicyDevicePasswordExpiration">0</a>
    <a n="zimbraFeatureSocialExternalEnabled">FALSE</a>
    <a n="zimbraFeatureAdminPreferencesEnabled">FALSE</a>
    <a n="zimbraFeaturePop3DataSourceEnabled">TRUE</a>
    <a n="zimbraGalSyncAccountBasedAutoCompleteEnabled">TRUE</a>
    <a n="zimbraMobilePolicyAllowBrowser">1</a>
    <a n="zimbraJunkMessagesIndexingEnabled">TRUE</a>
    <a n="zimbraPasswordMinUpperCaseChars">0</a>
    <a n="zimbraPrefIMFlashIcon">TRUE</a>
    <a n="zimbraMobileForceProtocol25">FALSE</a>
    <a n="zimbraPrefMailRequestReadReceipts">FALSE</a>
    <a n="zimbraPrefAdvancedClientEnforceMinDisplay">TRUE</a>
    <a n="zimbraPublicSharingEnabled">TRUE</a>
    <a n="zimbraMobilePolicyAllowStorageCard">1</a>
    <a n="zimbraZimletLoadSynchronously">FALSE</a>
    <a n="zimbraPrefCalendarFirstDayOfWeek">1</a>
    <a n="zimbraFeatureIMEnabled">FALSE</a>
    <a n="zimbraContactAutoCompleteMaxResults">20</a>
    <a n="zimbraPasswordMinDigitsOrPuncs">0</a>
    <a n="zimbraMobilePolicyAllowCamera">1</a>
    <a n="zimbraPasswordMinPunctuationChars">0</a>
    <a n="zimbraFilePublicShareLifetime">0</a>
    <a n="zimbraPrefSkin">mweb</a>
    <a n="zimbraPrefForwardReplyPrefixChar">></a>
    <a n="zimbraExternalShareLifetime">0</a>
    <a n="zimbraRecoveryAccountCodeValidity">1d</a>
    <a n="zimbraMobilePolicyRequireEncryptionSMIMEAlgorithm">0</a>
    <a n="zimbraFeatureWebClientOfflineAccessEnabled">TRUE</a>
    <a n="zimbraPrefShowAllNewMailNotifications">FALSE</a>
    <a n="zimbraPasswordMinAge">0</a>
    <a n="zimbraNotebookSanitizeHtml">TRUE</a>
    <a n="zimbraSignatureMinNumEntries">1</a>
    <a n="zimbraMaxMailItemsPerPage">100</a>
    <a n="zimbraMobilePolicyAllowInternetSharing">1</a>
    <a n="zimbraPrefAccountTreeOpen">TRUE</a>
    <a n="zimbraFeatureSharingEnabled">FALSE</a>
    <a n="zimbraPrefAutoSaveDraftInterval">30s</a>
    <a n="zimbraNewMailNotificationBody">New message received at ${RECIPIENT_ADDRESS}.${NEWLINE}Sender: ${SENDER_ADDRESS}${NEWLINE}Subject: ${SUBJECT}</a>
    <a n="zimbraMobilePolicyAllowIrDA">1</a>
    <a n="zimbraMobilePolicyRequireManualSyncWhenRoaming">0</a>
    <a n="zimbraFeatureMailUpsellEnabled">FALSE</a>
    <a n="zimbraFeatureSavedSearchesEnabled">TRUE</a>
    <a n="zimbraMailTransport">lmtp:some-mta.some.domain.com:7025</a>
    <a n="zimbraPrefCalendarToasterEnabled">FALSE</a>
    <a n="zimbraMobilePolicyAllowConsumerEmail">1</a>
    <a n="zimbraPasswordMaxLength">64</a>
    <a n="zimbraFeatureFreeBusyViewEnabled">FALSE</a>
    <a n="zimbraZimletAvailableZimlets">!com_zimbra_attachmail</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_phone</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_srchhighlighter</a>
    <a n="zimbraZimletAvailableZimlets">!com_zimbra_url</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_mailarchive</a>
    <a n="zimbraZimletAvailableZimlets">!com_zimbra_email</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_smime</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_webex</a>
    <a n="zimbraZimletAvailableZimlets">+com_zimbra_ymemoticons</a>
    <a n="zimbraZimletAvailableZimlets">!com_zimbra_date</a>
    <a n="zimbraPasswordEnforceHistory">0</a>
    <a n="zimbraFeatureTouchClientEnabled">TRUE</a>
    <a n="zimbraDumpsterEnabled">TRUE</a>
    <a n="zimbraAttachmentsViewInHtmlOnly">FALSE</a>
    <a n="zimbraSieveNotifyActionRFCCompliant">FALSE</a>
    <a n="objectClass">inetOrgPerson</a>
    <a n="objectClass">zimbraAccount</a>
    <a n="objectClass">zimbraCalendarResource</a>
    <a n="objectClass">amavisAccount</a>
    <a n="zimbraPrefColorMessagesEnabled">FALSE</a>
    <a n="zimbraPrefCalendarApptAllowAtendeeEdit">TRUE</a>
    <a n="zimbraMaxContactsPerPage">100</a>
    <a n="zimbraFeatureBriefcasesEnabled">TRUE</a>
    <a n="zimbraFeatureCrocodocEnabled">FALSE</a>
    <a n="zimbraPrefIncludeSpamInSearch">FALSE</a>
    <a n="zimbraFeatureContactsUpsellEnabled">FALSE</a>
    <a n="zimbraFeatureVoiceUpsellEnabled">FALSE</a>
    <a n="zimbraPrefCalendarInitialView">workWeek</a>
    <a n="zimbraPrefFolderTreeOpen">TRUE</a>
    <a n="zimbraPrefInboxUnreadLifetime">0</a>
    <a n="zimbraFeatureInstantNotify">TRUE</a>
    <a n="zimbraMobilePolicyAllowBluetooth">2</a>
    <a n="zimbraMobilePolicyDevicePasswordEnabled">TRUE</a>
    <a n="zimbraPrefImapSearchFoldersEnabled">TRUE</a>
    <a n="zimbraFeatureResetPasswordStatus">disabled</a>
    <a n="zimbraFeatureAppSpecificPasswordsEnabled">TRUE</a>
    <a n="zimbraFeatureDistributionListFolderEnabled">TRUE</a>
    <a n="zimbraPrefMailSendReadReceipts">prompt</a>
    <a n="zimbraShareLifetime">0</a>
    <a n="zimbraInterceptFrom">Postmaster &lt;postmaster@${ACCOUNT_DOMAIN}></a>
    <a n="zimbraMobilePolicyAllowWiFi">1</a>
    <a n="zimbraMailWhitelistMaxNumEntries">100</a>
    <a n="zimbraPrefForwardIncludeOriginalText">includeBody</a>
    <a n="zimbraMobilePolicyAllowTextMessaging">1</a>
    <a n="zimbraMobilePolicyAllowPartialProvisioning">TRUE</a>
    <a n="zimbraPrefMailItemsPerPage">25</a>
    <a n="zimbraPrefUseKeyboardShortcuts">TRUE</a>
    <a n="zimbraPublicShareLifetime">0</a>
    <a n="zimbraMobilePolicyMinDevicePasswordLength">4</a>
    <a n="zimbraTwoFactorAuthNumScratchCodes">10</a>
    <a n="zimbraDataSourcePop3PollingInterval">5m</a>
    <a n="zimbraFileUploadMaxSizePerFile">2147483648</a>
    <a n="zimbraFeatureConfirmationPageEnabled">FALSE</a>
    <a n="zimbraMobilePolicySuppressDeviceEncryption">FALSE</a>
    <a n="zimbraWebClientOfflineSyncMaxDays">30</a>
    <a n="zimbraPrefTimeZoneId">Africa/Harare</a>
    <a n="zimbraPasswordLockoutDuration">1h</a>
    <a n="zimbraSieveEditHeaderEnabled">FALSE</a>
    <a n="zimbraFeatureNewMailNotificationEnabled">TRUE</a>
    <a n="zimbraPasswordMinLength">0</a>
    <a n="zimbraPrefShortEmailAddress">TRUE</a>
    <a n="zimbraFeatureOpenMailInNewWindowEnabled">TRUE</a>
    <a n="zimbraPrefIMHideOfflineBuddies">FALSE</a>
    <a n="zimbraMailStatus">enabled</a>
    <a n="zimbraMobilePolicyAllowUnsignedApplications">1</a>
    <a n="zimbraMobilePolicyMaxInactivityTimeDeviceLock">15</a>
    <a n="zimbraFeatureGalEnabled">TRUE</a>
    <a n="zimbraFilePreviewMaxSize">20971520</a>
    <a n="zimbraMailPurgeUseChangeDateForSpam">TRUE</a>
    <a n="zimbraContactMaxNumEntries">10000</a>
    <a n="zimbraMailMessageLifetime">0</a>
    <a n="zimbraAllowAnyFromAddress">FALSE</a>
    <a n="zimbraFreebusyLocalMailboxNotActive">FALSE</a>
    <a n="zimbraPrefChatEnabled">TRUE</a>
    <a n="zimbraSmtpRestrictEnvelopeFrom">TRUE</a>
    <a n="zimbraPasswordLockoutSuppressionProtocols">zsync</a>
    <a n="zimbraPrefInboxReadLifetime">0</a>
    <a n="zimbraPrefTagTreeOpen">TRUE</a>
    <a n="zimbraPasswordModifiedTime">20200715063550Z</a>
    <a n="zimbraMobileShareContactEnabled">FALSE</a>
    <a n="zimbraFeatureGalAutoCompleteEnabled">FALSE</a>
    <a n="zimbraPrefGetMailAction">default</a>
    <a n="zimbraMobilePolicyAllowHTMLEmail">1</a>
    <a n="zimbraTouchJSErrorTrackingEnabled">FALSE</a>
    <a n="zimbraDomainAdminMaxMailQuota">26843545600</a>
    <a n="zimbraPrefAutoAddAddressEnabled">TRUE</a>
    <a n="zimbraFeatureTwoFactorAuthAvailable">FALSE</a>
    <a n="zimbraFeatureSkinChangeEnabled">FALSE</a>
    <a n="zimbraFeatureMobilePolicyEnabled">FALSE</a>
    <a n="zimbraMobilePolicyMaxEmailHTMLBodyTruncationSize">-1</a>
    <a n="zimbraPrefReadingPaneLocation">right</a>
    <a n="zimbraMailForwardingAddressMaxLength">4096</a>
    <a n="zimbraFeatureMailEnabled">TRUE</a>
    <a n="zimbraAccountCalendarUserType">RESOURCE</a>
    <a n="zimbraFeatureDataSourcePurgingEnabled">FALSE</a>
    <a n="zimbraFeaturePortalEnabled">FALSE</a>
    <a n="zimbraDataSourceImapPollingInterval">5m</a>
    <a n="zimbraContactEmailFields">email,email2,email3,email4,email5,email6,email7,email8,email9,email10,workEmail1,workEmail2,workEmail3</a>
    <a n="zimbraFeatureCalendarReminderDeviceEmailEnabled">FALSE</a>
    <a n="zimbraPrefCalendarSendInviteDeniedAutoReply">FALSE</a>
    <a n="zimbraDumpsterUserVisibleAge">30d</a>
    <a n="zimbraFeatureHtmlComposeEnabled">TRUE</a>
    <a n="zimbraFeatureFiltersEnabled">TRUE</a>
    <a n="zimbraFeatureFromDisplayEnabled">TRUE</a>
    <a n="zimbraPrefIMIdleTimeout">10</a>
    <a n="zimbraFeatureInitialSearchPreferenceEnabled">TRUE</a>
    <a n="zimbraFeatureMobileSyncEnabled">TRUE</a>
    <a n="zimbraTwoFactorAuthTokenLifetime">1h</a>
    <a n="zimbraFeatureExportFolderEnabled">TRUE</a>
    <a n="zimbraMailTrashLifetime">0</a>
    <a n="zimbraMobileSyncRedoMaxAttempts">default:1</a>
    <a n="zimbraMobileSyncRedoMaxAttempts">windows:2</a>
  </calresource>
</CreateCalendarResourceResponse>';

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
