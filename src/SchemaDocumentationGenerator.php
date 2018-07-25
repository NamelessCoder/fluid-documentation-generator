<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Export\ExporterInterface;

class SchemaDocumentationGenerator
{
    /**
     * @var ExporterInterface[]
     */
    private $exporters = [];

    private $forceUpdate = false;

    public static function getInstance(array $exporters = [], bool $forceUpdate = false): self
    {
        static $instance;
        if (!$instance) {
            $instance = new static($exporters, $forceUpdate);
        }
        return $instance;
    }

    public function __construct(array $exporters, bool $forceUpdate = false)
    {
        foreach ($exporters as $exporter) {
            $exporter->setGenerator($this);
        }
        $this->exporters = $exporters;
        $this->forceUpdate = $forceUpdate;
    }

    public function generateFilesForRoot(): void
    {
        foreach ($this->exporters as $exporter) {
            $exporter->exportRoot($this->forceUpdate);
        }
    }

    public function generateFilesForVendor(SchemaVendor $vendor): void
    {
        foreach ($this->exporters as $exporter) {
            $exporter->exportVendor($vendor, $this->forceUpdate);
        }
    }

    public function generateFilesForPackage(SchemaPackage $package): void
    {
        foreach ($this->exporters as $exporter) {
            $exporter->exportPackage($package, $this->forceUpdate);
        }
    }

    public function generateFilesForSchema(Schema $schema): void
    {
        $processedSchema = $schema->process();
        foreach ($this->exporters as $exporter) {
            // Generate exports for VH groups, but do not generate exports for the root group.
            foreach ($processedSchema->getDocumentationTree()->getDocumentedViewHelpers() as $viewHelperDocumentation) {
                $exporter->exportViewHelper($viewHelperDocumentation);
            }
            foreach ($processedSchema->getDocumentationTree()->getSubGroups() as $viewHelperDocumentationGroup) {
                $this->generateFilesForViewHelperDocumentationGroup($viewHelperDocumentationGroup, $exporter);
            }
            $exporter->exportSchema($processedSchema, $this->forceUpdate);
        }
    }

    public function generateFilesForViewHelperDocumentationGroup(ViewHelperDocumentationGroup $group, ExporterInterface $exporter): void
    {
        foreach ($group->getDocumentedViewHelpers() as $viewHelperDocumentation) {
            $exporter->exportViewHelper($viewHelperDocumentation, $this->forceUpdate);
        }
        foreach ($group->getSubGroups() as $subGroup) {
            $this->generateFilesForViewHelperDocumentationGroup($subGroup, $exporter);
        }
        $exporter->exportViewHelperGroup($group, $this->forceUpdate);
    }

    public function generateResourceLinksForViewHelper(ViewHelperDocumentation $viewHelperDocumentation): array
    {
        $resources = [];
        foreach ($this->exporters as $exporter) {
            $resources = array_merge(
                $resources,
                $exporter->createAdditionalViewHelperResources($viewHelperDocumentation)
            );
        }
        return $resources;
    }

    public function generateResourceLinksForSchema(ProcessedSchema $schema): array
    {
        $resources = [];
        foreach ($this->exporters as $exporter) {
            $resources = array_merge(
                $resources,
                $exporter->createAdditionalSchemaResources($schema)
            );
        }
        return $resources;
    }

    public function generateMachineResourceLinksForViewHelper(ViewHelperDocumentation $viewHelperDocumentation): array
    {
        $resources = [];
        foreach ($this->exporters as $exporter) {
            $resources = array_merge(
                $resources,
                $exporter->createAdditionalViewHelperResources($viewHelperDocumentation, $exporter->getIdentifier())
            );
        }
        return $resources;
    }
}
