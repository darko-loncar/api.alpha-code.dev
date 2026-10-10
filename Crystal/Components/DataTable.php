<?php
declare(strict_types=1);
namespace Crystal\Components;

final class DataTable
{
    private array $rows;
    private array $columns = [];

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function addColumn(string $field, string $label): self
    {
        $this->columns[$field] = new Column($field, $label);

        return $this;
    }

    public function setColumnCallable(
        string $field,
        ?callable $callable
    ): self {
        $this->getColumn($field)->setCallable($callable);

        return $this;
    }

    public function getColumn(string $field): DataTableColumn
    {
        if (!isset($this->columns[$field])) {
            throw new InvalidArgumentException(
                sprintf('Column "%s" does not exist.', $field)
            );
        }

        return $this->columns[$field];
    }

    public function getColumns(): array
    {
        return array_values($this->columns);
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
