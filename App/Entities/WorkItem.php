<?php
namespace App\Entities;

class WorkItem extends \Components\Entity
{
    public string $name;
    public string $description;
    public string $unit;
    public int $value;
}
