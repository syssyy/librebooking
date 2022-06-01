<?php

class RequiresApprovalRuleTests extends TestBase
{
    /**
     * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
     */
    public $userRepository;

    /**
     * @var IAuthorizationService|PHPUnit_Framework_MockObject_MockObject
     */
    public $authorizationService;

    /**
     * @var RequiresApprovalRule
     */
    public $rule;

    public function setUp(): void
    {
        parent::setup();

        $this->authorizationService = $this->createMock('IAuthorizationService');

        $this->rule = new RequiresApprovalRule($this->authorizationService);
    }

    public function testMarksReservationAsRequiringApprovalIfUserIsNotApprover()
    {
        $series = new TestReservationSeries();
        $resource = new FakeBookableResource(1);
        $resource->RequiresApproval(true);
        $series->WithResource($resource);
        $series->WithBookedBy($this->fakeUser);

        $this->authorizationService->expects($this->once())
                            ->method('CanApproveForResource')
                            ->with($this->equalTo($this->fakeUser), $this->equalTo($resource))
                            ->will($this->returnValue(false));

        $this->rule->Validate($series, null);

        $this->assertTrue($series->RequiresApproval());
    }

    public function testDoesNotMarkAsRequiringApprovalIfNoResourceNeedsIt()
    {
        $series = new TestReservationSeries();
        $resource = new FakeBookableResource(1);
        $resource->RequiresApproval(false);
        $series->WithResource($resource);
        $series->WithBookedBy($this->fakeUser);

        $this->rule->Validate($series, null);

        $this->assertFalse($series->RequiresApproval());
    }

    public function testDoesNotMarkAsRequiringApprovalIfUserCanApproveForResource()
    {
        $series = new TestReservationSeries();
        $resource = new FakeBookableResource(1);
        $resource->RequiresApproval(true);
        $series->WithResource($resource);
        $series->WithBookedBy($this->fakeUser);

        $this->authorizationService->expects($this->once())
                                    ->method('CanApproveForResource')
                                    ->with($this->equalTo($this->fakeUser), $this->equalTo($resource))
                                    ->will($this->returnValue(true));

        $this->rule->Validate($series, null);

        $this->assertFalse($series->RequiresApproval());
    }
}
