<?php

namespace App\DataExport;

class EntityDataExport{

    private function getFields(){
        return [
            'Data de Geração' => function ($entity) {
                return $entity->getCreatedAt()->format('d/m/Y');
            },
            'Número do Voucher' => function ($entity) {
                return $entity->getCode();
            },
            'Código da Escola' => function ($entity) {
                return $entity->getSchoolCode();
            },
            'Nome da Escola' => function ($entity) {
                return $entity->getSchool();
            },
            'Cidade' => function ($entity) {
                return $entity->getVoucherGroup()->getSchool()->getCity();
            },
            'Estado' => function ($entity) {
                return $entity->getVoucherGroup()->getSchool()->getState();
            },
            'Status' => function ($entity) {
                return $entity->getStatus();
            },
        ];
    }
}