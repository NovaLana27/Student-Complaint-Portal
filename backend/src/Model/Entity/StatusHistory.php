<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * StatusHistory Entity
 *
 * @property int $id
 * @property int $complaint_id
 * @property string|null $old_status
 * @property string $new_status
 * @property int|null $changed_by
 * @property string|null $note
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\Complaint $complaint
 */
class StatusHistory extends Entity
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
        'complaint_id' => true,
        'old_status' => true,
        'new_status' => true,
        'changed_by' => true,
        'note' => true,
        'created' => true,
        'complaint' => true,
    ];
}
