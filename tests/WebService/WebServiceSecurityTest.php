<?php

require_once(ROOT_DIR . 'lib/WebService/namespace.php');

class WebServiceSecurityTests extends TestBase
{
    private $sessionToken = 'sessionToken';
    private $userId = 'userId';

    /**
     * @var FakeWebServiceUserSession
     */
    private $session;

    /**
     * @var WebServiceSecurity
     */
    private $security;
    /**
     * @var IRestServer|PHPUnit_Framework_MockObject_MockObject
     */
    private $server;
    /**
     * @var IUserSessionRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $userSessionRepository;

    public function setUp(): void
    {
        parent::setup();

        $this->userSessionRepository = $this->createMock('IUserSessionRepository');
        $this->server = $this->createMock('IRestServer');

        $this->security = new WebServiceSecurity($this->userSessionRepository);

        $this->session = new FakeWebServiceUserSession($this->userId);
        $this->session->SessionToken = $this->sessionToken;
        $this->session->SessionExpiration = WebServiceExpiration::Create();
    }

    public function testSetsUserSessionIfValidAndNotExpired()
    {
        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue($this->sessionToken));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue($this->userId));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo($this->sessionToken))
                ->will($this->returnValue($this->session));

        $this->userSessionRepository->expects($this->once())
                ->method('Update')
                ->with($this->equalTo($this->session));

        $this->server->expects($this->once())
                ->method('SetSession')
                ->with($this->equalTo($this->session));

        $wasHandled = $this->security->HandleSecureRequest($this->server);

        $this->assertTrue($wasHandled);
        $this->assertTrue($this->session->_SessionExtended);
    }

    public function testDeletesExpiredSessions()
    {
        $this->session->_IsExpired = true;

        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue($this->sessionToken));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue($this->userId));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo($this->sessionToken))
                ->will($this->returnValue($this->session));

        $this->userSessionRepository->expects($this->once())
                ->method('Delete')
                ->with($this->equalTo($this->session));

        $wasHandled = $this->security->HandleSecureRequest($this->server);

        $this->assertFalse($wasHandled);
        $this->assertFalse($this->session->_SessionExtended);
    }

    public function testHandlesSessionNotFound()
    {
        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue($this->sessionToken));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue($this->userId));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo($this->sessionToken))
                ->will($this->returnValue(null));

        $wasHandled = $this->security->HandleSecureRequest($this->server);

        $this->assertFalse($wasHandled);
    }

    public function testHandlesSessionMisMatch()
    {
        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue('not the right token'));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue('not the right id'));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo('not the right token'))
                ->will($this->returnValue($this->session));

        $wasHandled = $this->security->HandleSecureRequest($this->server);

        $this->assertFalse($wasHandled);
    }

    public function testHandlesAdminRequest()
    {
        $this->session->IsAdmin = true;
        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue($this->sessionToken));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue($this->userId));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo($this->sessionToken))
                ->will($this->returnValue($this->session));

        $this->userSessionRepository->expects($this->once())
                ->method('Update')
                ->with($this->equalTo($this->session));

        $this->server->expects($this->once())
                ->method('SetSession')
                ->with($this->equalTo($this->session));

        $wasHandled = $this->security->HandleSecureRequest($this->server, true);

        $this->assertTrue($wasHandled);
        $this->assertTrue($this->session->_SessionExtended);
    }

    public function testHandlesWhenUserIsNotAdmin()
    {
        $this->session->IsAdmin = false;
        $this->server->expects($this->at(0))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::SESSION_TOKEN))
                ->will($this->returnValue($this->sessionToken));

        $this->server->expects($this->at(1))
                ->method('GetHeader')
                ->with($this->equalTo(WebServiceHeaders::USER_ID))
                ->will($this->returnValue($this->userId));

        $this->userSessionRepository->expects($this->once())
                ->method('LoadBySessionToken')
                ->with($this->equalTo($this->sessionToken))
                ->will($this->returnValue($this->session));

        $wasHandled = $this->security->HandleSecureRequest($this->server, true);

        $this->assertFalse($wasHandled);
        $this->assertFalse($this->session->_SessionExtended);
    }
}
