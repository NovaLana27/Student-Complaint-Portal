<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\StatusHistoriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\StatusHistoriesTable Test Case
 */
class StatusHistoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\StatusHistoriesTable
     */
    protected $StatusHistories;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.StatusHistories',
        'app.Complaints',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('StatusHistories') ? [] : ['className' => StatusHistoriesTable::class];
        $this->StatusHistories = $this->getTableLocator()->get('StatusHistories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->StatusHistories);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\StatusHistoriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\StatusHistoriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
