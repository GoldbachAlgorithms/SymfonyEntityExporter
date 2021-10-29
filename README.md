# Symfony Entity Exporter

[<img src="https://badgen.net/badge/Powered%20by/Goldbach/yellow" />](https://github.com/Goldbach07/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[<img src="https://badgen.net/badge/Developed%20for/Symfony/:black" />](https://symfony.com/)


Goldbach Algorithms Symfony Entity Exporter (fondly nicknamed Entity Exporter) is a library developed for the Symfony framework with the objective of exporting information from the database in a simple way.

**Compatible output extensions: *CSV*, *XLS* and *PDF***

## Installation

Use the composer to install

```bash
composer require goldbach-algorithms/symfony-entity-exporter
```
## Configuration

Make the settings below according to your project's needs within your .env file

#### ENTITY_PATH
If you have changed the paron directory for entities in your project, you must set the ENTITY_PATH variable within your .env file to indicate the new directory.

#### TRANSITORY_MEMORY
As an option it is also possible to define within the .env file of your project a value for the variable TRANSITORY_MEMORY, which will change the php memory limit during the execution of the export to avoid crashes. The default value is 1GB.


## Usage

See some examples of using Symfony Entity Exporter

**See the *.csv* export example**

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

You can also generate a .pdf file from the entity query return.

**See the *.pdf* export example**

```php
use GoldbachAlgorithms\SymfonyEntityExporter\SymfonyEntityExporter;

# Instantiate a new Entity Exporter
$entityExporter = new SymfonyEntityExporter;

# Set $data (query return) and entity class
$response = $entityExporter->pdf(
            $data,
            User::class,
            UserDataExport::class, # Data Export Template (not required)
            'Filename', # File .pdf name (not required),
            'Header content', # (not required)
            'Footer content', # (not required)
            'P', # Portrait or Landscape (not required)
            '10', # Margin Header (not required)
            '10', # Margin Footer (not required)
            '10', # Top content margin (not required)
            'A4', # Print Format (not required)
            'utf-8', # Char Mode (not required)
        );

# Exporting file (.pdf)
return $response;
```
And you can also export an html template, for example a twig file

**See the *.pdf* by html export example**

```php
use GoldbachAlgorithms\SymfonyEntityExporter\SymfonyEntityExporter;

# Instantiate a new Entity Exporter
$entityExporter = new SymfonyEntityExporter;

# Set a content
$content = $this->renderView('content.html.twig', $vars);

# Call the method pdfByHtml()
$response = $entityExporter->pdfByHtml(
            $content,
            'Filename', # File .pdf name (not required),
            'Header content', # (not required)
            'Footer content', # (not required)
            'P', # Portrait or Landscape (not required)
            '10', # Margin Header (not required)
            '10', # Margin Footer (not required)
            '10', # Top content margin (not required)
            'A4', # Print Format (not required)
            'utf-8', # Char Mode (not required)
        );

# Exporting file (.pdf)
return $response;
```

You can create an export template following the example in [src/ExampleDataExport.php](https://github.com/GoldbachAlgorithms/SymfonyEntityExporter/blob/main/src/ExampleDataExport.php) by adding a App\DataExport directory to your application.


## EasyAdminBundle
The Entity Exporter is compatible with the Easy Admin Bundle, so it is possible to use a request within a Controller on the page and perform data export.

**See the *.xls* export example***

```php
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use GoldbachAlgorithms\SymfonyEntityExporter\SymfonyEntityExporter;

# Inject the AdminContextFactory and define public

/** @var AdminContextFactory  */
public $adminContextFactory;

public function __construct(AdminContextFactory $adminContextFactory)
{
   $this->adminContextFactory = $adminContextFactory;
}

# Instantiate a new Entity Exporter
$entityExporter = new SymfonyEntityExporter;

# Get the FilterFactory into Controller
$filterFactory = $this->get(FilterFactory::class);

# Use the Entity Exporter builder to EasyAdmin
$data = $entityExporter->getEasyAdminQuery(
            $request,
            $filterFactory,
            $this,
            [
                'id' => 1
            ]
        );

# Set $data (query return) and entity class
$response = $entityExporter->xls($data, User::class);

# File .xls
return $response;
```

## License
[MIT](https://choosealicense.com/licenses/mit/)
