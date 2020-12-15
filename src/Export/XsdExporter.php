<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Export;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;

class XsdExporter implements ExporterInterface
{
    /**
     * @var SchemaDocumentationGenerator
     */
    private $generator;

    private $rootUrl = '';

    public function getIdentifier(): string
    {
        return 'xsd';
    }

    public function __construct(string $rootUrl)
    {
        $this->rootUrl = $rootUrl;
    }

    public function setGenerator(SchemaDocumentationGenerator $generator): void
    {
        $this->generator = $generator;
    }

    public function createAdditionalViewHelperResources(ViewHelperDocumentation $viewHelperDocumentation, ?string $label = null): array
    {
        return [];
    }

    public function createAdditionalSchemaResources(ProcessedSchema $schema, ?string $label = null): array
    {
        return [
            $label ?? 'XSD schema' => $this->rootUrl . $schema->getPath() . 'schema.xsd',
        ];
    }

    public function exportRoot(bool $forceUpdate): void
    {
        // This method has no function in this exporter
    }

    public function exportVendor(SchemaVendor $vendor): void
    {
        // This method has no function in this exporter
    }

    public function exportPackage(SchemaPackage $package): void
    {
        // This method has no function in this exporter
    }

    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($processedSchema->getPath() . 'schema.xsd')) {
            return;
        }
        $resolver->getWriter()->publishDataFileForSchema(
            $processedSchema,
            'schema.xsd',
            $processedSchema->getSchema()->getSchemaSource()
        );
    }

    public function exportViewHelper(ViewHelperDocumentation $viewHelperDocumentation, bool $forceUpdate = false): void
    {
        // This method has no function in this exporter
    }

    public function exportViewHelperGroup(ViewHelperDocumentationGroup $viewHelperDocumentationGroup, bool $forceUpdate = false): void
    {
        // This method has no function in this exporter
    }
}
