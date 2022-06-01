<?php

require_once(ROOT_DIR . 'lib/Config/namespace.php');
require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'lib/Database/namespace.php');
require_once(ROOT_DIR . 'lib/Database/Commands/namespace.php');

require_once(ROOT_DIR . 'Controls/Dashboard/AnnouncementsControl.php');
require_once(ROOT_DIR . 'Controls/Dashboard/UpcomingReservations.php');
require_once(ROOT_DIR . 'Controls/Dashboard/ResourceAvailabilityControl.php');

class DashboardPresenter
{
    private $_page;

    public function __construct(IDashboardPage $page)
    {
        $this->_page = $page;
    }

    public function Initialize()
    {
        $announcement = new AnnouncementsControl(new SmartyPage());
        $upcomingReservations = new UpcomingReservations(new SmartyPage());
        $availability = new ResourceAvailabilityControl(new SmartyPage());

        $this->_page->AddItem($announcement);
        $this->_page->AddItem($upcomingReservations);
        $this->_page->AddItem($availability);

        if (ServiceLocator::GetServer()->GetUserSession()->IsAdmin || ServiceLocator::GetServer()->GetUserSession()->IsResourceAdmin || ServiceLocator::GetServer()->GetUserSession()->IsScheduleAdmin) {
            $allUpcomingReservations = new AllUpcomingReservations(new SmartyPage());
            $this->_page->AddItem($allUpcomingReservations);
        }
    }
}
