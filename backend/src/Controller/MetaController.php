<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class MetaController extends ApiController
{
    protected array $publicActions = ['options'];

    /** GET /api/meta/options  (fills the dropdowns on the forms) */
    public function options(): Response
    {
        $departments = $this->fetchTable('Departments')->find()
            ->select(['id', 'name'])->orderBy(['name' => 'ASC'])->enableHydration(false)->all()->toList();
        $categories = $this->fetchTable('Categories')->find()
            ->select(['id', 'name'])->orderBy(['name' => 'ASC'])->enableHydration(false)->all()->toList();

        return $this->json([
            'departments' => $departments,
            'levels' => [100, 200, 300, 400, 500],
            'categories' => $categories,
            'statuses' => ['Submitted', 'Under Review', 'In Progress', 'Resolved', 'Closed', 'Rejected', 'Reopened'],
        ]);
    }
}