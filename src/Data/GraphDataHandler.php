<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Data;

use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVersion;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class GraphDataHandler
{
    const GRAPH_SCHEMA = 'schema';
    const GRAPH_VENDOR = 'vendor';
    const GRAPH_PACKAGE = 'package';
    const GRAPH_VERSION = 'version';
    const GRAPH_VIEW_HELPER = 'viewHelper';
    const GRAPH_VIEW_HELPER_GROUP = 'group';
    const GRAPH_ARGUMENT = 'argument';

    private $url = '';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    static function getInstance(string $url): self
    {
        return new static($url);
    }

    public function createSchemaData(ProcessedSchema $schema, bool $summary = false): array
    {
        return $this->merge(
            [static::GRAPH_PACKAGE => $this->createPackageData($schema->getSchema()->getPackage(), $summary)],
            [static::GRAPH_VERSION => $this->createVersionData($schema->getSchema()->getVersion(), $summary)],
            [static::GRAPH_VENDOR => $this->createVendorGraphData($schema->getSchema()->getVendor(), $summary)],
            ['viewHelpers' => $this->createPropertyIndexedDataArray($schema->getDocumentedViewHelpers(), 'name', $summary)]
        );
    }

    public function createVendorGraphData(SchemaVendor $vendor, bool $summary = false): array
    {
        return [
            'name' => $vendor->getVendorName(),
            'packages' => $this->createPropertyIndexedDataArray(
                DataFileResolver::getInstance()->resolveInstalledPackagesForVendor($vendor),
                'packageName',
                $summary
            ),
        ];
    }

    public function createPackageData(SchemaPackage $package, bool $summary = false): array
    {
        return [
            'name' => $package->getPackageName(),
            'fullyQualifiedName' => $package->getFullyQualifiedName(),
            'versions' => $this->createPropertyIndexedDataArray(
                $package->getVersions(),
                '',
                $summary
            )
        ];
    }

    public function createVersionData(SchemaVersion $version, bool $summary = false): array
    {
        return [
            'name' => $version->getVersion(),
            'fullyQualifiedName' => $version->getFullyQualifiedName(),
        ];
    }

    public function createViewHelperData(ViewHelperDocumentation $viewHelper, bool $summary = false): array
    {
        $schema = $viewHelper->getSchema();
        $viewHelperData = [
            'name' => $viewHelper->getName(),
            'localName' => $viewHelper->getLocalName(),
            'phpName' => $viewHelper->getPhpName(),
            'urls' => SchemaDocumentationGenerator::getInstance()->generateMachineResourceLinksForViewHelper($viewHelper),
        ];
        if (!$summary) {
            $viewHelperData = $this->merge(
                $viewHelperData,
                ['arguments' => $this->createPropertyIndexedDataArray($viewHelper->getArgumentDefinitions(), 'name')],
                [static::GRAPH_VIEW_HELPER_GROUP => $this->createViewHelperGroupData($viewHelper->getGroup(), true)],
                [static::GRAPH_SCHEMA => $this->createSchemaData($schema, true)]
            );
        }
        return $viewHelperData;
    }

    public function createViewHelperArgumentData(ArgumentDefinition $argumentDefinition): array
    {
        return [
            'name' => $argumentDefinition->getName(),
            'type' => $argumentDefinition->getType(),
            'description' => $argumentDefinition->getDescription(),
            'default' => $argumentDefinition->getDefaultValue(),
            'required' => $argumentDefinition->isRequired()
        ];
    }

    public function createViewHelperGroupData(ViewHelperDocumentationGroup $viewHelperGroup, bool $summary = false): array
    {
        return [
            'name' => $viewHelperGroup->getName(),
            'viewHelpers' => $this->createPropertyIndexedDataArray($viewHelperGroup->getDocumentedViewHelpers(), 'name', true),
        ];
    }

    public function merge(...$arrays): array
    {
        return array_merge_recursive(...$arrays);
    }

    private function createPropertyIndexedDataArray(array $values, string $propertyNameContainingKey, bool $summary = false): array
    {
        $structured = [];
        foreach ($values as $originalKey => $value) {
            $key = $propertyNameContainingKey ? $value->{'get' . ucfirst($propertyNameContainingKey)}() : $originalKey;
            switch (get_class($value)) {
                case SchemaVersion::class:
                    $graphData = $this->createVersionData($value, $summary);
                    break;
                case SchemaPackage::class:
                    $graphData = $this->createPackageData($value, $summary);
                    break;
                case ArgumentDefinition::class:
                    $graphData = $this->createViewHelperArgumentData($value);
                    break;
                case ViewHelperDocumentation::class:
                    $graphData = $this->createViewHelperData($value, $summary);
                    break;
                default:
                    break;
            }
            $structured[$key] = $graphData;
        }
        return $structured;
    }
}
