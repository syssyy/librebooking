<?php

require_once(ROOT_DIR . 'Pages/Reservation/NewReservationPage.php');
require_once(ROOT_DIR . 'Presenters/Reservation/GuestReservationPresenter.php');

interface IGuestReservationPage extends INewReservationPage
{
    /**
     * @return bool
     */
    public function GuestInformationCollected();

    /**
     * @return string
     */
    public function GetEmail();

    /**
     * @return bool
     */
    public function IsCreatingAccount();

    /**
     * @return bool
     */
    public function GetTermsOfServiceAcknowledgement();
}

class GuestReservationPage extends NewReservationPage implements IGuestReservationPage
{
    public function PageLoad()
    {
        if (Configuration::Instance()->GetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_ALLOW_GUEST_BOOKING, new BooleanConverter())) {
            $this->presenter = $this->GetPresenter();
            $this->presenter->PageLoad();
            $this->Set('ReturnUrl', Pages::SCHEDULE);
            $this->Display($this->GetTemplateName());
        } else {
            $this->RedirectToError(ErrorMessages::INSUFFICIENT_PERMISSIONS);
        }
    }

    protected function GetPresenter()
    {
        return new GuestReservationPresenter(
            $this,
            new GuestRegistration(new PasswordEncryption(), new UserRepository(), new GuestRegistrationNotificationStrategy(), new GuestReservationPermissionStrategy($this)),
            new WebAuthentication(PluginManager::Instance()->LoadAuthentication()),
            $this->LoadInitializerFactory(),
            new NewReservationPreconditionService()
        );
    }

    protected function GetTemplateName()
    {
        if ($this->GuestInformationCollected()) {
            return parent::GetTemplateName();
        }

        return 'Reservation/collect-guest.tpl';
    }

    public function GuestInformationCollected()
    {
        return !ServiceLocator::GetServer()->GetUserSession()->IsGuest();
    }

    public function GetEmail()
    {
        return $this->GetForm(FormKeys::EMAIL);
    }

    public function IsCreatingAccount()
    {
        return $this->IsPostBack() && !$this->GuestInformationCollected();
    }

    public function GetTermsOfServiceAcknowledgement()
    {
        return $this->GetCheckbox(FormKeys::TOS_ACKNOWLEDGEMENT);
    }
}
