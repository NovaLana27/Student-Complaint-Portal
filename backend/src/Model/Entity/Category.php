<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $name
 * @property int $unit_id
 * @property string $default_priority
 *
 * @property \App\Model\Entity\Unit $unit
 * @property \App\Model\Entity\Complaint[] $complaints
 */
class Category extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'unit_id' => true,
        'default_priority' => true,
        'unit' => true,
        'complaints' => true,
    ];
}
