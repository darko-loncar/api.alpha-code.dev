<?php
declare(strict_types=1);
namespace Components\Query;

interface iStatement
{
    public function getQuery(): string;
}
