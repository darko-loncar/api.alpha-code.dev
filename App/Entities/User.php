<?php
namespace App\Entities;

class User extends \Core\Entity
{
    public string $first_name = "";
    public string $last_name = "";
    public string $nickname = "";
    public string $email = "";
    public string $phone = "";
    public string $fk_profile_image = "";
    readonly public \DateTime $created_at;
    readonly public ?\DateTime $updated_at;
}
