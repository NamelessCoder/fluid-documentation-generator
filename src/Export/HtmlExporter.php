<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Export;

use cebe\markdown\GithubMarkdown;
use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

class HtmlExporter implements ExporterInterface
{
    /**
     * @var TemplateView
     */
    private $view;

    /**
     * @var SchemaDocumentationGenerator
     */
    private $generator;

    private $rootUrl;

    public function __construct(?string $rootUrl = null)
    {
        $this->rootUrl = $rootUrl;
        $resourcesDirectory = DataFileResolver::getInstance()->getResourcesDirectoryPath();
        $this->view = new TemplateView();
        $this->view->getRenderingContext()->setCache(new SimpleFileCache(DataFileResolver::getInstance()->getCacheDirectoryPath()));
        $this->view->getRenderingContext()->setTemplatePaths(new TemplatePaths(
            [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [$resourcesDirectory . 'templates' . DIRECTORY_SEPARATOR],
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => [$resourcesDirectory . 'layouts' . DIRECTORY_SEPARATOR],
                TemplatePaths::CONFIG_PARTIALROOTPATHS => [$resourcesDirectory . 'partials' . DIRECTORY_SEPARATOR],
            ]
        ));
    }

    public function getIdentifier(): string
    {
        return 'html';
    }

    public function setGenerator(SchemaDocumentationGenerator $generator): void
    {
        $this->generator = $generator;
    }

    public function createAdditionalViewHelperResources(ViewHelperDocumentation $viewHelperDocumentation, ?string $label = null): array
    {
        return [
            $label ?? 'HTML overview' => $this->rootUrl . $viewHelperDocumentation->getSchema()->getPath() . $viewHelperDocumentation->getPath() . '.html',
        ];
    }

    public function createAdditionalSchemaResources(ProcessedSchema $schema, ?string $label = null): array
    {
        return [
            $label ?? 'HTML overview' => $this->rootUrl . $schema->getPath(),
        ];
    }

    public function exportRoot(bool $forceUpdate = false): void
    {
        $this->view->assign('vendors', DataFileResolver::getInstance()->resolveInstalledVendors());
        $this->view->assign('readme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readRootDataFile('README.md')) );
        DataFileResolver::getInstance()->getWriter()->publishDataFile(
            'index.html',
            $this->view->render('Root')
        );
    }

    public function exportVendor(SchemaVendor $vendor): void
    {
        $resolver = DataFileResolver::getInstance();
        $this->view->assign('vendor', $vendor);
        $this->view->assign('title', $vendor->getVendorName() . ' - ViewHelper Documentation');
        $this->view->assign('vendors', DataFileResolver::getInstance()->resolveInstalledVendors());
        $this->view->assign('readme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readVendorDataFile($vendor, 'README.md')) );
        $this->view->assign('metadata', DataFileResolver::getInstance()->readVendorMetaDataFile($vendor));
        $this->view->assign('rootPath', '../');
        $resolver->getWriter()->publishDataFileForVendor(
            $vendor,
            'index.html',
            $this->view->render('Vendor')
        );
    }

    public function exportPackage(SchemaPackage $package): void
    {
        $resolver = DataFileResolver::getInstance();
        $this->view->assign('package', $package);
        $this->view->assign('title', $package->getFullyQualifiedName() . ' - ViewHelper Documentation');
        $this->view->assign('rootPath', '../../');
        $this->view->assign('vendor', $package->getVendor());
        $this->view->assign('vendors', DataFileResolver::getInstance()->resolveInstalledVendors());
        $this->view->assign('vendorReadme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readVendorDataFile($package->getVendor(), 'README.md')) );
        $this->view->assign('packageReadme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readPackageDataFile($package, 'README.md')) );
        $resolver->getWriter()->publishDataFileForPackage(
            $package,
            'index.html',
            $this->view->render('Package')
        );
    }

    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $processedSchema->getPath() . 'index.html')) {
            return;
        }
        $schema = $processedSchema->getSchema();
        $this->view->assign('schema', $processedSchema);
        $this->view->assign('title', $schema->getVersion()->getFullyQualifiedName() . ' - ViewHelpers');
        $this->view->assign('rootPath', '../../../');
        $this->view->assign('vendorReadme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readVendorDataFile($schema->getVendor(), 'README.md')) );
        $this->view->assign('packageReadme', (new GithubMarkdown())->parse(DataFileResolver::getInstance()->readPackageDataFile($schema->getPackage(), 'README.md')) );
        $this->view->assign('metadata', DataFileResolver::getInstance()->readSchemaMetaDataFile($schema));
        $this->view->assign('resources', $this->generator->generateResourceLinksForSchema($processedSchema));
        $resolver->getWriter()->publishDataFileForSchema(
            $processedSchema,
            'index.html',
            $this->view->render('Schema')
        );
    }

    public function exportViewHelper(ViewHelperDocumentation $viewHelperDocumentation, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $viewHelperDocumentation->getSchema()->getPath() . $viewHelperDocumentation->getPath() . '.html')) {
            return;
        }
        $path = $viewHelperDocumentation->getPath();
        $expandedGroups = [];
        if (strpos($path, '/') !== false) {
            $rebuiltPath = '';
            $segments = explode('/', $path);
            array_pop($segments);
            foreach ($segments as $segment) {
                $rebuiltPath .= $segment;
                $expandedGroups[$rebuiltPath] = $rebuiltPath;
                $rebuiltPath .= '/';
            }
        }
        $schema = $viewHelperDocumentation->getSchema()->getSchema();
        $backPath = str_repeat('../', substr_count($path, '/'));
        $rootPath = $backPath . '../../../';
        $this->view->assign('metadata', DataFileResolver::getInstance()->readSchemaMetaDataFile($schema));
        $this->view->assign('viewHelper', $viewHelperDocumentation);
        $this->view->assign('rootPath', $rootPath);
        $this->view->assign('title', $viewHelperDocumentation->getName() . ' - ' . $schema->getVersion()->getFullyQualifiedName());
        $this->view->assign('resources', $this->generator->generateResourceLinksForViewHelper($viewHelperDocumentation));
        $this->view->assign('schemaResources', $this->generator->generateResourceLinksForSchema($viewHelperDocumentation->getSchema()));
        $this->view->assign('expandedGroups', $expandedGroups);
        $this->view->assign('basePath', $rootPath . $viewHelperDocumentation->getSchema()->getPath());
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentation->getSchema(),
            $path . '.html',
            $this->view->render('ViewHelper')
        );
    }

    public function exportViewHelperGroup(ViewHelperDocumentationGroup $viewHelperDocumentationGroup, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        $groupPath = $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR;
        $schema = $viewHelperDocumentationGroup->getSchema();
        $publishingPath = $resolver->getPublicDirectoryPath() . $schema->getPath() . $groupPath;
        if (!$forceUpdate && file_exists($publishingPath . 'index.html')) {
            #return;
        }
        $expandedGroups = [];
        if (strpos($groupPath, '/') !== false) {
            $rebuiltPath = '';
            $segments = explode('/', $groupPath);
            array_pop($segments);
            foreach ($segments as $segment) {
                $rebuiltPath .= $segment;
                $expandedGroups[$rebuiltPath] = $rebuiltPath;
                $rebuiltPath .= '/';
            }
        }
        $backPath = str_repeat('../', substr_count($groupPath, '/'));
        $rootPath = $backPath . '../../../';
        $this->view->assign('metadata', DataFileResolver::getInstance()->readSchemaMetaDataFile($schema->getSchema()));
        $this->view->assign('title', $viewHelperDocumentationGroup->getName() . ' ViewHelpers - ' . $schema->getSchema()->getVersion()->getFullyQualifiedName());
        $this->view->assign('rootPath', $rootPath);
        $this->view->assign('backPath', $backPath);
        $this->view->assign('basePath', $rootPath . $schema->getPath());
        $this->view->assign('expandedGroups', $expandedGroups);
        $this->view->assign('resources', $this->generator->generateResourceLinksForSchema($schema));
        $this->view->assign('group', $viewHelperDocumentationGroup);
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentationGroup->getSchema(),
            $groupPath . 'index.html',
            $this->view->render('ViewHelperGroup')
        );
    }
}
