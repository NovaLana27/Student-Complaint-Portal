<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AuditLog Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $entity
 * @property int|null $entity_id
 * @property string|null $ip_address
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\User $user
 */
class AuditLog extends Entity
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
        'user_id' => true,
        'action' => true,
        'entity' => true,
        'entity_id' => true,
        'ip_address' => true,
        'created' => true,
        'user' => true,
    ];
}
