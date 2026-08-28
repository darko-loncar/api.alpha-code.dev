<?php
namespace App\Entities;

class Outfit extends \Components\Entity
{
    public string $name = "";
    public string $description = "";
    public int $fk_outfit_hair = 0;
    public int $fk_outfit_face = 0;
    public int $fk_outfit_upper = 0;
    public int $fk_outfit_bra = 0;
    public int $fk_outfit_mid = 0;
    public int $fk_outfit_legs = 0;
    public int $fk_outfit_between_legs = 0;
    public int $fk_outfit_footwear = 0;
    public int $heel_height = 10;
    public int $skirt_length = 20;
    public bool $skirt_slit = false;
    public int $glasses = 0;
    public int $calculated_value = 0;
    readonly public \DateTime $created_at;
    readonly public ?\DateTime $updated_at;
}
