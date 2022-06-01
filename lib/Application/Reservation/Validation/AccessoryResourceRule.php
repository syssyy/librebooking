<?php

require_once(ROOT_DIR . 'Domain/Access/AccessoryRepository.php');

class AccessoryResourceRule implements IReservationValidationRule
{
    /**
     * @var IAccessoryRepository
     */
    private $accessoryRepository;

    /**
     * @var Resources
     */
    private $strings;

    public function __construct(IAccessoryRepository $accessoryRepository)
    {
        $this->accessoryRepository = $accessoryRepository;
        $this->strings = Resources::GetInstance();
    }

    public function Validate($reservationSeries, $retryParameters)
    {
        $errors = [];

        /** @var ReservationAccessory[] $bookedAccessories */
        $bookedAccessories = [];

        foreach ($reservationSeries->Accessories() as $accessory) {
            $bookedAccessories[$accessory->AccessoryId] = $accessory;
        }

        $accessories = $this->accessoryRepository->LoadAll();

        $association = $this->GetResourcesAndRequiredAccessories($accessories);

        $bookedResourceIds = $reservationSeries->AllResourceIds();

        $badAccessories = $association->GetAccessoriesThatCannotBeBookedWithGivenResources($bookedAccessories, $bookedResourceIds);

        foreach ($badAccessories as $accessoryName) {
            $errors[] = $this->strings->GetString('AccessoryResourceAssociationErrorMessage', $accessoryName);
        }

        foreach ($reservationSeries->AllResources() as $resource) {
            $resourceId = $resource->GetResourceId();
            if ($association->ContainsResource($resourceId)) {
                /** @var Accessory[] $resourceAccessories */
                $resourceAccessories = $association->GetResourceAccessories($resourceId);
                foreach ($resourceAccessories as $accessory) {
                    $accessoryId = $accessory->GetId();

                    $resource = $accessory->GetResource($resourceId);
                    if (!empty($resource->MinQuantity) && $bookedAccessories[$accessoryId]->QuantityReserved < $resource->MinQuantity) {
                        $errors[] = $this->strings->GetString('AccessoryMinQuantityErrorMessage', [$resource->MinQuantity, $accessory->GetName()]);
                    }

                    if (!empty($resource->MaxQuantity) && $bookedAccessories[$accessoryId]->QuantityReserved > $resource->MaxQuantity) {
                        $errors[] = $this->strings->GetString('AccessoryMaxQuantityErrorMessage', [$resource->MaxQuantity, $accessory->GetName()]);
                    }
                }
            }
        }

        return new ReservationRuleResult(count($errors) == 0, implode("\n", $errors));
    }

    /**
     * @param Accessory[] $accessories
     * @return ResourceAccessoryAssociation
     */
    private function GetResourcesAndRequiredAccessories($accessories)
    {
        $association = new ResourceAccessoryAssociation();
        foreach ($accessories as $accessory) {
            $association->AddAccessory($accessory);
            foreach ($accessory->Resources() as $resource) {
                $association->AddRelationship($resource, $accessory);
            }
        }

        return $association;
    }
}

class ResourceAccessoryAssociation
{
    private $resources = [];

    /** @var Accessory[] */
    private $accessories = [];

    /**
     * @param ResourceAccessory $resource
     * @param Accessory $accessory
     */
    public function AddRelationship($resource, $accessory)
    {
        $this->resources[$resource->ResourceId][$accessory->GetId()] = $accessory;
    }

    /**
     * @param int $resourceId
     * @return bool
     */
    public function ContainsResource($resourceId)
    {
        return array_key_exists($resourceId, $this->resources);
    }

    /**
     * @param int $resourceId
     * @return Accessory[]
     */
    public function GetResourceAccessories($resourceId)
    {
        return $this->resources[$resourceId];
    }

    /**
     * @param Accessory $accessory
     */
    public function AddAccessory(Accessory $accessory)
    {
        $this->accessories[$accessory->GetId()] = $accessory;
    }

    /**
     * @param ReservationAccessory[] $bookedAccessories
     * @param int[] $bookedResourceIds
     * @return string[]
     */
    public function GetAccessoriesThatCannotBeBookedWithGivenResources($bookedAccessories, $bookedResourceIds)
    {
        $badAccessories = [];

        $bookedAccessoryIds = [];
        foreach ($bookedAccessories as $accessory) {
            $accessoryId = $accessory->AccessoryId;
            $bookedAccessoryIds[] = $accessoryId;

            if ($this->AccessoryNeedsARequiredResourceToBeBooked($accessoryId, $bookedResourceIds)) {
                $badAccessories[] = $this->accessories[$accessoryId]->GetName();
            }
        }

        return $badAccessories;
    }

    private function AccessoryNeedsARequiredResourceToBeBooked($accessoryId, $bookedResourceIds)
    {
        $accessory = $this->accessories[$accessoryId];
        if ($accessory->IsTiedToResource()) {
            Log::Debug('Checking required resources for accessory %s', $accessoryId);
            $overlap = array_intersect($accessory->ResourceIds(), $bookedResourceIds);
            return count($overlap) == 0;
        }
        return false;
    }
}
