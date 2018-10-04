<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Tests\Functional;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Export\RstExporter;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class Typo3BackendViewhelperRstDocumentationGenerationTest extends TestCase
{
    /**
     * @var SchemaDocumentationGenerator
     */
    protected $subject;

    /**
     * @var vfsStreamDirectory
     */
    protected $vfs;

    /**
     * @var DataFileResolver
     */
    private $dataFileResolver;

    /**
     * @test
     */
    public function renderRootRstFile()
    {
        $this->subject->generateFilesForRoot();

        foreach ($this->dataFileResolver->resolveInstalledVendors() as $vendor) {
            $this->subject->generateFilesForVendor($vendor);
            foreach ($vendor->getPackages() as $package) {
                $this->subject->generateFilesForPackage($package);
                foreach ($package->getVersions() as $version) {
                    echo $version->getVersion() . ' ';
                    $this->subject->generateFilesForSchema(new Schema($version));
                }
            }
        }
        $this->assertSame(file_get_contents(__DIR__ . '/../Fixtures/output/Documentation/Index.rst'),
            $this->vfs->getChild('outputDir/public/Index.rst'));
    }

    protected function setUp()
    {
        $this->vfs = vfsStream::setup('outputDir');
        $this->vfs->addChild(vfsStream::newDirectory('cache'));
        $this->dataFileResolver = DataFileResolver::getInstance(vfsStream::url('outputDir'));
        $this->dataFileResolver->setResourcesDirectory(__DIR__ . '/../../resources/');
        $this->dataFileResolver->setSchemasDirectory(__DIR__ . '/../Fixtures/schemas/typo3');
        $this->subject = new SchemaDocumentationGenerator(
            [
                new RstExporter()
            ]
        );
    }
}
