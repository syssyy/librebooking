<?php

require_once(ROOT_DIR . 'WebServices/Validators/UserRequestValidator.php');

class UserRequestValidatorTests extends TestBase
{
    /**
     * @var IAttributeService
     */
    private $attributeService;

    /**
     * @var UserRequestValidator
     */
    private $validator;

    /**
     * @var IUserViewRepository
     */
    private $userRepository;

    public function setUp(): void
    {
        parent::setup();

        $this->attributeService = $this->createMock('IAttributeService');
        $this->userRepository = $this->createMock('IUserViewRepository');

        $this->validator = new UserRequestValidator($this->attributeService, $this->userRepository);
    }

    public function testWhenCreateRequestIsJunk()
    {
        $errors = $this->validator->ValidateCreateRequest(null);
        $this->assertEquals(1, count($errors));
    }

    public function testCreateRequiredFields()
    {
        $this->expectsAttributeValidator();
        $request = CreateUserRequest::Example();
        $request->firstName = null;
        $request->lastName = '';
        $request->userName = ' ';
        $request->timezone = ' ';
        $request->language = ' ';
        $request->password = ' ';

        $errors = $this->validator->ValidateCreateRequest($request);
        $this->assertTrue(count($errors) == 6);
    }

    public function testCreateValidatesEmailFormat()
    {
        $this->expectsAttributeValidator();
        $request = CreateUserRequest::Example();
        $request->emailAddress = 'aaaaaa.com';
        $errors = $this->validator->ValidateCreateRequest($request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testCreateValidatesExistingEmail()
    {
        $this->expectsAttributeValidator();
        $request = CreateUserRequest::Example();

        $this->userRepository->expects($this->at(0))
                ->method('UserExists')
                ->with($this->equalTo($request->emailAddress), $this->isNull())
                ->will($this->returnValue(1));

        $errors = $this->validator->ValidateCreateRequest($request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testCreateValidatesExistingUsername()
    {
        $this->expectsAttributeValidator();
        $request = CreateUserRequest::Example();

        $this->userRepository->expects($this->at(1))
                ->method('UserExists')
                ->with($this->isNull(), $this->equalTo($request->userName))
                ->will($this->returnValue(1));

        $errors = $this->validator->ValidateCreateRequest($request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testCreateValidatesAttributes()
    {
        $request = CreateUserRequest::Example();
        $result = new AttributeServiceValidationResult(false, ['error']);
        $this->attributeService->expects($this->once())
                ->method('Validate')
                ->with(
                    $this->equalTo(CustomAttributeCategory::USER),
                    $this->equalTo([new AttributeValue($request->customAttributes[0]->attributeId, $request->customAttributes[0]->attributeValue)])
                )
                ->will($this->returnValue($result));

        $errors = $this->validator->ValidateCreateRequest($request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testWhenUpdateRequestIsJunk()
    {
        $errors = $this->validator->ValidateUpdateRequest(1, null);
        $this->assertEquals(1, count($errors));
    }

    public function testUpdateRequiredFields()
    {
        $this->expectsAttributeValidator();
        $request = UpdateUserRequest::Example();
        $request->firstName = null;
        $request->lastName = '';
        $request->userName = ' ';
        $request->timezone = ' ';

        $errors = $this->validator->ValidateUpdateRequest(1, $request);
        $this->assertTrue(count($errors) == 4);
    }

    public function testUpdateValidatesEmailFormat()
    {
        $this->expectsAttributeValidator();
        $request = UpdateUserRequest::Example();
        $request->emailAddress = 'aaaaaa.com';
        $errors = $this->validator->ValidateUpdateRequest(1, $request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testUpdateValidatesExistingEmail()
    {
        $this->expectsAttributeValidator();
        $request = UpdateUserRequest::Example();

        $this->userRepository->expects($this->at(0))
                ->method('UserExists')
                ->with($this->equalTo($request->emailAddress), $this->isNull())
                ->will($this->returnValue(2));

        $errors = $this->validator->ValidateUpdateRequest(1, $request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testUpdateValidatesExistingUsername()
    {
        $this->expectsAttributeValidator();
        $request = UpdateUserRequest::Example();

        $this->userRepository->expects($this->at(1))
                ->method('UserExists')
                ->with($this->isNull(), $this->equalTo($request->userName))
                ->will($this->returnValue(2));

        $errors = $this->validator->ValidateUpdateRequest(1, $request);
        $this->assertTrue(count($errors) == 1);
    }

    public function testUpdateValidatesAttributes()
    {
        $request = UpdateUserRequest::Example();
        $result = new AttributeServiceValidationResult(false, ['error']);
        $this->attributeService->expects($this->once())
                ->method('Validate')
                ->with(
                    $this->equalTo(CustomAttributeCategory::USER),
                    $this->equalTo([new AttributeValue($request->customAttributes[0]->attributeId, $request->customAttributes[0]->attributeValue)])
                )
                ->will($this->returnValue($result));

        $errors = $this->validator->ValidateUpdateRequest(1, $request);
        $this->assertTrue(count($errors) == 1);
    }

    private function expectsAttributeValidator()
    {
        $this->attributeService->expects($this->any())
                ->method('Validate')
                ->with($this->anything(), $this->anything())
                ->will($this->returnValue(new AttributeServiceValidationResult(true, null)));
    }
}
