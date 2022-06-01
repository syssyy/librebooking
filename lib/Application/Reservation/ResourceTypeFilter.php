<?php

class ResourceTypeFilter implements IResourceFilter
{
    /**
     * @var $resourcetypename
     */
    private $resourcetypeids = [];

    public function __construct($resourcetypename)
    {
        $reader = ServiceLocator::GetDatabase()
                  ->Query(new GetResourceTypeByNameCommand($resourcetypename));

        while ($row = $reader->GetRow()) {
            $this->resourcetypeids[] = $row[ColumnNames::RESOURCE_TYPE_ID];
        }

        $reader->Free();
    }

    /**
     * @param IResource $resource
     * @return bool
     */
    public function ShouldInclude($assignment)
    {
        return in_array($assignment->GetResourceTypeId(), $this->resourcetypeids);
    }
}
