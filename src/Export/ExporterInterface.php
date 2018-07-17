<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator\Export;

use NamelessCoder\FluidDocumentationGenerator\ProcessedSchema;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaPackage;
use NamelessCoder\FluidDocumentationGenerator\Entity\SchemaVendor;
use NamelessCoder\FluidDocumentationGenerator\SchemaDocumentationGenerator;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentation;
use NamelessCoder\FluidDocumentationGenerator\ViewHelperDocumentationGroup;

interface ExporterInterface
{
    public function getIdentifier(): string;
    public function setGenerator(SchemaDocumentationGenerator $generator): void;
    public function exportRoot(): void;
    public function exportVendor(SchemaVendor $vendor): void;
    public function exportPackage(SchemaPackage $package): void;
    public function exportSchema(ProcessedSchema $processedSchema, bool $forceUpdate = false): void;
    public function exportViewHelper(ViewHelperDocumentation $viewHelperDocumentation, bool $forceUpdate = false): void;
    public function exportViewHelperGroup(ViewHelperDocumentationGroup $viewHelperDocumentationGroup, bool $forceUpdate = false): void;
    public function createAdditionalViewHelperResources(ViewHelperDocumentation $viewHelperDocumentation, ?string $label = null): array;
    public function createAdditionalSchemaResources(ProcessedSchema $schema, ?string $label = null): array;
}
