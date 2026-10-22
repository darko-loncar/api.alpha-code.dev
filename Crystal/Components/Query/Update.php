<?php
declare(strict_types=1);
namespace Crystal\Components\Query;

/**
 * Update statement.
 */
final class Update extends Statement
{
    /** @var array<string, mixed> */
    private array $setValues = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function set(string $field, mixed $value): static
    {
        $this->setValues[$field] = $value;
        return $this;
    }

    public function getQuery(): string
    {
        if ($this->setValues === []) {
            throw new \LogicException('Update statement requires at least one value.');
        }

        $setFragments = [];
        foreach ($this->setValues as $field => $value) {
            $setFragments[] = "`{$field}` = ?";
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setFragments);

        if ($this->where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }

    public function getBindings(): array
    {
        return array_merge(array_values($this->setValues), parent::getBindings());
    }
}
