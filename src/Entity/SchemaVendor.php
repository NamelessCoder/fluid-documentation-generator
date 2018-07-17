<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Entity;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;

class SchemaVendor
{
    private $vendorName = '';

    public function __construct(string $vendorName)
    {
        $this->vendorName = $vendorName;
    }

    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    /**
     * @return SchemaPackage[]
     */
    public function getPackages(): array
    {
        return DataFileResolver::getInstance()->resolveInstalledPackagesForVendor($this);
    }
}
