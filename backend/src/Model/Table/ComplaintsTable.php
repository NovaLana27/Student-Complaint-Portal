<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Complaints Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Students
 * @property \App\Model\Table\CategoriesTable&\Cake\ORM\Association\BelongsTo $Categories
 * @property \App\Model\Table\AttachmentsTable&\Cake\ORM\Association\HasMany $Attachments
 * @property \App\Model\Table\NotificationsTable&\Cake\ORM\Association\HasMany $Notifications
 * @property \App\Model\Table\ResponsesTable&\Cake\ORM\Association\HasMany $Responses
 * @property \App\Model\Table\StatusHistoriesTable&\Cake\ORM\Association\HasMany $StatusHistories
 *
 * @method \App\Model\Entity\Complaint newEmptyEntity()
 * @method \App\Model\Entity\Complaint newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Complaint> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Complaint get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Complaint findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Complaint patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Complaint> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Complaint|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Complaint saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Complaint>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Complaint>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Complaint>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Complaint> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Complaint>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Complaint>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Complaint>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Complaint> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ComplaintsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('complaints');
        $this->setDisplayField('priority');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Students', [
            'foreignKey' => 'student_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Attachments', [
            'foreignKey' => 'complaint_id',
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'complaint_id',
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'complaint_id',
        ]);
        $this->hasMany('StatusHistories', [
            'foreignKey' => 'complaint_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('reference_no')
            ->maxLength('reference_no', 30)
            ->allowEmptyString('reference_no')
            ->add('reference_no', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('student_id')
            ->notEmptyString('student_id');

        $validator
            ->integer('category_id')
            ->notEmptyString('category_id');

        $validator
            ->integer('assigned_to')
            ->allowEmptyString('assigned_to');

        $validator
            ->scalar('description')
            ->requirePresence('description', 'create')
            ->notEmptyString('description');

        $validator
            ->scalar('priority')
            ->maxLength('priority', 10)
            ->notEmptyString('priority');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

        $validator
            ->boolean('is_anonymous')
            ->notEmptyString('is_anonymous');

        $validator
            ->dateTime('resolved_at')
            ->allowEmptyDateTime('resolved_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['reference_no'], ['allowMultipleNulls' => true]), ['errorField' => 'reference_no']);
        $rules->add($rules->existsIn(['student_id'], 'Students'), ['errorField' => 'student_id']);
        $rules->add($rules->existsIn(['category_id'], 'Categories'), ['errorField' => 'category_id']);

        return $rules;
    }
}
