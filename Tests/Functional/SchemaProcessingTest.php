<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Tests\Functional;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVersion;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class SchemaProcessingTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        DataFileResolver::getInstance(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR);
    }

    /**
     * @var vfsStreamDirectory
     */
    protected $vfs;

    protected function setUp()
    {
        $this->vfs = vfsStream::setup('public');
        $this->vfs->addChild(vfsStream::newDirectory('cache'));
        $resolver = DataFileResolver::getInstance(vfsStream::url('public'));
        $resolver->setSchemasDirectory(__DIR__ . '/../Fixtures/schemas/');
    }

    /**
     * @param string $viewHelperName
     * @param string $expectedDocumentation
     * @param array $expectedArguments
     * @dataProvider getDocumentedViewHelperExpectations
     */
    public function testViewHelperIsDocumentedCorrectly(string $viewHelperName, string $expectedDocumentation, array $expectedArguments): void
    {
        $vendor = new SchemaVendor('test');
        $package = new SchemaPackage($vendor, 'test');
        $version = new SchemaVersion($package, '1.0.0');
        $schema = new Schema($version);
        $processedSchema = $schema->process();
        $documentedViewHelpers = $processedSchema->getDocumentedViewHelpers();
        $this->assertArrayHasKey($viewHelperName, $documentedViewHelpers);
    }

    public function getDocumentedViewHelperExpectations(): array
    {
        return [
            [
                'format.json.encode', 'foobar', [new ArgumentDefinition('test', 'string', 'foobar', true)]
            ]
        ];
    }
}
