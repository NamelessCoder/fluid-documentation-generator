<?php
declare(strict_types=1);

namespace NamelessCoder\FluidDocumentationGenerator;

use NamelessCoder\FluidDocumentationGenerator\Data\DataFileResolver;
use NamelessCoder\FluidDocumentationGenerator\Entity\Schema;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class ProcessedSchema
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var ViewHelperDocumentationGroup
     */
    private $viewHelperDocumentationRootGroup;

    /**
     * @var ViewHelperDocumentation[]
     */
    private $viewHelpersDocumentations = [];

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->viewHelperDocumentationRootGroup = new ViewHelperDocumentationGroup($this);
        $this->processXmlFile();
    }

    public function getPath(): string
    {
        return implode(
            '/',
            [
                $this->schema->getVendor()->getVendorName(),
                $this->schema->getPackage()->getPackageName(),
                $this->schema->getVersion()->getVersion()
            ]
        ) . '/';
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getDocumentationTree(): ViewHelperDocumentationGroup
    {
        return $this->viewHelperDocumentationRootGroup;
    }

    /**
     * @return ViewHelperDocumentation[]
     */
    public function getDocumentedViewHelpers(): array
    {
        return $this->viewHelpersDocumentations;
    }

    private function processXmlFile(): array
    {
        $json = [];
        $xmlFilePath = DataFileResolver::getInstance()->resolveSchemaFileLocation($this->schema);
        $xmlDocument = simplexml_load_file($xmlFilePath);
        foreach ($xmlDocument->xpath('/xsd:schema/xsd:element') as $element) {
            $name = (string)$element->attributes()['name'];
            $group = $this->findOrCreateViewHelperDocumentationGroupByViewHelperName($name);
            $this->viewHelpersDocumentations[$name] = new ViewHelperDocumentation(
                $group->getSchema(),
                $name,
                (string)$element->xpath('xsd:annotation/xsd:documentation')[0],
                $this->extractArgumentDefinitions($element->xpath('xsd:complexType/xsd:attribute')),
                $group
            );
        }
        return $json;
    }

    private function findOrCreateViewHelperDocumentationGroupByViewHelperName(string $name): ViewHelperDocumentationGroup
    {
        if (strpos($name, '.') === false) {
            return $this->viewHelperDocumentationRootGroup;
        }
        return $this->findOrCreateViewHelperDocumentationGroupByGroupPath(substr($name, 0, strrpos($name, '.')));
    }

    private function findOrCreateViewHelperDocumentationGroupByGroupPath(string $path): ViewHelperDocumentationGroup
    {
        $parts = explode('.', $path);
        $group = $root = $this->viewHelperDocumentationRootGroup;
        $path = '';
        foreach ($parts as $part) {
            $subGroup = $group->getSubGroupByPath($part);
            $path .= ucfirst($part);
            if (!$subGroup) {
                $subGroup = new ViewHelperDocumentationGroup($this, $part, $path);
                $group->addSubGroup($subGroup);
            }
            $group = $subGroup;
            $path .= '/';
        }
        return $group;
    }

    /**
     * @param array|\SimpleXMLElement[] $elements
     * @return ArgumentDefinition[]
     */
    private function extractArgumentDefinitions(array $elements): array
    {
        $argumentDefinitions = [];
        foreach ($elements as $element) {
            $attributes = $element->attributes();
            $name = (string)$attributes['name'];
            $argumentDefinitions[$name] = new ArgumentDefinition(
                $name,
                substr((string)$attributes['type'], 4),
                (string)$element->xpath('xsd:annotation/xsd:documentation')[0],
                ($attributes['use'] ?? false) === 'required',
                ((string)$attributes['default'])
            );
        }
        return $argumentDefinitions;
    }
}
