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

class Typo3BackendViewhelperDirStructureRstDocumentGenerationTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $vfs;

    /**
     * the generated file is compared against this fixture file
     * @var string
     */
    private $fixtureFilePath = __DIR__ . '/../Fixtures/rendering/output/Documentation';

    /**
     * output of the generation process
     * @var string
     */
    private $generatedFilePath = 'outputDir/public/Index.rst';

    /**
     * @test
     */
    public function compareDirStructure()
    {
        $expected = $this->directoryToArray($this->fixtureFilePath);
        $actual = $this->directoryToArray($this->vfs->getChild('outputDir/public')->url());
        $this->assertSame($expected, $actual);
    }

    private function directoryToArray(string $directory): array
    {

        $result = [];

        $cdir = scandir($directory);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, ['.', '..'])) {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $this->directoryToArray($directory . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    protected function setUp()
    {
        $this->vfs = vfsStream::setup('outputDir');
        $this->vfs->addChild(vfsStream::newDirectory('cache'));
        $dataFileResolver = DataFileResolver::getInstance(vfsStream::url('outputDir'));
        $dataFileResolver->setResourcesDirectory(__DIR__ . '/../../resources/');
        $dataFileResolver->setSchemasDirectory(__DIR__ . '/../Fixtures/rendering/input/');
        $schemaDocumentationGenerator = new SchemaDocumentationGenerator(
            [
                new RstExporter()
            ]
        );
        $schemaDocumentationGenerator->generateFilesForRoot();
        foreach ($dataFileResolver->resolveInstalledVendors() as $vendor) {
            $schemaDocumentationGenerator->generateFilesForVendor($vendor);
            foreach ($vendor->getPackages() as $package) {
                $schemaDocumentationGenerator->generateFilesForPackage($package);
                foreach ($package->getVersions() as $version) {
                    $schemaDocumentationGenerator->generateFilesForSchema(new Schema($version));
                }
            }
        }
    }
}