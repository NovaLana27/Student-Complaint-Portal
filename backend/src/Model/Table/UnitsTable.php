<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Units Model
 *
 * @property \App\Model\Table\CategoriesTable&\Cake\ORM\Association\HasMany $Categories
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\Unit newEmptyEntity()
 * @method \App\Model\Entity\Unit newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Unit> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Unit get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Unit findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Unit patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Unit> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Unit|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Unit saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Unit>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Unit>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Unit>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Unit> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Unit>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Unit>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Unit>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Unit> deleteManyOrFail(iterable $entities, array $options = [])
 */
class UnitsTable extends Table
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

        $this->setTable('units');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Categories', [
            'foreignKey' => 'unit_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'unit_id',
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
            ->scalar('name')
            ->maxLength('name', 120)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->email('email')
            ->allowEmptyString('email');

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
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
    }
}
