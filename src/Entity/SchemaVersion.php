<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Entity;

class SchemaVersion
{
    private $version = '';

    /**
     * @var SchemaPackage
     */
    private $package;

    public function __construct(SchemaPackage $package, string $version)
    {
        $this->package = $package;
        $this->version = $version;
    }

    public function getFullyQualifiedName(): string
    {
        return $this->package->getVendor()->getVendorName() . '/' . $this->package->getPackageName() . ':' . $this->version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPackage(): SchemaPackage
    {
        return $this->package;
    }
}
