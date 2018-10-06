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
        $vendors = DataFileResolver::getInstance()->resolveInstalledVendors();
        // better to put the output together here, because fluid tends to mess up the empty lines
        // that are important to proper rst rendering
        $toctree = [];
        $intend = '    ';
        foreach ($vendors as $vendor) {
            foreach ($vendor->getPackages() as $package) {
                foreach ($package->getVersions() as $version) {
                    $toctree[] = $intend . $vendor->getVendorName() . '/' . $package->getPackageName() . '/' . $version->getVersion() . '/Index' . PHP_EOL;
                }
            }
        }
        $this->view->assign('tocTree', $toctree);
        DataFileResolver::getInstance()->getWriter()->publishDataFile(
            'Index.rst',
            $this->view->render('Root')
        );
    }

    public function exportVendor(SchemaVendor $vendor): void
    {
    }

    public function exportPackage(SchemaPackage $package): void
    {
    }

    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void
    {
        $resolver = DataFileResolver::getInstance();
        if (!$forceUpdate && file_exists($resolver->getPublicDirectoryPath() . $processedSchema->getPath() . 'Index.rst')) {
            return;
        }
        $schema = $processedSchema->getSchema();
        $headline = $schema->getPackage()->getVendor()->getVendorName() . '/' . $schema->getPackage()->getPackageName();
        $decorationHeadlineLength = strlen($headline);
        $headlineDecoration = array_pad([], $decorationHeadlineLength, '=');
        $toctree = [];
        $intend = '    ';
        $subGroupsCount = \count($processedSchema->getDocumentationTree()->getSubGroups());
        if ($subGroupsCount > 0) {
            $toctree[] = $intend . '*/Index' . PHP_EOL;
        }
        $viewHelpers = $processedSchema->getDocumentationTree()->getDocumentedViewHelpers();
        foreach ($viewHelpers as $viewHelper) {
            $toctree[] = $intend . $viewHelper->getLocalName() . PHP_EOL;
        }
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => '../../../',
            'subGroups' => $subGroupsCount,
            'viewHelpers' => \count($viewHelpers),
            'tocTree' => $toctree,
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

        $headline = $viewHelperDocumentation->getName();
        $decorationHeadlineLength = strlen($headline);
        $headlineDecoration = array_pad([], $decorationHeadlineLength, '=');

        $arguments = [];
        foreach ($viewHelperDocumentation->getArgumentDefinitions() as $argumentDefinition) {
            $argumentHeadline = trim($argumentDefinition->getName() . ' (' . $argumentDefinition->getType() . ') ' . ($argumentDefinition->isRequired() ? 'required' : ''));
            $argumentHeadlineDecoration = array_pad([], strlen($argumentHeadline), '-');
            $argumentsData = [
                'headline' => $argumentHeadline,
                'headlineDecoration' => implode('', $argumentHeadlineDecoration),
                'description' => $argumentDefinition->getDescription(),
            ];

            if ($argumentDefinition->getDefaultValue() !== 'NULL') {
                $argumentsData['default'] = 'Default: ' . trim(str_replace(PHP_EOL, '', (string)$argumentDefinition->getDefaultValue())) . PHP_EOL;
            }
            $arguments[] = $argumentsData;
        }
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => '../../../',
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
        $groupPath = $viewHelperDocumentationGroup->getPath() . DIRECTORY_SEPARATOR;
        $backPath = str_repeat('../', substr_count($groupPath, '/'));
        $rootPath = $backPath . '../../../';

        $headline = $viewHelperDocumentationGroup->getGroupId();
        $headlineDecoration = array_pad([], strlen($headline), '=');
        $viewHelpers = $viewHelperDocumentationGroup->getDocumentedViewHelpers();
        $subGroupsCount = \count($viewHelperDocumentationGroup->getSubGroups());
        $toctree = [];
        $intend = '    ';
        if ($subGroupsCount > 0) {
            $toctree[] = $intend . '*/Index' . PHP_EOL;
        }
        foreach ($viewHelpers as $viewHelper) {
            $toctree[] = $intend . $viewHelper->getLocalName() . PHP_EOL;
        }
        $this->view->assignMultiple([
            'headline' => $headline,
            'headlineDecoration' => implode('', $headlineDecoration),
            'rootPath' => $rootPath,
            'viewHelpers' => \count($viewHelpers),
            'subGroups' => $subGroupsCount,
            'tocTree' => $toctree,
        ]);
        $resolver->getWriter()->publishDataFileForSchema(
            $viewHelperDocumentationGroup->getSchema(),
            $groupPath . 'Index.rst',
            $this->view->render('ViewHelperGroup')
        );
    }
}
