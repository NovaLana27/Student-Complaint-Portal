<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'full_name' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'matric_no' => 'Lorem ipsum dolor sit amet',
                'level' => 1,
                'department_id' => 1,
                'unit_id' => 1,
                'password_hash' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ip',
                'is_active' => 1,
                'created' => '2026-11-14 23:11:10',
                'modified' => '2026-11-14 23:11:10',
            ],
        ];
        parent::init();
    }
}
