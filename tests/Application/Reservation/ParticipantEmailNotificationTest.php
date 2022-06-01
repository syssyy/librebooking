<?php

require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/Notification/namespace.php');

class ParticipantEmailNotificationTests extends TestBase
{
    public function setUp(): void
    {
        parent::setup();
    }

    public function teardown(): void
    {
        parent::teardown();
    }

    public function testSendsReservationCreatedEmailIfThereAreNewParticipants()
    {
        $ownerId = 828;
        $owner = new User();
        $participantId1 = 50;
        $participant1 = new User();
        $participantId2 = 60;
        $participant2 = new User();

        $instance1 = new TestReservation();
        $instance1->WithAddedParticipants([$participantId1, $participantId2]);

        $series = new TestReservationSeries();
        $series->WithOwnerId($ownerId);
        $series->WithCurrentInstance($instance1);

        $userRepo = $this->createMock('IUserRepository');
        $attributeRepo = $this->createMock('IAttributeRepository');

        $userRepo->expects($this->at(0))
                 ->method('LoadById')
                 ->with($this->equalTo($ownerId))
                 ->will($this->returnValue($owner));

        $userRepo->expects($this->at(1))
                 ->method('LoadById')
                 ->with($this->equalTo($participantId1))
                 ->will($this->returnValue($participant1));

        $userRepo->expects($this->at(2))
                 ->method('LoadById')
                 ->with($this->equalTo($participantId2))
                 ->will($this->returnValue($participant2));

        $notification = new ParticipantAddedEmailNotification($userRepo, $attributeRepo);
        $notification->Notify($series);

        $this->assertEquals(2, count($this->fakeEmailService->_Messages));
        $lastExpectedMessage = new ParticipantAddedEmail($owner, $participant2, $series, $attributeRepo, $userRepo);
        $this->assertInstanceOf('ParticipantAddedEmail', $this->fakeEmailService->_LastMessage);
        //		$this->assertEquals($lastExpectedMessage, $this->fakeEmailService->_LastMessage);
    }

    public function testSendsReservationDeletedEmails()
    {
        $ownerId = 828;
        $owner = new User();
        $participantId1 = 50;
        $participant1 = new User();
        $participantId2 = 60;
        $participant2 = new User();

        $instance1 = new TestReservation();
        $instance1->WithAddedParticipants([1000, 2000]);
        $instance1->WithExistingParticipants([$participantId1, $participantId2]);

        $series = new TestReservationSeries();
        $series->WithOwnerId($ownerId);
        $series->WithCurrentInstance($instance1);

        $userRepo = $this->createMock('IUserRepository');
        $attributeRepo = $this->createMock('IAttributeRepository');

        $userRepo->expects($this->at(0))
                 ->method('LoadById')
                 ->with($this->equalTo($ownerId))
                 ->will($this->returnValue($owner));

        $userRepo->expects($this->at(1))
                 ->method('LoadById')
                 ->with($this->equalTo($participantId1))
                 ->will($this->returnValue($participant1));

        $userRepo->expects($this->at(2))
                 ->method('LoadById')
                 ->with($this->equalTo($participantId2))
                 ->will($this->returnValue($participant2));

        $notification = new ParticipantDeletedEmailNotification($userRepo, $attributeRepo);
        $notification->Notify($series);

        $this->assertEquals(2, count($this->fakeEmailService->_Messages));
        $lastExpectedMessage = new ParticipantAddedEmail($owner, $participant2, $series, $attributeRepo, $userRepo);
        $this->assertInstanceOf('ParticipantDeletedEmail', $this->fakeEmailService->_LastMessage);
        //		$this->assertEquals($lastExpectedMessage, $this->fakeEmailService->_LastMessage);
    }
}
