<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Tests\Functional;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVersion;
use NamelessCoder\FluidDocumentationGenerator\Export\HtmlExporter;
use NamelessCoder\FluidDocumentationGenerator\Export\JsonExporter;
use NamelessCoder\FluidDocumentationGenerator\Export\XsdExporter;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class SchemaDocumentationGeneratorTest extends TestCase
{
    /**
     * @var SchemaDocumentationGenerator
     */
    protected $subject;

    /**
     * @var vfsStreamDirectory
     */
    protected $vfs;

    protected function setUp()
    {
        $this->vfs = vfsStream::setup('outputDir');
        $this->vfs->addChild(vfsStream::newDirectory('cache'));
        $resolver = DataFileResolver::getInstance(vfsStream::url('outputDir'));
        $resolver->setResourcesDirectory(__DIR__ . '/../../resources/');
        $resolver->setSchemasDirectory(__DIR__ . '/../Fixtures/schemas/');
        $this->subject = new SchemaDocumentationGenerator(
            [
                new JsonExporter(''),
                new XsdExporter(vfsStream::url('')),
                new HtmlExporter(vfsStream::url(''))
            ],
            true
        );
    }

    public function testGenerateResourcesForRoot(): void
    {
        $this->subject->generateFilesForRoot();
        $this->assertTrue($this->vfs->hasChild('outputDir/public/index.json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/index.html'));
    }

    public function testGenerateResourcesForVendor(): void
    {
        $this->subject->generateFilesForVendor(new SchemaVendor('test'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/index.json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/index.html'));
    }

    public function testGenerateResourcesForPackage(): void
    {
        $this->subject->generateFilesForPackage(new SchemaPackage(new SchemaVendor('test'), 'test'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/index.json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/index.html'));
    }

    public function testGenerateResourcesForSchema(): void
    {
        $schema = new Schema(new SchemaVersion(new SchemaPackage(new SchemaVendor('test'), 'test'), '1.0.0'));
        $this->subject->generateFilesForSchema($schema);
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/tree.json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/index.json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/index.html'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/schema.xsd'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/Format'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/Format/Json'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/Format/Json/Encode.html'));
        $this->assertTrue($this->vfs->hasChild('outputDir/public/test/test/1.0.0/Format/Json/Decode.html'));
    }

    public function testGenerateMachineResourceLinksForSchema(): void
    {
        $schemaMock = $this->getMockBuilder(ProcessedSchema::class)->disableOriginalConstructor()->getMock();
        $this->assertSame(
            [
                'JSON tree' => 'tree.json',
                'JSON index' => 'index.json',
                'XSD schema' => 'vfs://schema.xsd',
                'HTML overview' => 'vfs://',
            ],
            $this->subject->generateResourceLinksForSchema($schemaMock)
        );
    }

    public function testGenerateResourceLinksForViewHelper(): void
    {
        $schemaMock = $this->getMockBuilder(ProcessedSchema::class)->disableOriginalConstructor()->getMock();
        $viewHelper = new ViewHelperDocumentation($schemaMock, 'root', 'foo', [], new ViewHelperDocumentationGroup($schemaMock));
        $this->assertSame(
            [
                'JSON schema' => 'Root.json',
                'HTML overview' => 'vfs://Root.html',
            ],
            $this->subject->generateResourceLinksForViewHelper($viewHelper)
        );
    }

    public function testGenerateMachineResourceLinksForViewHelper(): void
    {
        $schemaMock = $this->getMockBuilder(ProcessedSchema::class)->disableOriginalConstructor()->getMock();
        $viewHelper = new ViewHelperDocumentation($schemaMock, 'root', 'foo', [], new ViewHelperDocumentationGroup($schemaMock));
        $this->assertSame(
            [
                'json' => 'Root.json',
                'html' => 'vfs://Root.html',
            ],
            $this->subject->generateMachineResourceLinksForViewHelper($viewHelper)
        );
    }
}
