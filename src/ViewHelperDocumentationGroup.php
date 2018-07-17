<?php
declare(strict_types=1);
namespace NamelessCoder\FluidDocumentationGenerator;

class ViewHelperDocumentationGroup
{
    /**
     * @var ProcessedSchema
     */
    private $schema;

    /**
     * @var string
     */
    private $groupId = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var ViewHelperDocumentation[]
     */
    private $documentedViewHelpers = [];

    /**
     * @var self[]
     */
    private $subGroups = [];

    public function __construct(ProcessedSchema $schema, string $groupId = '', string $path = '')
    {
        $this->schema = $schema;
        $this->groupId = $groupId;
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSchema(): ProcessedSchema
    {
        return $this->schema;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getName(): string
    {
        return ucfirst($this->groupId);
    }

    public function addSubGroup(self $group): void
    {
        $this->subGroups[$group->getGroupId()] = $group;
    }

    public function getSubGroupByPath(string $path): ?self
    {
        return $this->subGroups[$path] ?? null;
    }

    public function getSubGroups(): array
    {
        return $this->subGroups;
    }

    public function addDocumentedViewHelper(ViewHelperDocumentation $viewHelperDocumentation): void
    {
        $this->documentedViewHelpers[$viewHelperDocumentation->getName()] = $viewHelperDocumentation;
    }

    /**
     * @return ViewHelperDocumentation[]
     */
    public function getDocumentedViewHelpers(): array
    {
        return $this->documentedViewHelpers;
    }
}
