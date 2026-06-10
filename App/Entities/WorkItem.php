<?php
namespace App\Entities;

class WorkItem extends \Core\Entity  
{
    public string $name;
    public string $description;
    public string $unit;
    public int $value;
}
