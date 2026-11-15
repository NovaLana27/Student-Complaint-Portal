<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property string|null $matric_no
 * @property int|null $level
 * @property int|null $department_id
 * @property int|null $unit_id
 * @property string $password_hash
 * @property string $role
 * @property bool $is_active
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Unit $unit
 * @property \App\Model\Entity\AuditLog[] $audit_logs
 * @property \App\Model\Entity\Notification[] $notifications
 * @property \App\Model\Entity\Response[] $responses
 */
class User extends Entity
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
        'full_name' => true,
        'email' => true,
        'matric_no' => true,
        'level' => true,
        'department_id' => true,
        'unit_id' => true,
        'password_hash' => true,
        'role' => true,
        'is_active' => true,
        'created' => true,
        'modified' => true,
        'department' => true,
        'unit' => true,
        'audit_logs' => true,
        'notifications' => true,
        'responses' => true,
    ];
}
