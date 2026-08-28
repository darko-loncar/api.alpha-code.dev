<?php
declare(strict_types=1);
namespace Components;

use Components\QueryBuilder;

class EntitySelect {

    private string $nameAttr;
    private ?int $selectedOptionId;
    private mixed $optionColumn;
    private array $entities;
    private bool $allowNull = false;
    private string $id = "";

    function __construct(string $nameAttr, string $entityClass, string|callable $optionColumn, ?int $selectedOptionId = null)
    {
        $this->nameAttr = $nameAttr;
        $this->optionColumn = $optionColumn;
        $this->entities = (new QueryBuilder($entityClass::getTableName()))->get();
        $this->selectedOptionId = $selectedOptionId;
    }

    /**
     * Dropdown will have option for "not selected"
     */
    public function setAllowNull() : EntitySelect
    {
        $this->allowNull = true;
        return $this;
    }

    /**
     * Sets Id attribute of select element
     */
    public function setId(string $id) : EntitySelect
    {
        $this->id = $id;
        return $this;
    }

    public function getHtml() : string 
    {
        // Resolve attributes
        $nameAttr = $this->nameAttr;
        $idAttr = ($this->id === "") ? $nameAttr : $this->id;

        // Open HTML select 
        $htmlParts = ["<select id='$idAttr' name='$nameAttr'>"];

        // Add the null option if applicable
        if ($this->allowNull === true) {
            $htmlParts[] = "<option value=''>-- not selected --</option>";
        }

        /* Loops through all Entities in Collection */
        $entities = $this->entities;
        foreach($entities as $entityItem) {
            
            /* Build data attributes on options  */
            $dataAttributes = [];
            foreach($entityItem as $prop => $value){
                if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $dataAttributes[] = "data-$prop='$value' ";
            }
            $dataAttributesString = implode(' ', $dataAttributes); // Join data attributes as string

            // If option text column was string use it as option text. If not, apply callback
            $optionText = (is_callable($this->optionColumn))
                ? ($this->optionColumn)($entityItem)
                : $entityItem->{$this->optionColumn};    

            $optionValue = $entityItem['id'];
            $selectedAttribute = ($optionValue === $this->selectedOptionId) ? ' selected' : '';

            // Append the option tag to the parts array
            $htmlParts[] = "<option$selectedAttribute $dataAttributesString value='$optionValue'>$optionText</option>";
        }

        // Close HTML Select
        $htmlParts[] = "</select>";
        return implode('', $htmlParts);  // Join all parts at once for better performance
    }  
}