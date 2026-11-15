<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Complaint Entity
 *
 * @property int $id
 * @property string|null $reference_no
 * @property int $student_id
 * @property int $category_id
 * @property int|null $assigned_to
 * @property string $description
 * @property string $priority
 * @property string $status
 * @property bool $is_anonymous
 * @property \Cake\I18n\DateTime|null $resolved_at
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Student $student
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Attachment[] $attachments
 * @property \App\Model\Entity\Notification[] $notifications
 * @property \App\Model\Entity\Response[] $responses
 * @property \App\Model\Entity\StatusHistory[] $status_histories
 */
class Complaint extends Entity
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
        'reference_no' => true,
        'student_id' => true,
        'category_id' => true,
        'assigned_to' => true,
        'description' => true,
        'priority' => true,
        'status' => true,
        'is_anonymous' => true,
        'resolved_at' => true,
        'created' => true,
        'modified' => true,
        'student' => true,
        'category' => true,
        'attachments' => true,
        'notifications' => true,
        'responses' => true,
        'status_histories' => true,
    ];
}
