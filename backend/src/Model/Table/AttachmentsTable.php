<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Attachments Model
 *
 * @property \App\Model\Table\ComplaintsTable&\Cake\ORM\Association\BelongsTo $Complaints
 *
 * @method \App\Model\Entity\Attachment newEmptyEntity()
 * @method \App\Model\Entity\Attachment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Attachment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Attachment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Attachment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Attachment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Attachment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Attachment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Attachment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AttachmentsTable extends Table
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

        $this->setTable('attachments');
        $this->setDisplayField('file_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Complaints', [
            'foreignKey' => 'complaint_id',
            'joinType' => 'INNER',
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
            ->integer('complaint_id')
            ->notEmptyString('complaint_id');

        $validator
            ->scalar('file_name')
            ->maxLength('file_name', 255)
            ->requirePresence('file_name', 'create')
            ->notEmptyString('file_name');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 255)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 100)
            ->allowEmptyString('mime_type');

        $validator
            ->integer('size_bytes')
            ->allowEmptyString('size_bytes');

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
        $rules->add($rules->existsIn(['complaint_id'], 'Complaints'), ['errorField' => 'complaint_id']);

        return $rules;
    }
}
