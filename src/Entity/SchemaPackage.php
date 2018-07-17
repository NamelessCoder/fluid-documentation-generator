<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Entity;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;

class SchemaPackage
{
    /**
     * @var SchemaVendor
     */
    private $vendor;
    private $packageName = '';

    public function __construct(SchemaVendor $vendor, string $packageName)
    {
        $this->vendor = $vendor;
        $this->packageName = $packageName;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getFullyQualifiedName(): string
    {
        return $this->vendor->getVendorName() . '/' . $this->packageName;
    }

    public function getVendor(): SchemaVendor
    {
        return $this->vendor;
    }

    /**
     * @return SchemaVersion[]
     */
    public function getVersions(): array
    {
        return DataFileResolver::getInstance()->resolveInstalledVersionsForPackage($this);
    }
}
