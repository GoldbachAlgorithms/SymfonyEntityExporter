<?php

namespace App\DataExport;

class ExampleDataExport
{

    /**
     * @var string
     */
    private $entity;

    /**
     * @var array
     */
    private $columns;

    public function __construct()
    {
        $this->loader();
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    public function getColumns(): ?array
    {
        return $this->columns;
    }

    
    private function loader()
    {
        # Set the entity name and the custom columns
        
        $this->setEntity('User');

        $columns =  [
            'User' => function ($user) {
                return $user->getAdresses()->getStreet();
            },
        ];

        $this->setColumns($columns);
    }
}
