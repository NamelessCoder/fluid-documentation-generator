<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Tests\Functional;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use PHPUnit\Framework\TestCase;

class FileResolvingTest extends TestCase
{
    public static function setUpBeforeClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
        DataFileResolver::getInstance(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR);
    }

    public function testResolvesInstalledVendors(): void
    {
        $vendors = DataFileResolver::getInstance()->resolveInstalledVendors();
        $this->assertSame('test', $vendors[0]->getVendorName());
    }

    public function testResolvesInstalledPackages(): void
    {
        $packages = DataFileResolver::getInstance()->resolveInstalledPackagesForVendor(new SchemaVendor('test'));
        $this->assertSame('test', $packages[0]->getPackageName());
    }

    public function testResolvesInstalledVersions(): void
    {
        $versions = DataFileResolver::getInstance()->resolveInstalledVersionsForPackage(new SchemaPackage(new SchemaVendor('test'), 'test'));
        $this->assertSame('1.0.0', $versions[0]->getVersion());
    }
}
