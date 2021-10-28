# Symfony Entity Exporter

Goldbach Algorithms Symfony Entity Exporter (fondly nicknamed Entity Exporter) is a library developed for the Symfony framework with the objective of exporting information from the database in a simple way.

## Installation

Use the composer to install

```bash
composer require goldbach-algorithms/symfony-entity-exporter
```

## Usage

```php
use GoldbachAlgorithms\SymfonyEntityExporter\SymfonyEntityExporter;

# Instantiate a new Entity Exporter
$entityExporter = new SymfonyEntityExporter;

# Set $data (query return) and entity class
$response = $entityExporter->csv(
            $data,
            User::class,
            UserDataExport::class, # Data Export Template (not required)
            'Title', # Title (not required)
            'Filename', # File .csv name (not required)
            ';' # .csv Delimiter (not required) default ;
        );

# Exporting file (.csv)
return $response;
```

You can create an export template following the example in [src/EntityDataExport.php](https://github.com/GoldbachAlgorithms/SymfonyEntityExporter/blob/main/src/EntityDataExport.php) by adding a App\DataExport directory to your application.


## EasyAdminBundle
The Entity Exporter is compatible with the Easy Admin Bundle, so it is possible to use a request within a Controller on the page and perform data export.

```php
use GoldbachAlgorithms\SymfonyEntityExporter\SymfonyEntityExporter;

# Instantiate a new Entity Exporter
$entityExporter = new SymfonyEntityExporter;

#
$filterFactory = $this->get(FilterFactory::class);

$data = $entityExporter->getEasyAdminQuery(
            $request,
            $filterFactory,
            $this,
            [
                'id' => 1
            ]
        );

# Set $data (query return) and entity class
$response = $entityExporter->csv($data, User::class);

# File .csv
return $response;
```

## License
[MIT](https://choosealicense.com/licenses/mit/)