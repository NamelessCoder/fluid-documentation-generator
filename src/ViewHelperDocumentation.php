<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator;

use cebe\markdown\GithubMarkdown;
use Michelf\Markdown;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class ViewHelperDocumentation
{
    /**
     * @var ProcessedSchema
     */
    private $schema;

    /**
     * @var ViewHelperDocumentationGroup
     */
    private $group;

    /**
     * @var ArgumentDefinition[]
     */
    private $argumentDefinitions = [];

    private $viewHelperName = '';
    private $description = '';

    public function __construct(
        ProcessedSchema $schema,
        string $viewHelperName,
        string $description,
        array $argumentDefinitions,
        ViewHelperDocumentationGroup $group
    ) {
        $this->schema = $schema;
        $this->viewHelperName = $viewHelperName;
        $this->description = trim($description, "\n\r\t/");
        $this->argumentDefinitions = $argumentDefinitions;
        $this->group = $group;
        $this->group->addDocumentedViewHelper($this);
    }

    public function getGroup(): ViewHelperDocumentationGroup
    {
        return $this->group;
    }

    public function getName(): string
    {
        return $this->viewHelperName;
    }

    public function getSchema(): ProcessedSchema
    {
        return $this->schema;
    }

    public function getPath(): string
    {
        return implode('/', array_map('ucfirst', explode('.', $this->viewHelperName)));
    }

    public function getPhpName(): string
    {
        return str_replace('/', '\\', $this->getPath()) . 'ViewHelper';
    }

    public function getLocalName(): string
    {
        $lastDotPosition = strrpos($this->viewHelperName, '.');
        return ucfirst($lastDotPosition ? substr($this->viewHelperName, $lastDotPosition + 1) : $this->viewHelperName);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPathToSchemaRoot(): string
    {
        return str_repeat('../', substr_count($this->viewHelperName, '.'));
    }

    public function getDescriptionAsMarkup(): string
    {
        $description = $this->getDescription();
        return empty(trim((string)$description)) ? '' : (new GithubMarkdown())->parse($description);
    }

    /**
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitions(): array
    {
        return $this->argumentDefinitions;
    }
}
