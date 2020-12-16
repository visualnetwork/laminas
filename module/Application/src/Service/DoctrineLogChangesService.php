<?php 

namespace Application\Service;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use \DateTime;
use vnw\Entity\AbstractObject\AbstractObject;
use vnw\Entity\Log\Log;


class DoctrineLogChangesService
{
	
	/**
	 * Hold the changes
	 * @var type
	 */
	protected $rows = [];
	protected $sm;
	protected $disabled = [
			'vnw\Entity\Log\Log',
	];
	
	public function __construct($sm)
	{
		$this->sm = $sm;
		$sm->get('Application')
		->getEventManager()
		->attach('finish', [$this, 'flush']);
	}
	
	public function flush()
	{
		$orm = $this->sm->get('Doctrine\ORM\EntityManager');
		
		foreach ($this->rows as $row) {
			$orm->persist($row);
		}
		if (!empty($this->rows)) {
			$orm->flush();
		}
	}
	
	public function getUnitOfWork()
	{
		return $this->sm->get('Doctrine\ORM\EntityManager')->getUnitOfWork();
	}
	
	public function getEntityTableName(LifecycleEventArgs $args)
	{
		$meta = $args->getObjectManager()->getClassMetadata(get_class($args->getObject()));
		return $meta->table['name'];
	}
	
	public function getEntityFields(LifecycleEventArgs $args)
	{
		$meta = $args->getObjectManager()->getClassMetadata(get_class($args->getObject()));
		$fields = [];
		foreach ($meta->fieldMappings as $field) {
			$fields[$field['fieldName']] = $field['columnName'];
		}
		
		foreach ($meta->associationMappings as $field) {
			if (isset($field['sourceToTargetKeyColumns'])) {
				$fields[$field['fieldName']] = key($field['sourceToTargetKeyColumns']);
			}
		}
		
		return $fields;
	}
	
	/**
	 * Adds all initial fields
	 * @param LifecycleEventArgs $args
	 * @return type
	 */
	public function postPersist(LifecycleEventArgs $args)
	{
		$entity = $args->getObject();
		if (in_array(get_class($entity), $this->disabled)) {
			return;
		}
		
		$tableName = $this->getEntityTableName($args);
		$fields = $this->getEntityFields($args);
		
		foreach ($fields as $field => $column) {
			$value = call_user_func_array([$entity, 'get' . ucfirst($field)], []);
			$row = [
					'UserName' => 1,
					'TableName' => $tableName,
					'Pk' => $entity->getId(),
					'ColumnName' => $column,
					'OldValue' => null,
					'NewValue' => $value instanceof AbstractObject ? $value->getId() :  $this->getStringValue($value),
					'Creation' => new \DateTime('NOW')
			];
			$log = new Log();
			$log->exchangeArray($row);
			$this->rows[] = $log;
		}
	}
	
	/**
	 * Add only new changes
	 * @param LifecycleEventArgs $args
	 * @return type
	 */
	public function preUpdate(LifecycleEventArgs $args)
	{
		$entity = $args->getObject();
		if (in_array(get_class($entity), $this->disabled)) {
			return;
		}
		
		$tableName = $this->getEntityTableName($args);
		$fields = $this->getEntityFields($args);
		$set = $this->getUnitOfWork()->getEntityChangeSet($entity);
		foreach ($set as $property => $changes) {
			$oldValue = $changes[0];
			$newValue = $changes[1];
			if ($changes[0] instanceof AbstractObject || $changes[1] instanceof AbstractObject) {
				if ($changes[0] instanceof AbstractObject) {
					$oldValue = $changes[0]->getId();
				}
				if ($changes[1] instanceof AbstractObject) {
					$newValue = $changes[1]->getId();
				}
			}
			
			if ($oldValue == $newValue) {
				continue;
			}
			
			$row = [
					'UserName' => 1,
					'TableName' => $tableName,
					'Pk' => $entity->getId(),
					'ColumnName' => $fields[$property],
					'OldValue' => $this->getStringValue($oldValue),
					'NewValue' => $this->getStringValue($newValue),
					'Creation' => new \DateTime('NOW')
			];
			
			$log = new Log();
			$log->exchangeArray($row);
			$this->rows[] = $log;
		}
	}
	
	public function getStringValue($value)
	{
		if (is_scalar($value)) {
			return $value;
		}
		
		if ($value instanceof \DateTime) {
			return $value->format('Y-m-d H:i:s');
		} elseif (is_array($value)) {
			return serialize($value);
		}
		
		return $value;
	}
	
}
?>