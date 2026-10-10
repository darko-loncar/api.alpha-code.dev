<?php
declare(strict_types=1);
namespace Crystal\Components;

final class Column
{
    private string $field;
    private string $label;
    private $callable;

    public function __construct(
        string $field,
        string $label,
        ?callable $callable = null
    ) {
        $this->field = $field;
        $this->label = $label;
        $this->callable = $callable;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCallable()
    {
        return $this->callable;
    }
}
