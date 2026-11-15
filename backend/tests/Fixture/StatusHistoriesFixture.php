<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StatusHistoriesFixture
 */
class StatusHistoriesFixture extends TestFixture
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
                'complaint_id' => 1,
                'old_status' => 'Lorem ipsum dolor ',
                'new_status' => 'Lorem ipsum dolor ',
                'changed_by' => 1,
                'note' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-11-14 23:11:09',
            ],
        ];
        parent::init();
    }
}
