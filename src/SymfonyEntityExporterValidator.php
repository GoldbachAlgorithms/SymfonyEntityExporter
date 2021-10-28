<?php

namespace GoldbachAlgorithms\SymfonyEntityExporter;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SymfonyEntityExporterValidator{

    const PARAMETER_NOT_SUPPORTED = "The type of first parameter is not supported. Try to set an array or object.";

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
}