<?php

require_once(ROOT_DIR . 'Presenters/Reservation/ReservationDeletePresenter.php');
require_once(ROOT_DIR . 'Pages/Ajax/ReservationDeletePage.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

class ReservationDeletePresenterTests extends TestBase
{
    private $userId;

    /**
     * @var UserSession
     */
    private $user;

    /**
     * @var IReservationDeletePage
     */
    private $page;

    /**
     * @var IDeleteReservationPersistenceService
     */
    private $persistenceService;

    /**
     * @var IReservationHandler
     */
    private $handler;

    /**
     * @var ReservationDeletePresenter
     */
    private $presenter;

    public function setUp(): void
    {
        parent::setup();

        $this->user = $this->fakeServer->UserSession;
        $this->userId = $this->user->UserId;

        $this->persistenceService = $this->createMock('IDeleteReservationPersistenceService');
        $this->handler = $this->createMock('IReservationHandler');

        $this->page = $this->createMock('IReservationDeletePage');

        $this->presenter = new ReservationDeletePresenter(
            $this->page,
            $this->persistenceService,
            $this->handler,
            $this->user
        );
    }

    public function teardown(): void
    {
        parent::teardown();
    }

    public function testLoadsExistingReservationAndDeletesIt()
    {
        $referenceNumber = '109809';
        $seriesUpdateScope = SeriesUpdateScope::ThisInstance;
        $reason = 'reason';

        $expectedSeries = $this->createMock('ExistingReservationSeries');

        $this->page->expects($this->once())
            ->method('GetReferenceNumber')
            ->will($this->returnValue($referenceNumber));

        $this->page->expects($this->once())
            ->method('GetSeriesUpdateScope')
            ->will($this->returnValue($seriesUpdateScope));

        $this->page->expects($this->once())
            ->method('GetReason')
            ->will($this->returnValue($reason));

        $this->persistenceService->expects($this->once())
            ->method('LoadByReferenceNumber')
            ->with($this->equalTo($referenceNumber))
            ->will($this->returnValue($expectedSeries));

        $expectedSeries->expects($this->once())
            ->method('Delete')
            ->with($this->equalTo($this->user), $this->equalTo($reason));

        $expectedSeries->expects($this->once())
            ->method('ApplyChangesTo')
            ->with($this->equalTo($seriesUpdateScope));

        $existingSeries = $this->presenter->BuildReservation();
    }

    public function testHandlingReservationCreationDelegatesToServicesForValidationAndPersistenceAndNotification()
    {
        $builder = new ExistingReservationSeriesBuilder();
        $series = $builder->Build();
        $instance = new Reservation($series, NullDateRange::Instance());
        $series->WithCurrentInstance($instance);

        $this->handler->expects($this->once())
            ->method('Handle')
            ->with($this->equalTo($series), $this->equalTo($this->page))
            ->will($this->returnValue(true));


        $this->presenter->HandleReservation($series);
    }
}
