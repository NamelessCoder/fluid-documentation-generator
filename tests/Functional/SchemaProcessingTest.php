<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Tests\Functional;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVersion;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

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
     * @test
     */
    public function viewHelperDocumentationContainsEntriesAsExpected(): void
    {
        $vendor = new SchemaVendor('test');
        $package = new SchemaPackage($vendor, 'test');
        $version = new SchemaVersion($package, '1.0.0');
        $schema = new Schema($version);
        $processedSchema = $schema->process();
        $documentedViewHelpers = $processedSchema->getDocumentedViewHelpers();
        $this->assertArrayHasKey('root', $documentedViewHelpers);
        $this->assertArrayHasKey('format.json.encode', $documentedViewHelpers);
        $this->assertArrayHasKey('format.json.decode', $documentedViewHelpers);
    }
}
