<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;

class ReportsController extends ApiController
{
    /** GET /api/reports/summary  (admin: everything, officer: only their unit) */
    public function summary(): Response
    {
        if ($denied = $this->requireRole(['admin', 'officer'])) {
            return $denied;
        }
        $conn = ConnectionManager::get('default');
        [$where, $params] = $this->scope();
        $from = 'FROM complaints cp JOIN categories c ON c.id = cp.category_id JOIN units u ON u.id = c.unit_id ' . $where;

        return $this->json([
            'total' => (int)$conn->execute('SELECT COUNT(*) AS n ' . $from, $params)->fetch('assoc')['n'],
            'by_status' => $conn->execute('SELECT cp.status, COUNT(*) AS total ' . $from . ' GROUP BY cp.status', $params)->fetchAll('assoc'),
            'by_category' => $conn->execute('SELECT c.name AS category, COUNT(*) AS total ' . $from . ' GROUP BY c.id, c.name ORDER BY total DESC', $params)->fetchAll('assoc'),
            'by_priority' => $conn->execute('SELECT cp.priority, COUNT(*) AS total ' . $from . ' GROUP BY cp.priority', $params)->fetchAll('assoc'),
            'by_unit' => $conn->execute('SELECT u.name AS unit, COUNT(*) AS total ' . $from . ' GROUP BY u.id, u.name ORDER BY total DESC', $params)->fetchAll('assoc'),
        ]);
    }

    /** GET /api/reports/resolution-time  (admin) average hours to resolve, per unit */
    public function resolutionTime(): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $rows = ConnectionManager::get('default')->execute(
            'SELECT u.name AS unit, COUNT(*) AS resolved,
                    ROUND(AVG(TIMESTAMPDIFF(HOUR, cp.created, cp.resolved_at)), 1) AS avg_hours
             FROM complaints cp
             JOIN categories c ON c.id = cp.category_id
             JOIN units u ON u.id = c.unit_id
             WHERE cp.resolved_at IS NOT NULL
             GROUP BY u.id, u.name ORDER BY avg_hours ASC'
        )->fetchAll('assoc');

        return $this->json(['data' => $rows]);
    }

    /** GET /api/reports/export  (admin) CSV download of all complaints */
    public function export(): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $rows = ConnectionManager::get('default')->execute(
            'SELECT cp.reference_no AS complaint_id, c.name AS category, u.name AS unit, cp.priority, cp.status,
                    cp.created AS submitted_on, cp.resolved_at
             FROM complaints cp
             JOIN categories c ON c.id = cp.category_id
             JOIN units u ON u.id = c.unit_id
             ORDER BY cp.created DESC'
        )->fetchAll('assoc');

        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['complaint_id', 'category', 'unit', 'priority', 'status', 'submitted_on', 'resolved_at'], ',', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($out, array_values($row), ',', '"', '\\');
        }
        rewind($out);
        $csv = (string)stream_get_contents($out);
        fclose($out);

        return $this->response
            ->withType('csv')
            ->withDownload('complaints.csv')
            ->withStringBody($csv);
    }

    /** Officers only see their own unit's numbers. */
    private function scope(): array
    {
        if ($this->authUser['role'] === 'officer') {
            return ['WHERE c.unit_id = ?', [(int)($this->authUser['unit_id'] ?? 0)]];
        }

        return ['', []];
    }
}