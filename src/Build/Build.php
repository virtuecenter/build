<?php
/**
 * virtuecenter\build
 *
 * Copyright (c)2013 Ryan Mahoney, https://github.com/virtuecenter <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Build;

class Build {
	private $root = false;
	private $url = false;
	private $pubSubBuild;
	private $collectionRoute;
	private $helperRoute;
	private $configRoute;
	private $formRoute;
	private $filter;
	private $cache;

	public function __construct ($pubSubBuild, $collectionRoute, $helperRoute, $formRoute, $configRoute, $filter, $cache) {
		$this->pubSubBuild = $pubSubBuild;
		$this->collectionRoute = $collectionRoute;
		$this->helperRoute = $helperRoute;
		$this->configRoute = $configRoute;
		$this->formRoute = $formRoute;
		$this->filter = $filter;
		$this->cache = $cache;
	}

	public function project ($path, $url='%dataAPI%') {
		$this->root = $path;
		$this->url = $url;
		
		$this->clearCache();
		$this->config();
		$this->directories();
		$this->db();
		$this->route();
		$this->collections();
		$this->filters();
		$this->forms();
		$this->helpers();
		$this->topics();
		$this->moveStatic();
		
		echo 'Built', "\n";
		exit;
	}

	private function config () {
		$this->configRoute->build($this->root);
	}

	private function clearCache () {
		$this->cache->deleteBatch([
			$this->root . '-collections.json',
			$this->root . '-filters.json',
			$this->root . '-helpers.json',
			$this->root . '-events.json',
			$this->root . '-forms.json'
		]);
	}

	private function collections () {
		$this->cache->set($this->root . '-collections.json', $this->collectionRoute->build($this->root, $this->url), 2, 0);
	}

	private function forms () {
		$this->cache->set($this->root . '-forms.json', $this->formRoute->build($this->root, $this->url), 2, 0);
	}

	private function filters () {
		$this->cache->set($this->root . '-filters.json', $this->filter->build($this->root), 2, 0);
	}

	private function helpers () {
		$this->cache->set($this->root . '-helpers.json', $this->helperRoute->build($this->root), 2, 0);
	}

	private function topics () {
		$this->pubSubBuild->build($this->root);
	}

	private function db () {
		$dbPath = $this->root . '/../config/db.php';
		if (!file_exists($dbPath)) {
			file_put_contents($dbPath, file_get_contents(__DIR__ . '/../../static/db.php'));
		}
	}

	private function moveStatic () {
		//@symlink($this->root . '/../vendor/virtuecenter/separation/dependencies/handlebars.min.js', $this->root . '/js/handlebars.min.js');
		//@symlink($this->root . '/../vendor/virtuecenter/separation/jquery.separation.js', $this->root . '/js/jquery.separation.js');
		//@symlink($this->root . '/../vendor/virtuecenter/separation/dependencies/jquery.ba-hashchange.js', $this->root . '/js/jquery.ba-hashchange.js');
		//@symlink($this->root . '/../vendor/virtuecenter/separation/dependencies/require.js', $this->root . '/js/require.js');
		@symlink($this->root . '/../vendor/virtuecenter/separation/dependencies/jquery.min.js', $this->root . '/js/jquery.min.js');
		@symlink($this->root . '/../vendor/virtuecenter/separation/dependencies/jquery.form.js', $this->root . '/js/jquery.form.js');
		@symlink($this->root . '/../vendor/virtuecenter/form/js/formXHR.js', $this->root . '/js/formXHR.js');
		@symlink($this->root . '/../vendor/virtuecenter/form/js/formHelperSemantic.js', $this->root . '/js/formHelperSemantic.js');
	}

	private function route () {
		$routePath = $this->root . '/../Route.php';
		if (!file_exists($routePath)) {
			file_put_contents($routePath, file_get_contents(__DIR__ . '/../../static/Route.php'));
		}
	}

	private function directories () {
		foreach (['css', 'js', 'layouts', 'partials', 'images', 'fonts', 'helpers'] as $dir) {
			$dirPath = $this->root . '/' . $dir;
			if (!file_exists($dirPath)) {
				mkdir($dirPath);
			}
		}
		foreach (['collections', 'config', 'forms', 'app', 'mvc', 'subscribers', 'filters', 'bundles'] as $dir) {
			$dirPath = $this->root . '/../' . $dir;
			if (!file_exists($dirPath)) {
				mkdir($dirPath);
			}
		}
		foreach (['collections', 'documents', 'forms'] as $dir) {
			$dirPath = $this->root . '/layouts/' . $dir;
			if (!file_exists($dirPath)) {
				mkdir($dirPath);
			}
			$dirPath = $this->root . '/partials/' . $dir;
			if (!file_exists($dirPath)) {
				mkdir($dirPath);
			}
			$dirPath = $this->root . '/../app/' . $dir;
			if (!file_exists($dirPath)) {
				mkdir($dirPath);
			}
		}
	}
}