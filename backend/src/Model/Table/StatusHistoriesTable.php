<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StatusHistories Model
 *
 * @property \App\Model\Table\ComplaintsTable&\Cake\ORM\Association\BelongsTo $Complaints
 *
 * @method \App\Model\Entity\StatusHistory newEmptyEntity()
 * @method \App\Model\Entity\StatusHistory newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\StatusHistory> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StatusHistory get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\StatusHistory findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\StatusHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\StatusHistory> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\StatusHistory|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\StatusHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\StatusHistory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\StatusHistory>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\StatusHistory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\StatusHistory> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\StatusHistory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\StatusHistory>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\StatusHistory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\StatusHistory> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StatusHistoriesTable extends Table
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

        $this->setTable('status_histories');
        $this->setDisplayField('new_status');
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
            ->scalar('old_status')
            ->maxLength('old_status', 20)
            ->allowEmptyString('old_status');

        $validator
            ->scalar('new_status')
            ->maxLength('new_status', 20)
            ->requirePresence('new_status', 'create')
            ->notEmptyString('new_status');

        $validator
            ->integer('changed_by')
            ->allowEmptyString('changed_by');

        $validator
            ->scalar('note')
            ->maxLength('note', 500)
            ->allowEmptyString('note');

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
