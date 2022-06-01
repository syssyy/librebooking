<?php

require_once(ROOT_DIR . 'lib/Application/Schedule/namespace.php');

class CalendarSubscriptionServiceTests extends TestBase
{
    /**
     * @var CalendarSubscriptionService
     */
    private $service;

    /**
     * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepo;

    /**
     * @var IResourceRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceRepo;

    /**
     * @var IScheduleRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleRepo;

    public function setUp(): void
    {
        parent::setup();

        $this->userRepo = $this->createMock('IUserRepository');
        $this->resourceRepo = $this->createMock('IResourceRepository');
        $this->scheduleRepo = $this->createMock('IScheduleRepository');

        $this->service = new CalendarSubscriptionService($this->userRepo, $this->resourceRepo, $this->scheduleRepo);
    }

    public function testGetsUserByPublicId()
    {
        $expected = new FakeUser();
        $publicId = uniqid();

        $this->userRepo->expects($this->once())
                ->method('LoadByPublicId')
                ->with($this->equalTo($publicId))
                ->will($this->returnValue($expected));

        $actual = $this->service->GetUser($publicId);

        $this->assertEquals($expected, $actual);
    }

    public function testGetsResourceByPublicId()
    {
        $expected = new FakeBookableResource(123);
        $publicId = uniqid();

        $this->resourceRepo->expects($this->once())
                ->method('LoadByPublicId')
                ->with($this->equalTo($publicId))
                ->will($this->returnValue($expected));

        $actual = $this->service->GetResource($publicId);

        $this->assertEquals($expected, $actual);
    }

    public function testGetsScheduleByPublicId()
    {
        $expected = new FakeSchedule();
        $publicId = uniqid();

        $this->scheduleRepo->expects($this->once())
                ->method('LoadByPublicId')
                ->with($this->equalTo($publicId))
                ->will($this->returnValue($expected));

        $actual = $this->service->GetSchedule($publicId);

        $this->assertEquals($expected, $actual);
    }
}
