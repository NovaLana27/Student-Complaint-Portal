<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Routing\Router;

class ComplaintsController extends ApiController
{
    protected array $publicActions = ['track'];

    private const STATUSES = ['Submitted', 'Under Review', 'In Progress', 'Resolved', 'Closed', 'Rejected', 'Reopened'];

    /** POST /api/complaints  (students submit a complaint, optional file field: "evidence") */
    public function add(): Response
    {
        if ($denied = $this->requireRole(['student'])) {
            return $denied;
        }

        $data = $this->request->getData();
        $errors = [];

        $category = null;
        if (empty($data['category_id'])) {
            $errors['category_id'] = 'Choose a category';
        } else {
            $category = $this->fetchTable('Categories')->find()
                ->where(['id' => (int)$data['category_id']])->enableHydration(false)->first();
            if (!$category) {
                $errors['category_id'] = 'Choose a valid category';
            }
        }
        $description = trim((string)($data['description'] ?? ''));
        if (strlen($description) < 10) {
            $errors['description'] = 'Describe your complaint in at least 10 characters';
        }
        if ($errors) {
            return $this->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        }

        $priority = in_array($data['priority'] ?? '', ['low', 'medium', 'high'], true)
            ? $data['priority'] : $category['default_priority'];

        // Auto-route: give it to the first active officer of the responsible unit (if any)
        $officer = $this->fetchTable('Users')->find()
            ->where(['role' => 'officer', 'unit_id' => $category['unit_id'], 'is_active' => 1])
            ->orderBy(['id' => 'ASC'])->enableHydration(false)->first();

        $complaints = $this->fetchTable('Complaints');
        $complaint = $complaints->newEntity([
            'student_id' => (int)$this->authUser['id'],
            'category_id' => (int)$category['id'],
            'assigned_to' => $officer ? (int)$officer['id'] : null,
            'description' => $description,
            'priority' => $priority,
            'status' => 'Submitted',
            'is_anonymous' => !empty($data['is_anonymous']) && $data['is_anonymous'] !== 'false' ? 1 : 0,
        ], ['validate' => false]);

        if (!$complaints->save($complaint)) {
            return $this->json(['message' => 'Could not save complaint', 'errors' => $complaint->getErrors()], 500);
        }

        // Unique, readable Complaint ID, e.g. RUN-CMP-2026-00042
        $complaint->reference_no = 'RUN-CMP-' . date('Y') . '-' . str_pad((string)$complaint->id, 5, '0', STR_PAD_LEFT);
        $complaints->save($complaint);

        $this->logStatus((int)$complaint->id, null, 'Submitted', (int)$this->authUser['id'], 'Complaint submitted');
        $warning = $this->saveEvidence((int)$complaint->id);
        $this->audit('complaint_submitted', 'complaints', (int)$complaint->id);

        $this->notify(
            (int)$this->authUser['id'],
            (int)$complaint->id,
            'Complaint received: ' . $complaint->reference_no,
            'Your complaint has been received. Your Complaint ID is ' . $complaint->reference_no . '. Use it to track progress.'
        );
        if ($officer) {
            $this->notify(
                (int)$officer['id'],
                (int)$complaint->id,
                'New complaint assigned: ' . $complaint->reference_no,
                'A new complaint (' . $complaint->reference_no . ') has been assigned to you.'
            );
        }

        $response = [
            'complaint_id' => $complaint->reference_no,
            'status' => 'Submitted',
            'created' => $complaint->created,
        ];
        if ($warning) {
            $response['warning'] = $warning;
        }

        return $this->json($response, 201);
    }

    /** GET /api/complaints?status=&category_id=&page=&limit=  (what you see depends on your role) */
    public function index(): Response
    {
        $complaints = $this->fetchTable('Complaints');
        $query = $complaints->find()
            ->select([
                'Complaints.id', 'Complaints.reference_no', 'Complaints.status', 'Complaints.priority',
                'Complaints.created', 'Complaints.modified',
                'category' => 'c.name',
            ])
            ->join([
                'c' => ['table' => 'categories', 'type' => 'INNER', 'conditions' => 'c.id = Complaints.category_id'],
            ])
            ->orderBy(['Complaints.created' => 'DESC'])
            ->enableHydration(false);

        $role = $this->authUser['role'];
        if ($role === 'student') {
            $query->where(['Complaints.student_id' => (int)$this->authUser['id']]);
        } elseif ($role === 'officer') {
            $query->where(['c.unit_id' => (int)($this->authUser['unit_id'] ?? 0)]);
        }

        $status = $this->request->getQuery('status');
        if ($status && in_array($status, self::STATUSES, true)) {
            $query->where(['Complaints.status' => $status]);
        }
        $categoryId = $this->request->getQuery('category_id');
        if ($categoryId) {
            $query->where(['Complaints.category_id' => (int)$categoryId]);
        }

        $page = max(1, (int)$this->request->getQuery('page', 1));
        $limit = min(100, max(1, (int)$this->request->getQuery('limit', 20)));
        $total = $query->count();
        $rows = $query->limit($limit)->offset(($page - 1) * $limit)->all()->toList();

        return $this->json(['data' => $rows, 'page' => $page, 'limit' => $limit, 'total' => $total]);
    }

    /** GET /api/complaints/track/{ref}  (PUBLIC: status by Complaint ID, limited details) */
    public function track(string $ref): Response
    {
        $ref = strtoupper(trim($ref));
        $complaint = $this->fetchTable('Complaints')->find()
            ->select([
                'Complaints.id', 'Complaints.reference_no', 'Complaints.status',
                'Complaints.created', 'Complaints.modified', 'category' => 'c.name',
            ])
            ->join(['c' => ['table' => 'categories', 'type' => 'INNER', 'conditions' => 'c.id = Complaints.category_id']])
            ->where(['Complaints.reference_no' => $ref])
            ->enableHydration(false)
            ->first();

        if (!$complaint) {
            return $this->json(['message' => 'No complaint found with that ID'], 404);
        }

        $timeline = $this->fetchTable('StatusHistories')->find()
            ->select(['new_status', 'created'])
            ->where(['complaint_id' => $complaint['id']])
            ->orderBy(['id' => 'ASC'])
            ->enableHydration(false)->all()->toList();

        return $this->json([
            'complaint_id' => $complaint['reference_no'],
            'status' => $complaint['status'],
            'category' => $complaint['category'],
            'submitted_on' => $complaint['created'],
            'last_updated' => $complaint['modified'],
            'timeline' => $timeline,
        ]);
    }

    /** GET /api/complaints/{id} */
    public function view(string $id): Response
    {
        $complaint = $this->loadComplaint((int)$id);
        if (!$complaint) {
            return $this->json(['message' => 'Complaint not found'], 404);
        }
        if (!$this->canAccess($complaint)) {
            return $this->json(['message' => 'You do not have access to this complaint'], 403);
        }

        $role = $this->authUser['role'];
        if ($complaint['is_anonymous'] && $role === 'officer') {
            $complaint['student_name'] = 'Anonymous';
            $complaint['student_matric'] = null;
        }

        $attachments = $this->fetchTable('Attachments')->find()
            ->where(['complaint_id' => (int)$id])->enableHydration(false)->all()->toList();
        foreach ($attachments as &$a) {
            $a['url'] = Router::url('/' . $a['file_path'], true);
        }
        unset($a);

        $responsesQuery = $this->fetchTable('Responses')->find()
            ->select(['Responses.id', 'Responses.message', 'Responses.is_internal', 'Responses.created',
                'author' => 'u.full_name', 'author_role' => 'u.role'])
            ->join(['u' => ['table' => 'users', 'type' => 'INNER', 'conditions' => 'u.id = Responses.user_id']])
            ->where(['Responses.complaint_id' => (int)$id])
            ->orderBy(['Responses.id' => 'ASC'])
            ->enableHydration(false);
        if ($role === 'student') {
            $responsesQuery->where(['Responses.is_internal' => 0]);
        }

        $history = $this->fetchTable('StatusHistories')->find()
            ->where(['complaint_id' => (int)$id])->orderBy(['id' => 'ASC'])->enableHydration(false)->all()->toList();

        return $this->json([
            'complaint' => $complaint,
            'attachments' => $attachments,
            'responses' => $responsesQuery->all()->toList(),
            'history' => $history,
        ]);
    }

    /** PATCH /api/complaints/{id}/status  (officer/admin) body: status, note */
    public function updateStatus(string $id): Response
    {
        if ($denied = $this->requireRole(['officer', 'admin'])) {
            return $denied;
        }
        $complaint = $this->loadComplaint((int)$id);
        if (!$complaint) {
            return $this->json(['message' => 'Complaint not found'], 404);
        }
        if (!$this->canAccess($complaint)) {
            return $this->json(['message' => 'You do not have access to this complaint'], 403);
        }

        $newStatus = (string)$this->request->getData('status');
        $note = trim((string)$this->request->getData('note', ''));
        if (!in_array($newStatus, self::STATUSES, true)) {
            return $this->json(['message' => 'Invalid status', 'allowed' => self::STATUSES], 422);
        }
        if ($newStatus === 'Rejected' && $note === '') {
            return $this->json(['message' => 'Give a reason when rejecting a complaint'], 422);
        }

        $old = $complaint['status'];
        $table = $this->fetchTable('Complaints');
        $entity = $table->get((int)$id);
        $entity->status = $newStatus;
        if ($newStatus === 'Resolved' || ($newStatus === 'Closed' && !$entity->resolved_at)) {
            $entity->resolved_at = DateTime::now();
        } elseif (!in_array($newStatus, ['Resolved', 'Closed'], true)) {
            $entity->resolved_at = null;
        }
        $table->save($entity);

        $this->logStatus((int)$id, $old, $newStatus, (int)$this->authUser['id'], $note ?: null);
        $this->audit('status_changed', 'complaints', (int)$id);
        $this->notify(
            (int)$complaint['student_id'],
            (int)$id,
            'Update on ' . $complaint['reference_no'],
            'Your complaint ' . $complaint['reference_no'] . ' is now: ' . $newStatus . ($note ? '. Note: ' . $note : '.')
        );

        return $this->json(['complaint_id' => $complaint['reference_no'], 'status' => $newStatus]);
    }

    /** PATCH /api/complaints/{id}/assign  (admin) body: officer_id */
    public function assign(string $id): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $complaint = $this->loadComplaint((int)$id);
        if (!$complaint) {
            return $this->json(['message' => 'Complaint not found'], 404);
        }

        $officer = $this->fetchTable('Users')->find()
            ->where(['id' => (int)$this->request->getData('officer_id'), 'role' => 'officer', 'is_active' => 1])
            ->enableHydration(false)->first();
        if (!$officer) {
            return $this->json(['message' => 'Choose a valid officer'], 422);
        }

        $table = $this->fetchTable('Complaints');
        $entity = $table->get((int)$id);
        $entity->assigned_to = (int)$officer['id'];
        $table->save($entity);

        $this->audit('complaint_assigned', 'complaints', (int)$id);
        $this->notify(
            (int)$officer['id'],
            (int)$id,
            'Complaint assigned: ' . $complaint['reference_no'],
            'Complaint ' . $complaint['reference_no'] . ' has been assigned to you.'
        );

        return $this->json(['message' => 'Assigned to ' . $officer['full_name']]);
    }

    /** POST /api/complaints/{id}/responses  body: message, is_internal (staff only) */
    public function respond(string $id): Response
    {
        $complaint = $this->loadComplaint((int)$id);
        if (!$complaint) {
            return $this->json(['message' => 'Complaint not found'], 404);
        }
        if (!$this->canAccess($complaint)) {
            return $this->json(['message' => 'You do not have access to this complaint'], 403);
        }

        $message = trim((string)$this->request->getData('message', ''));
        if (strlen($message) < 2) {
            return $this->json(['message' => 'Write a message'], 422);
        }

        $isStaff = in_array($this->authUser['role'], ['officer', 'admin'], true);
        $isInternal = $isStaff && filter_var($this->request->getData('is_internal', false), FILTER_VALIDATE_BOOLEAN);

        $responses = $this->fetchTable('Responses');
        $response = $responses->newEntity([
            'complaint_id' => (int)$id,
            'user_id' => (int)$this->authUser['id'],
            'message' => $message,
            'is_internal' => $isInternal ? 1 : 0,
        ], ['validate' => false]);
        $responses->save($response);

        if ($isStaff && !$isInternal) {
            $this->notify(
                (int)$complaint['student_id'],
                (int)$id,
                'New reply on ' . $complaint['reference_no'],
                'You have a new reply on complaint ' . $complaint['reference_no'] . '.'
            );
        } elseif (!$isStaff && $complaint['assigned_to']) {
            $this->notify(
                (int)$complaint['assigned_to'],
                (int)$id,
                'Student replied on ' . $complaint['reference_no'],
                'The student replied on complaint ' . $complaint['reference_no'] . '.'
            );
        }

        return $this->json(['message' => 'Reply added'], 201);
    }

    /** POST /api/complaints/{id}/reopen  (the student who owns a Resolved complaint) */
    public function reopen(string $id): Response
    {
        if ($denied = $this->requireRole(['student'])) {
            return $denied;
        }
        $complaint = $this->loadComplaint((int)$id);
        if (!$complaint || !$this->canAccess($complaint)) {
            return $this->json(['message' => 'Complaint not found'], 404);
        }
        if ($complaint['status'] !== 'Resolved') {
            return $this->json(['message' => 'Only resolved complaints can be reopened'], 422);
        }

        $table = $this->fetchTable('Complaints');
        $entity = $table->get((int)$id);
        $entity->status = 'Reopened';
        $entity->resolved_at = null;
        $table->save($entity);

        $this->logStatus((int)$id, 'Resolved', 'Reopened', (int)$this->authUser['id'], $this->request->getData('note') ?: null);
        $this->audit('complaint_reopened', 'complaints', (int)$id);
        if ($complaint['assigned_to']) {
            $this->notify(
                (int)$complaint['assigned_to'],
                (int)$id,
                'Complaint reopened: ' . $complaint['reference_no'],
                'Complaint ' . $complaint['reference_no'] . ' was reopened by the student.'
            );
        }

        return $this->json(['complaint_id' => $complaint['reference_no'], 'status' => 'Reopened']);
    }

    // ---------------------------------------------------------------- helpers

    /** One complaint with category, unit, student and officer names. */
    private function loadComplaint(int $id): ?array
    {
        return $this->fetchTable('Complaints')->find()
            ->select([
                'Complaints.id', 'Complaints.reference_no', 'Complaints.student_id', 'Complaints.category_id',
                'Complaints.assigned_to', 'Complaints.description', 'Complaints.priority', 'Complaints.status',
                'Complaints.is_anonymous', 'Complaints.resolved_at', 'Complaints.created', 'Complaints.modified',
                'category' => 'c.name', 'unit_id' => 'c.unit_id',
                'student_name' => 's.full_name', 'student_matric' => 's.matric_no',
                'officer_name' => 'o.full_name',
            ])
            ->join([
                'c' => ['table' => 'categories', 'type' => 'INNER', 'conditions' => 'c.id = Complaints.category_id'],
                's' => ['table' => 'users', 'type' => 'INNER', 'conditions' => 's.id = Complaints.student_id'],
                'o' => ['table' => 'users', 'type' => 'LEFT', 'conditions' => 'o.id = Complaints.assigned_to'],
            ])
            ->where(['Complaints.id' => $id])
            ->enableHydration(false)
            ->first();
    }

    /** Students: only their own. Officers: their unit's. Admins: everything. */
    private function canAccess(array $complaint): bool
    {
        $role = $this->authUser['role'];
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'student') {
            return (int)$complaint['student_id'] === (int)$this->authUser['id'];
        }
        if ($role === 'officer') {
            return (int)$complaint['unit_id'] === (int)($this->authUser['unit_id'] ?? 0);
        }

        return false;
    }

    private function logStatus(int $complaintId, ?string $old, string $new, int $by, ?string $note): void
    {
        $table = $this->fetchTable('StatusHistories');
        $table->save($table->newEntity([
            'complaint_id' => $complaintId,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $by,
            'note' => $note,
        ], ['validate' => false]));
    }

    /** Saves the optional "evidence" upload. Returns an error message, or null if fine. */
    private function saveEvidence(int $complaintId): ?string
    {
        $file = $this->request->getUploadedFile('evidence');
        if (!$file || is_array($file) || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return 'The evidence file could not be uploaded (it may be too large).';
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return 'Evidence file is too large (maximum 5MB).';
        }
        $original = (string)$file->getClientFilename();
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'], true)) {
            return 'Evidence file type not allowed (use jpg, png, pdf, doc or docx).';
        }

        $dir = WWW_ROOT . 'uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->moveTo($dir . DS . $name);

        $attachments = $this->fetchTable('Attachments');
        $attachments->save($attachments->newEntity([
            'complaint_id' => $complaintId,
            'file_name' => $original,
            'file_path' => 'uploads/' . $name,
            'mime_type' => $file->getClientMediaType(),
            'size_bytes' => $file->getSize(),
        ], ['validate' => false]));

        return null;
    }
}