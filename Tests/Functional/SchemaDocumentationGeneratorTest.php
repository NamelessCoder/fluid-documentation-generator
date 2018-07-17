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
        $this->vfs = vfsStream::setup('public');
        $this->vfs->addChild(vfsStream::newDirectory('cache'));
        $resolver = DataFileResolver::getInstance(vfsStream::url('public'));
        $resolver->setResourcesDirectory(__DIR__ . '/../../resources/');
        $resolver->setSchemasDirectory(__DIR__ . '/../Fixtures/schemas/');
        $this->subject = new SchemaDocumentationGenerator(
            [
                new JsonExporter(''),
                new XsdExporter(''),
                new HtmlExporter('')
            ],
            true
        );
    }

    public function testGenerateResourcesForRoot(): void
    {
        $this->subject->generateFilesForRoot();
        $folder = $this->vfs->getChild('public');
        $this->assertSame('index.json', $folder->getChild('index.json')->getName());
        $this->assertSame('index.html', $folder->getChild('index.html')->getName());
    }

    public function testGenerateResourcesForVendor(): void
    {
        $this->subject->generateFilesForVendor(new SchemaVendor('test'));
        $folder = $this->vfs->getChild('public')->getChild('test');
        $this->assertSame('index.json', $folder->getChild('index.json')->getName());
        $this->assertSame('index.html', $folder->getChild('index.html')->getName());
    }

    public function testGenerateResourcesForPackage(): void
    {
        $this->subject->generateFilesForPackage(new SchemaPackage(new SchemaVendor('test'), 'test'));
        $folder = $this->vfs->getChild('public')->getChild('test')->getChild('test');
        $this->assertSame('index.json', $folder->getChild('index.json')->getName());
        $this->assertSame('index.html', $folder->getChild('index.html')->getName());
    }

    public function testGenerateResourcesForSchema(): void
    {
        $schema = new Schema(new SchemaVersion(new SchemaPackage(new SchemaVendor('test'), 'test'), '1.0.0'));
        $this->subject->generateFilesForSchema($schema);
        $folder = $this->vfs->getChild('public')->getChild('test')->getChild('test')->getChild('1.0.0');
        $this->assertSame('tree.json', $folder->getChild('tree.json')->getName());
        $this->assertSame('index.json', $folder->getChild('index.json')->getName());
        $this->assertSame('index.html', $folder->getChild('index.html')->getName());
        $this->assertSame('schema.xsd', $folder->getChild('schema.xsd')->getName());
        $this->assertSame('Format', $folder->getChild('Format')->getName());
        $this->assertSame('Json', $folder->getChild('Format')->getChild('Json')->getName());
        $this->assertSame('Encode.html', $folder->getChild('Format')->getChild('Json')->getChild('Encode.html')->getName());
        $this->assertSame('Decode.html', $folder->getChild('Format')->getChild('Json')->getChild('Decode.html')->getName());
    }

    public function testGenerateMachineResourceLinksForSchema(): void
    {
        $schemaMock = $this->getMockBuilder(ProcessedSchema::class)->disableOriginalConstructor()->getMock();
        $this->assertSame(
            [
                'JSON tree' => 'tree.json',
                'JSON index' => 'index.json',
                'XSD schema' => 'schema.xsd',
                'HTML overview' => '',
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
                'HTML overview' => 'Root.html',
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
                'html' => 'Root.html',
            ],
            $this->subject->generateMachineResourceLinksForViewHelper($viewHelper)
        );
    }
}
