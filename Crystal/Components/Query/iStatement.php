<?php
declare(strict_types=1);
namespace Crystal\Components\Query;

interface iStatement
{
    public function getQuery(): string;
}
