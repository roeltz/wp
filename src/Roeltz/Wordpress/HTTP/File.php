<?php

namespace Roeltz\Wordpress\HTTP;

class File {

	const TYPE_DIRECTORY = 'dir';
	const DEFAULT_TYPE = 'application/octet-stream';

	public $path;
	public $type;

	function __construct($path, $type = null) {
		$this->path = $path;
		if (is_file($path))
			$this->type = $type ? $type : $this->guessType($path);
		else
			$this->type = self::TYPE_DIRECTORY;
	}

	function getExtension() {
		return end(explode('.', basename($this->path)));
	}

	function getExtensionlessName() {
		$parts = explode('.', basename($this->path));
		array_pop($parts);
		return join('.', $parts);
	}

	function getRelativePath($to) {
		return \Pipa\relative_path($to, $this->path);
	}

	function getSize() {
		return filesize($this->path);
	}

	function delete() {
		unlink($this->path);
	}

	function __toString() {
		return $this->path;
	}

	private function guessType($path) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo) {
			return finfo_file($finfo, $path);
		} else {
			return self::DEFAULT_TYPE;
		}
	}
}
