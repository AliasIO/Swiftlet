<?php

namespace Swiftlet\Abstracts;

/**
 * Common class
 * @abstract
 */
abstract class Common implements \Swiftlet\Interfaces\Common
{
	/**
	 * Common setters and getters
	 *
	 * @param string $property
	 * @param mixed $arguments
	 * @throws \Swiftlet\Exception
	 */
	public function __call($property, $arguments)
	{
		$action = substr($property, 0, 3);

		if ( $action == 'get' || $action == 'set' ) {
			$property = lcfirst(substr($property, 3));

			if ( property_exists($this, $property) ) {
				$reflection = new \ReflectionObject($this);

				if ( $reflection->getProperty($property)->isPublic() ) {
					if ( $action == 'get' ) {
						return $this->{$property};
					} else {
						$this->{$property} = $arguments ? $arguments[0] : null;

						return $this;
					}
				}
			}
		}

		throw new \Swiftlet\Exception('Not implemented: ' . get_called_class() . '::' . $property);
	}
}
