<?php
namespace OdyMaterialyAPI;

@_API_EXEC === 1 or die('Restricted access.');

class APIException extends \Exception implements \JsonSerializable
{
	const TYPE = 'APIException';

	public function jsonSerialize()
	{
		return ['success' => false, 'type' => static::TYPE, 'message' => $this->getMessage()];
	}

	public function __toString()
	{
		return json_encode($this, JSON_UNESCAPED_UNICODE);
	}
}
