<?php

namespace GoldbachAlgorithms\SymfonyEntityExporter;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Sasedev\MpdfBundle\Factory\MpdfFactory;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SymfonyEntityExporter extends SymfonyEntityExporterAdmin
{
    const GET = 'get';
    const ENTITIES_PATH = "App\Entity\\";
    const FILTER = 0;
    const __toString = "__toString";
    const __getId = "getId";
    const TRUE = 'true';
    const FALSE = 'false';
    const NOT_SUPPORTED = 'Not supported format';
    const DEFAULT_FIELDS = [
        'IsVerified' => 'isVerified'
    ];
    const TRANSITORY_MEMORY = 'TRANSITORY_MEMORY';
    const ENV_NOT_DEFINED = "is not defined into .env";
    const QUERY_ERROR = "The first parameter must be an array";
    const PARAMETER_NOT_SUPPORTED = "The type of first parameter is not supported. Try to set an array or object.";
    const DEFAULT_TITLE = 'NoTitled';
    const DEFAULT_FILE = 'File';
    const DEFAULT_DELIMITER = ';';
    const SUPPORTED_EXTENSIONS = ['csv','xls'];
    const NOT_SUPPORTED_EXTENSION = 'Extension not supported.';
    const EXPORTER_HEADER = [
        'csv' => 'text/csv',
        'xls' => 'application/vnd.ms-excel',
    ];
    
    public function csv(
        $query,
        $class,
        $exporter = null,
        $title = self::DEFAULT_TITLE,
        $filename = self::DEFAULT_FILE,
        $delimiter = self::DEFAULT_DELIMITER
    ) {
        $this->transitoryMemory();

        $query = $this->validate($query);

        $dataExport = $this->dataExport($class, $exporter);

        $extension = 'csv';

        return $this->execute($query, $dataExport, $title, $filename, $extension, $delimiter);
    }

    public function xls(
        $query,
        $class,
        $exporter = null,
        $title = self::DEFAULT_TITLE,
        $filename = self::DEFAULT_FILE
    ) {
        $this->transitoryMemory();

        $query = $this->validate($query);

        $dataExport = $this->dataExport($class, $exporter);

        $extension = 'xls';

        return $this->execute($query, $dataExport, $title, $filename, $extension);
    }

    public function pdfByHtml(
        string $content,
        string $filename = 'PDF - Goldbach Algorithms',
        string $headerHtml = null,
        string $footerHtml = null,
        string $orientation = 'P',
        string $marginHeader = '10',
        string $marginFooter = '10',
        string $topMargin = '30',
        string $format = 'A4',
        string $mode = 'utf-8'
    ) {
        $MpdfFactory = new MpdfFactory('/tmp');

        $mPdf = $MpdfFactory->createMpdfObject(
            [
                'mode' => $mode,
                'format' => $format,
                'margin_header' => $marginHeader,
                'margin_footer' => $marginFooter,
                'orientation' => $orientation
            ]
        );

        $mPdf
            ->SetTopMargin($topMargin);

        if ($headerHtml) {
            $mPdf
                ->SetHTMLHeader($headerHtml);
        }

        if ($footerHtml) {
            $mPdf
                ->SetFooter($footerHtml);
        }

        $mPdf
            ->WriteHTML($content);
        
        return $MpdfFactory
                    ->createDownloadResponse($mPdf, $filename.".pdf");
    }

    public function pdf(
        $query,
        $class,
        $exporter = null,
        string $filename = 'PDF - Goldbach Algorithms',
        string $headerHtml = null,
        string $footerHtml = null,
        string $orientation = 'L', # P (Portrait) or L (Landscape)
        string $marginHeader = '10',
        string $marginFooter = '10',
        string $topMargin = '30',
        string $format = 'A4',
        string $mode = 'utf-8'
    ) {
        $this->transitoryMemory();

        $query = $this->validate($query);

        $dataExport = $this->dataExport($class, $exporter);

        $html = $this->arrayToHtml($query, $dataExport);
        
        return $this
                ->pdfByHtml(
                    $html,
                    $filename,
                    $headerHtml,
                    $footerHtml,
                    $orientation,
                    $marginHeader,
                    $marginFooter,
                    $topMargin,
                    $format,
                    $mode
                );
    }

    public function arrayToHtml($query, $dataExport)
    {
        $values = $this->getArrayByQuery($query, $dataExport);

        $bases = $values[0];
        $table = "<table border='1' cellpadding='2'>";
        $table .= "<tr>";
        foreach ($bases as $key => $base) {
            $table .= "<td><b>".$key."</b></td>";
        }
        $table .= "</tr>";
        for ($j = 0; $j < count($values); $j++) {
            $table .= "<tr>";
            foreach ($values[$j] as $v) {
                $table .= "<td>".$v."</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</table>";
        return $table;
    }

    public function dataExport($class, $dataExportClass)
    {
        if (is_null($dataExportClass)) {
            $entityFields = $this->getEntityFields($class);
            
            $dataExport = $this->setDataExport(
                $class,
                $entityFields
            );
        } else {
            $__dataExport = $this->validateDataExport($dataExportClass, $class);
            
            $dataExport = $__dataExport->getColumns();
        }

        return $dataExport;
    }

    public function getEntityFields(
        string $class
    ): array {
        $dataExport = new $class;
        $dataExport = (array) $dataExport;
        $entityFields = [];

        foreach ($dataExport as $key => $dt) {
            $entityFields[] = $this->str_to_camel(trim(str_replace($class, "", $key)), true);
        }

        return $entityFields;
    }

    public function setDataExport(
        string $class,
        array $fields
    ) {
        $class = new $class;

        $reflectionObject = new \ReflectionObject($class);
        $methods = $reflectionObject->getMethods();
        
        foreach ($fields as $field) {
            $filter = [
                function ($class) use ($field, $methods) {
                    return $this->getItem($methods, $class, $field);
                }
            ];

            $exports[$field] = $filter[self::FILTER];
        }

        return $exports;
    }

    public function getItem(
        $methods,
        $class,
        $field
    ) {
        foreach ($methods as $method) {
            $isDefault = $this->isDefault($field);

            if ($isDefault) {
                $compare = self::DEFAULT_FIELDS[$field];
            } else {
                $compare = self::GET.$field;
            }

            if ($compare == $method->getName()) {
                $entityPath = $method->getReturnType();

                if (empty($entityPath)) {
                    if (isset($_ENV['ENTITY_DIRECTORY']) && !empty($_ENV['ENTITY_DIRECTORY'])) {
                        $entityPath = $_ENV['ENTITY_DIRECTORY'] . "\\" . $field;
                    } else {
                        $entityPath = self::ENTITIES_PATH . "\\" . $field;
                    }
                }

                $entityPath = str_replace("\\\\", "\\", $entityPath);
                
                if (class_exists($entityPath)) {
                    $data = $method->invoke($class);

                    if (is_object($data)) {
                        $hasToString = $this->hasToString($entityPath);

                        $hasId = $this->hasId($entityPath);

                        if ($hasToString) {
                            return $method->invoke($class)->__toString();
                        } elseif ($hasId) {
                            return $method->invoke($class)->getId();
                        } else {
                            return $method->invoke($class);
                        }
                    }

                    return $data;
                } else {
                    try {
                        $data = $method->invoke($class);
                    } catch (\Exception $e) {
                        throw new BadRequestException(
                            "GoldbachAlgorithmsError: " . $e->getMessage()
                        );
                    }

                    if (is_array($data)) {
                        return json_encode($data);
                    } elseif (is_bool($data)) {
                        if ($data) {
                            return self::TRUE;
                        } else {
                            return self::FALSE;
                        }
                    } elseif (is_string($data)) {
                        return $data;
                    } elseif (is_int($data)) {
                        return $data;
                    } elseif (is_null($data)) {
                        return null;
                    } elseif (is_object($data)) {
                        if ($data instanceof DateTime) {
                            return $data;
                        } else {
                            $objects = [];
                            foreach ($data as $object) {
                                $objects[] = $object->getId();
                            }
                            return json_encode($objects);
                        }
                    } else {
                        return self::NOT_SUPPORTED;
                    }
                }
            }
        }
    }

    public function execute(
        $query,
        $dataExport,
        $title,
        $filename,
        $extension,
        $delimiter = null
    ) {
        $entities = new ArrayCollection($query);
      
        $this->dataExportTest($entities, $dataExport);

        if (in_array($extension, self::SUPPORTED_EXTENSIONS)) {
            try {
                return $this->export(
                    $entities,
                    $dataExport,
                    $title,
                    $filename,
                    $extension,
                    $delimiter
                );
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: ". self::NOT_SUPPORTED_EXTENSION
            );
        }
    }

    public function dataExportTest(
        $entities,
        $dataExport
    ) {
        $entity = $entities[0];
        if (!is_null($entity)) {
            foreach ($dataExport as $callback) {
                $callback($entity);
            }
        }
    }

    public function getArrayByQuery($query, $columns)
    {
        $entities = new ArrayCollection($query);

        $headers = array_keys($columns);
        
        $i = 0;

        while ($entity = $entities->current()) {
            $values[$i] = [];

            $h = 0;
            
            foreach ($columns as $column => $callback) {
                try {
                    $value = $callback;

                    if (is_callable($callback)) {
                        $value = $callback($entity);
                    }

                    $values[$i][$headers[$h]] = $value;
                } catch (\Exception $e) {
                }
                $h++;
            }

            $entities->next();
            $i++;
        }

        return $values;
    }

    public function export(
        $entities,
        $columns,
        $title,
        $filename,
        $extension,
        $delimiter
    ): StreamedResponse {
        $response = new StreamedResponse();

        $spreadsheet = new Spreadsheet();

        $valuex = $response->setCallback(function () use ($entities, $columns, $spreadsheet, $title, $extension, $delimiter) {
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle($title);
            
            $headers = array_keys($columns);

            $abc = [];
            foreach (range('A', 'Z') as $first) {
                array_push($abc, $first);
            }

            foreach (range('A', 'Z') as $second) {
                foreach (range('A', 'Z') as $third) {
                    array_push($abc, $second.$third);
                }
            }

            foreach ($headers as $key => $header) {
                $sheet->getCell($abc[$key].'1')->setValue($header);
            }
            
            $i = 2;
            while ($entity = $entities->current()) {
                $values = [];
                
                foreach ($columns as $column => $callback) {
                    try {
                        $value = $callback;

                        if (is_callable($callback)) {
                            $value = $callback($entity);
                        }

                        $values[] = $value;
                    } catch (\Exception $e) {
                    }
                }

                $sheet->fromArray($values, null, 'A'.$i, true);

                $i++;

                $entities->next();
            }

            return $$value;


            if ($extension == 'csv') {
                $writer =  new Csv($spreadsheet);
                $writer->setUseBOM(true);
                $writer->setDelimiter($delimiter);
                $writer->setSheetIndex(0);
            }
            
            if ($extension == 'xls') {
                $writer = new Xls($spreadsheet);
            }
            
            $writer->save('php://output');
        });
        dd($valuex);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', self::EXPORTER_HEADER[$extension]);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.' . $extension . '"');

        return $response;
    }

    public function getEasyAdminQuery(
        Request $request,
        object $filterFactory,
        object $controller,
        array $aditionalParameters = []
    ): array {
        $context = $this->_getEasyAdminRefererContext($request);
        $searchDto = $controller->adminContextFactory->getSearchDto($request, $context->getCrud());
        $fields = FieldCollection::new($controller->configureFields(Crud::PAGE_INDEX));
        $filters = $filterFactory
                    ->create(
                        $context->getCrud()->getFiltersConfig(),
                        $fields,
                        $context->getEntity()
                    );

        $data = $controller->createIndexQueryBuilder(
            $searchDto,
            $context->getEntity(),
            $fields,
            $filters
        );

        if (!is_null($aditionalParameters)) {
            foreach ($aditionalParameters as $key => $p) {
                $data = $data
                        ->andWhere("entity.$key = :$key")
                        ->setParameter("$key", $p);
            }
        }

        $data = $data
                    ->getQuery()
                    ->getResult();
        
        return $data;
    }

    public function _getEasyAdminRefererContext(Request $request)
    {
        \parse_str(\parse_url($request->query->get(EA::REFERRER))[EA::QUERY], $referrerQuery);

        if (array_key_exists(EA::FILTERS, $referrerQuery)) {
            $request->query->set(EA::FILTERS, $referrerQuery[EA::FILTERS]);
        }

        if (array_key_exists(EA::QUERY, $referrerQuery)) {
            $request->query->set(EA::QUERY, $referrerQuery[EA::QUERY]);
        }

        return $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
    }
}
