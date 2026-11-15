<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ComplaintsFixture
 */
class ComplaintsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'reference_no' => 'Lorem ipsum dolor sit amet',
                'student_id' => 1,
                'category_id' => 1,
                'assigned_to' => 1,
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'priority' => 'Lorem ip',
                'status' => 'Lorem ipsum dolor ',
                'is_anonymous' => 1,
                'resolved_at' => '2026-11-14 23:11:09',
                'created' => '2026-11-14 23:11:09',
                'modified' => '2026-11-14 23:11:09',
            ],
        ];
        parent::init();
    }
}
