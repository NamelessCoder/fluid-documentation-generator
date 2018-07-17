<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator\Data;

use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;

class DataFileWriter
{
    /**
     * @var DataFileResolver
     */
    private $resolver;

    public function __construct(DataFileResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function publishDataFileForSchema(ProcessedSchema $schema, string $subPath, $contents): string
    {
        return $this->writeDataFileToTargetLocation(
            $this->resolver->resolveSchemaSpecificPublicDataFilePath($schema, $subPath),
            $contents
        );
    }

    public function publishDataFile(string $subPath, $contents): string
    {
        return $this->writeDataFileToTargetLocation(
            $this->resolver->getPublicDirectoryPath() . $subPath,
            $contents
        );
    }

    public function publishDataFileForVendor(SchemaVendor $vendor, string $subPath, $contents): string
    {
        return $this->writeDataFileToTargetLocation(
            $this->resolver->getPublicDirectoryPath() . $vendor->getVendorName() . DIRECTORY_SEPARATOR . $subPath,
            $contents
        );
    }

    public function publishDataFileForPackage(SchemaPackage $package, string $subPath, $contents): string
    {
        return $this->writeDataFileToTargetLocation(
            $this->resolver->getPublicDirectoryPath() . $package->getVendor()->getVendorName() . DIRECTORY_SEPARATOR . $package->getPackageName() . DIRECTORY_SEPARATOR . $subPath,
            $contents
        );
    }

    private function writeDataFileToTargetLocation(string $targetFileLocation, $contents): string
    {
        $directoryPath = pathinfo($targetFileLocation, PATHINFO_DIRNAME);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        if (is_array($contents)) {
            $contents = json_encode($contents, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES);
        }
        file_put_contents($targetFileLocation, $contents);
        return $targetFileLocation;
    }
}
