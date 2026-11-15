<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Notification Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $complaint_id
 * @property string $channel
 * @property string $message
 * @property bool $is_read
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Complaint $complaint
 */
class Notification extends Entity
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
        'complaint_id' => true,
        'channel' => true,
        'message' => true,
        'is_read' => true,
        'created' => true,
        'user' => true,
        'complaint' => true,
    ];
}
