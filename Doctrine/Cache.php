<?php


namespace Inpa\Doctrine;

/**
 * Nette cache driver for doctrine
 *
 * @author	Patrik Votoček
 * @author	Filip Procházka
 */
class Cache extends \Doctrine\Common\Cache\AbstractCache
{
	/** @var string */
	const CACHED_KEYS_KEY = 'Inpa.Doctrine.Cache.Keys';
	
	/** @var \Nette\Caching\Cache */
	private $cache = array();
	
	/** @var array */
	private $keys = array();

	/**
	 * @param \Nette\Caching\IStorage
	 */
	public function  __construct(\Nette\Caching\IStorage $cacheStorage)
	{
		$this->cache = new \Nette\Caching\Cache($cacheStorage, str_replace("\\", ".", __CLASS__));
		$this->keys = $this->cache->derive('.Keys');
	}
	
	/* * ******************* Cache keys fix ******************* */
	
	/**
	 * @param scalar $key
	 */
	private function removeCacheKey($key)
	{
		$keys = $this->keys[self::CACHED_KEYS_KEY];
		if (isset($keys[$key])) {
			unset($keys[$key]);
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}

		return $keys;
	}



	/**
	 * @param scalar $key
	 */
	private function addCacheKey($key, $lifetime = 0)
	{
		$keys = $this->keys[self::CACHED_KEYS_KEY];
		if (!isset($keys[$key]) || $keys[$key] !== ($lifetime ?: TRUE)) {
			$keys[$key] = $lifetime ?: TRUE;
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}
		
		return $keys;
	}
	
	/*	 * ******************* \Doctrine\Common\Cache\AbstractCache ******************* */

	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		//@TODO wait fot $cache->getIds() in Nette\Caching\Cache 
		$keys = (array)$this->keys[self::CACHED_KEYS_KEY];
		$keys = array_filter($keys, function($expire) {
			if ($expire > 0 && $expire < time()) {
				return FALSE;
			} // otherwise it's still valid

			return TRUE;
		});

		if ($keys !== $this->keys[self::CACHED_KEYS_KEY]) {
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}

		return array_keys($keys);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		if (isset($this->cache[$id])) {
			return $this->cache->load($id);
		}
		return FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		return isset($this->cache[$id]);
	}

	/**
	* {@inheritdoc}
	*/
	protected function _doSave($id, $data, $lifeTime = 0)
	{
		$files = array();
		if ($data instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
			$ref = \Nette\Reflection\ClassType::from($data->name);
			$files[] = $ref->getFileName();
			foreach ($data->parentClasses as $class) {
				$ref = \Nette\Reflection\ClassType::from($class);
				$files[] = $ref->getFileName();
			}
		}
		
		if ($lifeTime != 0) {
			$this->cache->save($id, $data, array('expire' => time() + $lifeTime, 'tags' => array("doctrine"), 'files' => $files));
			$this->addCacheKey($id, time() + $lifeTime);
		} else {
			$this->cache->save($id, $data, array('tags' => array("doctrine"), 'files' => $files));
			$this->addCacheKey($id);
		}
		return TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		unset($this->cache[$id]);
		$this->removeCacheKey($id);
		return TRUE;
	}
}
