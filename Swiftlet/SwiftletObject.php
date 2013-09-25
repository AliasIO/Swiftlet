<?php

namespace Swiftlet;

/**
 * SwiftletObject class
 * @abstract
 */
abstract class SwiftletObject implements Interfaces\SwiftletObject
{
	/**
	 *
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
						$this->{$property} = $arguments ? $arguments[1] : null;

						return;
					}
				}
			}
		}

		throw new \Swiftlet\Exception('Not implemented: ' . get_called_class() . '::' . $property);
	}
}
