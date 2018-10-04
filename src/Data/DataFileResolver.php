<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Data;

use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVersion;
use NamelessCoder\FluidDocumentationGenerator\Exception\SchemaFileNotFoundException;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;

class DataFileResolver
{
    const SCHEMA_FILENAME = 'schema.xsd';
    const CACHE_DIRECTORY = 'cache';
    const PUBLIC_DIRECTORY = 'public';
    const SCHEMAS_DIRECTORY = 'schemas';
    const RESOURCES_DIRECTORY = 'resources';

    /**
     * @var DataFileWriter
     */
    private $writer;

    private $rootDirectory = '';

    private $resourcesDirectory = '';

    private $schemasDirectory = '';

    private static $instance;

    public static function getInstance(?string $rootDirectory = null): DataFileResolver
    {
        if (!static::$instance && !$rootDirectory) {
            throw new \RuntimeException('DataFileResolver must be fetched once with a root directory argument');
        }
        if (!static::$instance || $rootDirectory) {
            static::$instance = new static($rootDirectory);
        }
        return static::$instance;
    }

    public function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
        $this->writer = new DataFileWriter($this);
    }

    public function getWriter(): DataFileWriter
    {
        return $this->writer;
    }
    public function readSchemaMetaDataFile(Schema $schema): array
    {
        return $this->readVendorMetaDataFile($schema->getVendor())
            + $this->readPackageMetaDataFile($schema->getPackage())
            + (array) json_decode($this->readSchemaDataFile($schema, 'metadata.json'), true);
    }

    public function readPackageMetaDataFile(SchemaPackage $package): array
    {
        return $this->readVendorMetaDataFile($package->getVendor())
            + (array) json_decode($this->readPackageDataFile($package, 'metadata.json'), true);
    }

    public function readVendorMetaDataFile(SchemaVendor $vendor): array
    {
        return (array) json_decode($this->readVendorDataFile($vendor, 'metadata.json'), true);
    }

    public function readRootDataFile(string $relativeFilePath): string
    {
        $filePath = $this->getSchemaDirectoryPath() . $relativeFilePath;
        return is_file($filePath) ? file_get_contents($filePath) : '';
    }

    public function readVendorDataFile(SchemaVendor $vendor, string $relativeFilePath): string
    {
        $filePath = $this->getSchemaDirectoryPath() . $vendor->getVendorName() . DIRECTORY_SEPARATOR . $relativeFilePath;
        return is_file($filePath) ? file_get_contents($filePath) : '';
    }

    public function readPackageDataFile(SchemaPackage $package, string $relativeFilePath): string
    {
        $filePath = $this->getSchemaDirectoryPath() . $package->getVendor()->getVendorName() . DIRECTORY_SEPARATOR
            . $package->getPackageName() . DIRECTORY_SEPARATOR . $relativeFilePath;
        return is_file($filePath) ? file_get_contents($filePath) : '';
    }

    public function readSchemaDataFile(Schema $schema, string $relativeFilePath): string
    {
        $filePath = $this->getSchemaDirectoryPath() . $schema->getVendor()->getVendorName() . DIRECTORY_SEPARATOR
            . $schema->getPackage()->getPackageName() . DIRECTORY_SEPARATOR . $schema->getVersion()->getVersion()
            . DIRECTORY_SEPARATOR . $relativeFilePath;
        return is_file($filePath) ? file_get_contents($filePath) : '';
    }

    /**
     * @return SchemaVendor[]
     */
    public function resolveInstalledVendors(): array
    {
        $cache = [];
        if (empty($cache)) {
            $path = $this->getSchemaDirectoryPath();
            $cache = array_map(
                function ($item)
                {
                    return new SchemaVendor(pathinfo($item, PATHINFO_FILENAME));
                },
                $this->readContentsOfFolder($path)
            );
        }
        return $cache;
    }

    /**
     * @param SchemaVendor $vendor
     * @return SchemaPackage[]
     */
    public function resolveInstalledPackagesForVendor(SchemaVendor $vendor): array
    {
        static $cache = [];
        if (!isset($cache[$vendor->getVendorName()])) {
            $path = $this->getSchemaDirectoryPath() . DIRECTORY_SEPARATOR . $vendor->getVendorName() . DIRECTORY_SEPARATOR;
            $cache[$vendor->getVendorName()] = array_map(
                function ($item) use ($vendor)
                {
                    return new SchemaPackage($vendor, $item);
                },
                $this->readContentsOfFolder($path)
            );
        }
        return $cache[$vendor->getVendorName()];
    }

    /**
     * @param SchemaPackage $package
     * @return SchemaVersion[]
     */
    public function resolveInstalledVersionsForPackage(SchemaPackage $package): array
    {
        static $cache = [];
        if (!isset($cache[$package->getFullyQualifiedName()])) {
            $path = $this->getSchemaDirectoryPath() . DIRECTORY_SEPARATOR . $package->getVendor()->getVendorName()
                . DIRECTORY_SEPARATOR . $package->getPackageName() . DIRECTORY_SEPARATOR;
            $cache[$package->getFullyQualifiedName()] = array_map(
                function ($item) use ($package)
                {
                    return new SchemaVersion($package, $item);
                },
                $this->readContentsOfFolder($path)
            );
        }
        return $cache[$package->getFullyQualifiedName()];
    }

    public function resolveSchemaFileLocation(Schema $schema): string
    {
        $path = $this->getSchemaDirectoryPath() . $this->createSchemaSpecificSubPath($schema) . static::SCHEMA_FILENAME;
        if (!file_exists($path)) {
            throw new SchemaFileNotFoundException('File ' . $path . ' does not exist');
        }
        return $path;
    }

    public function resolveSchemaSpecificPublicDataFilePath(ProcessedSchema $schema, string $subPath): string
    {
        return $this->getPublicDirectoryPath() . $this->createSchemaSpecificSubPath($schema->getSchema()) . $subPath;
    }

    private function createSchemaSpecificSubPath(Schema $schema): string
    {
        $vendor = $schema->getVendor()->getVendorName();
        $package = $schema->getPackage()->getPackageName();
        $version = $schema->getVersion()->getVersion();
        return $vendor . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR;
    }

    public function setResourcesDirectory(string $resourcesDirectory): void
    {
        $this->resourcesDirectory = $resourcesDirectory;
    }

    public function setSchemasDirectory(string $schemasDirectory): void
    {
        $this->schemasDirectory = $schemasDirectory;
    }

    private function readContentsOfFolder(string $folderPath): array
    {
        $files = @scandir($folderPath);
        return empty($files) ? [] : array_values(
            array_filter(
                $files,
                function ($item) use ($folderPath)
                {
                    return $item{0} !== '.' && is_dir($folderPath . $item);
                }
            )
        );
    }

    public function getResourcesDirectoryPath(): string
    {
        return $this->resourcesDirectory ?: $this->rootDirectory . DIRECTORY_SEPARATOR . static::RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR;
    }

    public function getPublicDirectoryPath(): string
    {
//		return '/home/maddy/Documents/fluid/viewhelper_docu/Documentation/';
        return $this->rootDirectory . DIRECTORY_SEPARATOR . static::PUBLIC_DIRECTORY . DIRECTORY_SEPARATOR;
    }

    public function getSchemaDirectoryPath(): string
    {
        return $this->schemasDirectory ?: $this->rootDirectory . DIRECTORY_SEPARATOR . static::SCHEMAS_DIRECTORY . DIRECTORY_SEPARATOR;
    }

    public function getCacheDirectoryPath(): string
    {
        return $this->rootDirectory . DIRECTORY_SEPARATOR . static::CACHE_DIRECTORY . DIRECTORY_SEPARATOR;
    }
}
