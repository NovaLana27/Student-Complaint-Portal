<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AuditLogsFixture
 */
class AuditLogsFixture extends TestFixture
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
                'action' => 'Lorem ipsum dolor sit amet',
                'entity' => 'Lorem ipsum dolor sit amet',
                'entity_id' => 1,
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-11-14 23:11:08',
            ],
        ];
        parent::init();
    }
}
