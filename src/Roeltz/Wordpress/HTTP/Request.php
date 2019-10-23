<?php

namespace Roeltz\Wordpress\HTTP;

class Request {

	public $method;

	public $path;

	public $host;

	public $secure;

	public $headers;

	public $data;

	static function fromGlobals(array $data = []) {
		$method = $_SERVER["REQUEST_METHOD"];
		$path = $_SERVER["REQUEST_URI"];
		$host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
		$secure = @$_SERVER["HTTPS"] == "on";
		$headers = [];
		$data = [];

		foreach (getallheaders() as $header=>$value) {
			$headers[strtolower($header)] = $value;
		}

		if (strpos(@$headers["content-type"], "application/json") !== false) {
			$data = array_merge(self::getJSONEntityBodyData(), $data);
		} else {
			$data = array_merge(self::getHTTPEntityBodyData(), $data);
		}

		return new self($method, $path, $host, $secure, $headers, $data);
	}

	static function getJSONEntityBodyData() {
		$json = @json_decode(file_get_contents("php://input"), true);
		return array_merge($_REQUEST, (array) $json);
	}

	static function getHTTPEntityBodyData() {
		$data = $_REQUEST;

		foreach($data as $paramName=>&$value) {
			if (empty($value) && $value !== "0")
				$value = null;
		}

		foreach($_FILES as $paramName=>$file) {
			$fileValue = null;
			if (is_array($file["name"])) {
				$items = array();
				foreach ($file["name"] as $i=>$fileName) {
					if (!$file["error"][$i])
						$items[] = new UploadedFile($fileName, $file["tmp_name"][$i], $file["type"][$i]);
				}
				$fileValue = $items;
			} elseif (!$file["error"]) {
				$fileValue = new UploadedFile($file["name"], $file["tmp_name"], $file["type"]);
			}
			if ($fileValue !== null) {
				$data[$paramName] = (@$data->$paramName) ? array_merge((array) $data->$paramName, $fileValue) : $fileValue;
			}
		}
		return $data;
	}

	function __construct($method, $path, $host = null, $secure = false, array $headers = [], array $data = []) {
		$this->method = $method;
		$this->path = $path;
		$this->host = $host;
		$this->secure = $secure;
		$this->headers = $headers;
		$this->data = $data;
	}

	function getURL() {
		$qs = $_SERVER["QUERY_STRING"] ? "?{$_SERVER["QUERY_STRING"]}" : "";
		return ($this->https ? "https" : "http") . "://{$this->host}{$this->path}$qs";
	}
}
