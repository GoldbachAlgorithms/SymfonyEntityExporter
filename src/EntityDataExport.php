<?php

namespace App\DataExport;

class EntityDataExport{

    public function getFields(){
        
        return [
            'Id' => function ($entity) {
                return $entity->getId();
            }
        ];
    }
}