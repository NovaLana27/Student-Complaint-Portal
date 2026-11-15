<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotificationsFixture
 */
class NotificationsFixture extends TestFixture
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
                'user_id' => 1,
                'complaint_id' => 1,
                'channel' => 'Lorem ip',
                'message' => 'Lorem ipsum dolor sit amet',
                'is_read' => 1,
                'created' => '2026-11-14 23:11:09',
            ],
        ];
        parent::init();
    }
}
