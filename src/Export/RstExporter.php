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
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

class RstExporter implements ExporterInterface
{
    /**
     * @var TemplateView
     */
    private $view;

    /**
     * @var SchemaDocumentationGenerator
     */
    private $generator;

    /**
     * @var null|string
     */
    private $rootUrl;

    /**
     * intention level for toctree structures
     * @var string
     */
    private $intend = '   ';

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
                TemplatePaths::CONFIG_FORMAT => 'rst'
            ]
        ));
    }

    public function getIdentifier(): string
    {
        return 'rst';
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
        return [];
    }

    public function exportRoot(bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . 'Index.rst')) {
            return;
        }

        $vendors = $resolver->resolveInstalledVendors();
        // better to put the output together here, because fluid tends to mess up the empty lines
        // that are important to proper rst rendering
        $toctree = [];
        foreach ($vendors as $vendor) {
            foreach ($vendor->getPackages() as $package) {
                foreach ($package->getVersions() as $version) {
                    $toctree[] = $this->intend . $vendor->getVendorName() . '/' . $package->getPackageName() . '/' . $version->getVersion() . '/Index' . PHP_EOL;
                }
            }
        }
        $this->view->assign('tocTree', $toctree);
        $resolver->getWriter()->publishDataFile(
            'Index.rst',
            $this->view->render('Root')
        );
    }

    public function exportVendor(SchemaVendor $vendor): void
    {
        // not needed in this export
    }

    public function exportPackage(SchemaPackage $package): void
    {
        // not needed in this export
    }

    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $processedSchema->getPath() . 'Index.rst')) {
            return;
        }
        $schema = $processedSchema->getSchema();
        $headline = $schema->getPackage()->getVendor()->getVendorName() . '/' . $schema->getPackage()->getPackageName();
        $headlineDecoration = array_pad([], strlen($headline), '=');
        $subGroupsCount = \count($processedSchema->getDocumentationTree()->getSubGroups());
        $viewHelpers = $processedSchema->getDocumentationTree()->getDocumentedViewHelpers();
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => '../../../',
            'subGroups' => $subGroupsCount,
            'viewHelpers' => \count($viewHelpers),
            'tocTree' => $this->getTocTree($viewHelpers, $subGroupsCount),
        ]);
        $resolver->getWriter()->publishDataFileForSchema(
            $processedSchema,
            'Index.rst',
            $this->view->render('Schema')
        );
    }

    public function exportViewHelper(ViewHelperDocumentation $viewHelperDocumentation, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $viewHelperDocumentation->getSchema()->getPath() . $viewHelperDocumentation->getPath() . '.rst')) {
            return;
        }
        $path = $viewHelperDocumentation->getPath();
        $backPath = str_repeat('../', substr_count($path, '/'));
        $rootPath = $backPath . '../../../';

        $headline = $viewHelperDocumentation->getName();
        $headlineDecoration = array_pad([], strlen($headline), '=');

        $arguments = [];
        foreach ($viewHelperDocumentation->getArgumentDefinitions() as $argumentDefinition) {
            $argumentHeadline = trim($argumentDefinition->getName() . ' (' . $argumentDefinition->getType() . ') ' . ($argumentDefinition->isRequired() ? 'required' : ''));
            $argumentHeadlineDecoration = array_pad([], strlen($argumentHeadline), '-');
            $argumentsData = [
                'headline' => $argumentHeadline,
                'headlineDecoration' => implode('', $argumentHeadlineDecoration),
                'description' => trim($argumentDefinition->getDescription()),
            ];

            $defaultValue = $argumentDefinition->getDefaultValue();
            if ($defaultValue !== 'NULL' && $defaultValue !== "''") {
                $sanitizedDefault = str_replace(PHP_EOL, '', $defaultValue);
                $argumentsData['default'] = 'Default: ' . trim($sanitizedDefault) . PHP_EOL;
            }
            $arguments[] = $argumentsData;
        }
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => $rootPath,
            'viewHelper' => $viewHelperDocumentation,
            'arguments' => $arguments,
        ]);
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentation->getSchema(),
            $path . '.rst',
            $this->view->render('ViewHelper')
        );
    }

    public function exportViewHelperGroup(ViewHelperDocumentationGroup $viewHelperDocumentationGroup, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $viewHelperDocumentationGroup->getPath() . 'Index.rst')) {
            return;
        }

        $groupPath = $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR;
        $backPath = str_repeat('../', substr_count($groupPath, '/'));
        $rootPath = $backPath . '../../../';

        $headline = $viewHelperDocumentationGroup->getGroupId();
        $headlineDecoration = array_pad([], strlen($headline), '=');
        $viewHelpers = $viewHelperDocumentationGroup->getDocumentedViewHelpers();
        $subGroupsCount = \count($viewHelperDocumentationGroup->getSubGroups());
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => $rootPath,
            'viewHelpers' => \count($viewHelpers),
            'subGroups' => $subGroupsCount,
            'tocTree' => $this->getTocTree($viewHelpers, $subGroupsCount),
        ]);
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentationGroup->getSchema(),
            $groupPath . 'Index.rst',
            $this->view->render('ViewHelperGroup')
        );
    }

    protected function getTocTree(array $viewHelpers, int $subGroupsCount): array
    {
        $toctree = [];
        if ($subGroupsCount > 0) {
            $toctree[] = $this->intend . '*/Index' . PHP_EOL;
        }
        foreach ($viewHelpers as $viewHelper) {
            $toctree[] = $this->intend . $viewHelper->getLocalName() . PHP_EOL;
        }
        return $toctree;
    }
}
