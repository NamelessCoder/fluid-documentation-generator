<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Export;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Data\GraphDataHandler;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;

class JsonExporter implements ExporterInterface
{

    /**
     * @var SchemaDocumentationGenerator
     */
    private $generator;

    private $rootUrl = '';

    public function __construct(string $rootUrl)
    {
        $this->rootUrl = $rootUrl;
    }

    public function getIdentifier(): string
    {
        return 'json';
    }

    public function setGenerator(SchemaDocumentationGenerator $generator): void
    {
        $this->generator = $generator;
    }

    public function createAdditionalViewHelperResources(ViewHelperDocumentation $viewHelperDocumentation, ?string $label = null): array
    {
        return [
            $label ?? 'JSON schema' => $this->rootUrl . $viewHelperDocumentation->getSchema()->getPath() . $viewHelperDocumentation->getPath() . '.json',
        ];
    }

    public function createAdditionalSchemaResources(ProcessedSchema $schema, ?string $label = null): array
    {
        return [
            $label ? $label . '-tree' : 'JSON tree' => $this->rootUrl . $schema->getPath() . 'tree.json',
            $label ? $label . '-index' :  'JSON index' => $this->rootUrl . $schema->getPath() . 'index.json',
        ];
    }

    public function exportRoot(): void
    {
        $resolver = DataFileResolver::getInstance();
        $index = [];
        foreach (DataFileResolver::getInstance()->resolveInstalledVendors() as $vendor) {
            $packages = [];
            foreach ($vendor->getPackages() as $package) {
                $versions = [];
                foreach ($package->getVersions() as $version) {
                    $schema = new ProcessedSchema(new Schema($version));
                    $versions[] = [
                        'version' => $version->getVersion(),
                        'urls' => $this->createAdditionalSchemaResources($schema)
                    ];
                }
                $packages[] = [
                    'name' => $package->getFullyQualifiedName(),
                    'versions' => $versions
                ];
            }
            $index[] = [
                'name' => $vendor->getVendorName(),
                'packages' => $packages,
            ];
        }
        $resolver->getWriter()->publishDataFile(
            'index.json',
            $index
        );
    }

    public function exportVendor(SchemaVendor $vendor): void
    {
        $resolver = DataFileResolver::getInstance();
        $index = [];
        foreach ($vendor->getPackages() as $package) {
            $index[] = $package->getPackageName();
        }
        $resolver->getWriter()->publishDataFile(
            $vendor->getVendorName() . DIRECTORY_SEPARATOR . 'index.json',
            $index
        );
    }

    public function exportPackage(SchemaPackage $package): void
    {
        $resolver = DataFileResolver::getInstance();
        $index = [];
        foreach ($package->getVersions() as $version) {
            $index[] = $version->getVersion();
        }
        $resolver->getWriter()->publishDataFile(
            $package->getVendor()->getVendorName() . DIRECTORY_SEPARATOR . $package->getPackageName() . DIRECTORY_SEPARATOR . 'index.json',
            $index
        );
    }

    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $processedSchema->getPath() . 'index.json')) {
            return;
        }
        $index = [];
        $tree = [];
        $graphDataHandler = GraphDataHandler::getInstance($this->rootUrl);
        foreach ($processedSchema->getDocumentedViewHelpers() as $name => $viewHelperDocumentation) {
            $segments = explode('.', $name);
            $targetParentArray = &$tree;
            if (count($segments) > 1) {
                foreach (array_slice($segments, 0, -1) as $segment) {
                    if (!array_key_exists($segment, $targetParentArray)) {
                        $targetParentArray[$segment] = [];
                    }
                    $targetParentArray =& $targetParentArray[$segment];
                }
            }
            $data = $graphDataHandler->createViewHelperData($viewHelperDocumentation, true);

            $targetParentArray[end($segments)] = $data;
            $index[] = $data;
        }
        $resolver->getWriter()->publishDataFileForSchema(
            $processedSchema,
            'tree.json',
            $tree
        );
        $resolver->getWriter()->publishDataFileForSchema(
            $processedSchema,
            'index.json',
            $index
        );
    }

    public function exportViewHelper(ViewHelperDocumentation $viewHelperDocumentation, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $viewHelperDocumentation->getSchema()->getPath() . $viewHelperDocumentation->getPath() . '.json')) {
            return;
        }
        $schema = $viewHelperDocumentation->getSchema();
        $graphDataHandler = GraphDataHandler::getInstance($this->rootUrl);
        $resolver->getWriter()->publishDataFileForSchema(
            $schema,
            $viewHelperDocumentation->getPath() . '.json',
            $graphDataHandler->createViewHelperData($viewHelperDocumentation)
        );
    }

    public function exportViewHelperGroup(ViewHelperDocumentationGroup $viewHelperDocumentationGroup, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        $publishingPath = $resolver->getPublicDirectoryPath() . $viewHelperDocumentationGroup->getSchema()->getPath() . $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR;
        if (!$forceUpdate && file_exists($publishingPath . 'index.json') && file_exists($publishingPath . 'tree.json')) {
            return;
        }
        $index = [];
        $tree = [];
        $graphDataHandler = GraphDataHandler::getInstance($this->rootUrl);
        foreach ($viewHelperDocumentationGroup->getDocumentedViewHelpers() as $name => $viewHelperDocumentation) {
            $segments = explode('.', $name);
            $targetParentArray = &$tree;
            if (count($segments) > 1) {
                foreach (array_slice($segments, 0, -1) as $segment) {
                    if (!array_key_exists($segment, $targetParentArray)) {
                        $targetParentArray[$segment] = [];
                    }
                    $targetParentArray =& $targetParentArray[$segment];
                }
            }
            $data = $graphDataHandler->createViewHelperData($viewHelperDocumentation, true);

            $targetParentArray[end($segments)] = $data;
            $index[] = $data;
        }
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentationGroup->getSchema(),
            $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR . 'tree.json',
            $tree
        );
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentationGroup->getSchema(),
            $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR . 'index.json',
            $index
        );
    }
}
