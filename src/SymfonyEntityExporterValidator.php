<?php

namespace GoldbachAlgorithms\SymfonyEntityExporter;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SymfonyEntityExporterValidator{

    const PARAMETER_NOT_SUPPORTED = "The type of first parameter is not supported. Try to set an array or object.";
    const GET_COLUMNS_NOT_FOUND = 'Method getColumns() not found in ';
    const GET_ENTITY_NOT_FOUND = 'Method getEntity() not found in ';
    const DIFF_ENTITY = 'The entity informed for the construction of the file is different from the one defined in ';
    const ENTITIES_PATH = "App\Entity\\";

    public function validate($query)
    {
        if (is_object($query) || is_null($query)) {
            $prepareObject = [];
            $prepareObject[] = $query;
            $query = $prepareObject;
            return $query;
        } elseif (is_array($query)) {
            return $query;
        } else {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: " . self::PARAMETER_NOT_SUPPORTED
            );
        }
    }

    public function validateDataExport($dataExportClass, $class)
    {
        $dataExport = new $dataExportClass;

        $getEntity = method_exists($dataExport, 'getEntity');
        if (!$getEntity) {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: " . self::GET_ENTITY_NOT_FOUND. " in $dataExportClass"
            );
        }

        if (isset($_ENV['ENTITY_DIRECTORY']) && !empty($_ENV['ENTITY_DIRECTORY'])) {
            $entityPath = $_ENV['ENTITY_DIRECTORY'];
        } else {
            $entityPath = self::ENTITIES_PATH;
        }

        $dataExportEntity = $entityPath . $dataExport->getEntity();
        if ($dataExportEntity != $class) {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: " . self::DIFF_ENTITY. "$dataExportClass"
            );
        }

        $getColumns = method_exists($dataExport, 'getColumns');
        if (!$getColumns) {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: " . self::GET_COLUMNS_NOT_FOUND. "$dataExportClass"
            );
        }

        return $dataExport;
    }
}