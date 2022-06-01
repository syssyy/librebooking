<?php

require_once(ROOT_DIR . 'Pages/PersonalCalendarPage.php');
require_once(ROOT_DIR . 'Presenters/Calendar/PersonalCalendarPresenter.php');

class PersonalCalendarPresenterTests extends TestBase
{
    /**
     * @var ICommonCalendarPage|PHPUnit_Framework_MockObject_MockObject
     */
    private $page;

    /**
     * @var PersonalCalendarPresenter
     */
    private $presenter;

    /**
     * @var IReservationViewRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var ICalendarFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $calendarFactory;

    /**
     * @var ICalendarSubscriptionService|PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionService;

    /**
     * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepository;

    /**
     * @var IResourceService|PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceService;

    /**
     * @var IScheduleRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleRepository;

    public function setUp(): void
    {
        parent::setup();

        $this->page = $this->createMock('ICommonCalendarPage');
        $this->repository = $this->createMock('IReservationViewRepository');
        $this->calendarFactory = $this->createMock('ICalendarFactory');
        $this->subscriptionService = $this->createMock('ICalendarSubscriptionService');
        $this->userRepository = $this->createMock('IUserRepository');
        $this->resourceService = $this->createMock('IResourceService');
        $this->scheduleRepository = $this->createMock('IScheduleRepository');

        $this->presenter = new PersonalCalendarPresenter(
            $this->page,
            $this->repository,
            $this->calendarFactory,
            $this->subscriptionService,
            $this->userRepository,
            $this->resourceService,
            $this->scheduleRepository
        );
    }

    public function testBindsEmptyCalendarToPageWhenNoReservationsAreFound()
    {
        $userId = 10;
        $this->fakeUser->UserId = $userId;
        $userTimezone = "America/New_York";

        $calendarType = CalendarTypes::Month;

        $showInaccessible = true;
        $this->fakeConfig->SetSectionKey(ConfigSection::SCHEDULE, ConfigKeys::SCHEDULE_SHOW_INACCESSIBLE_RESOURCES, 'true');

        $resourceId = 1;
        $resourceName = 'rn';
        $defaultScheduleId = 12;

        $r1 = new FakeBookableResource(1, 'dude1');
        $r2 = new FakeBookableResource($resourceId, $resourceName);
        $resources = [$r1, $r2];

        $resourceGroupTree = new ResourceGroupTree();

        $schedules = [new Schedule(1, null, false, 2, null), new Schedule($defaultScheduleId, null, true, 3, null),];

        $this->page
                ->expects($this->atLeastOnce())
                ->method('GetScheduleId')
                ->will($this->returnValue($defaultScheduleId));

        $this->page
                ->expects($this->atLeastOnce())
                ->method('GetResourceId')
                ->will($this->returnValue(null));

        $this->page->expects($this->once())
                   ->method('GetCalendarType')
                   ->will($this->returnValue($calendarType));

        $details = new CalendarSubscriptionDetails(true);

        $this->subscriptionService->expects($this->once())
                                  ->method('ForUser')
                                  ->with($this->equalTo($userId))
                                  ->will($this->returnValue($details));

        $this->page->expects($this->once())
                   ->method('BindSubscription')
                   ->with($this->equalTo($details));

        $this->scheduleRepository
                ->expects($this->atLeastOnce())
                ->method('GetAll')
                ->will($this->returnValue($schedules));

        $this->resourceService
                ->expects($this->atLeastOnce())
                ->method('GetAllResources')
                ->with($this->equalTo($showInaccessible), $this->equalTo($this->fakeUser))
                ->will($this->returnValue($resources));

        $this->resourceService
                ->expects($this->atLeastOnce())
                ->method('GetResourceGroups')
                ->with($this->anything(), $this->equalTo($this->fakeUser))
                ->will($this->returnValue($resourceGroupTree));
        $this->page
                ->expects($this->atLeastOnce())
                ->method('SetFirstDay')
                ->with($this->equalTo($schedules[1]->GetWeekdayStart()));

        $this->userRepository->expects($this->once())
                    ->method('LoadById')
                    ->with($this->equalTo($this->fakeUser->UserId))
                    ->will($this->returnValue(new FakeUser()));

        $calendarFilters = new CalendarFilters($schedules, $resources, $defaultScheduleId, null, $resourceGroupTree);
        $this->page->expects($this->atLeastOnce())->method('BindFilters')->with($this->equalTo($calendarFilters));

        $this->presenter->PageLoad($this->fakeUser, $userTimezone);
    }
}
