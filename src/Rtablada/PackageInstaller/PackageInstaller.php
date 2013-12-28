<?php namespace Rtablada\PackageInstaller;

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Config\Repository as Config;

/**
* Installs a packages provided ServiceProviders and Aliases to config/app.php
*/
class PackageInstaller
{
	/**
	 * Instance of the File class
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $file;

	/**
	 * Instance of the Config class
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Cache of configuration contents
	 *
	 * @var string
	 */
	protected $contentsCache;

	/**
	 * Creates the PackageInstaller Instance
	 *
	 * @param Illuminate\Filesystem\Filesystem $file
	 */
	public function __construct(File $file, Config $config)
	{
		$this->file = $file;
		$this->config = $config;
	}

	/**
	 * Updates the app.php file
	 * 
	 * @param Rtablada\PackageIstaller\Provider
	 */
	public function updateConfigurations(Provider $provider)
	{
		$this->updateProviders($provider->providers);
		$this->updateAliases($provider->aliases);
		return $this->putConfigContents();
	}

	/**
	 * Merges the existing providers with the new ones
	 * 
	 * @param  array  $providers
	 */
	protected function updateProviders(array $providers)
	{
		$this->getConfigContents();
		$configProviders = $this->config->get('app.providers');
		$configProviders = array_merge($configProviders, $providers);
		$configProviders = array_unique($configProviders);
		$this->replaceConfig('providers', $configProviders);
	}

	/**
	 * Merges the existing aliases with the new ones
	 * 
	 * @param  array  $aliases
	 */
	protected function updateAliases(array $aliases)
	{
		$aliases = $this->getAliasMap($aliases);
		$this->getConfigContents();
		$configAliases = $this->config->get('app.aliases');
		$configAliases = array_merge($configAliases, $aliases);
		$configAliases = array_unique($configAliases);
		$this->replaceConfig('aliases', $configAliases);
	}

	/**
	 * [getAliasMap description]
	 * 
	 * @param  array  $aliases
	 * @return array         
	 */
	protected function getAliasMap(array $aliases)
	{
		$aliasMap = array();
		foreach ($aliases as $alias) {
			$aliasMap[$alias->alias] = $alias->facade;
		}
		return $aliasMap;
	}

	/**
	 * Writes the configuration back to the filesystem
	 * 
	 * @return integer 
	 */
	protected function putConfigContents()
	{
		return $this->file->put($this->getConfigPath(), $this->contentsCache);
	}

	/**
	 * Replaces the old aliases|providers with the new ones
	 * 
	 * @param  string $key   aliases | providers
	 * @param  array  $array 
	 */
	protected function replaceConfig($key, array $array)
	{
		$replace = $this->getNewConfigContents($key, $array);
		$pattern = "/'{$key}' => array\([^)]*\)/s";
		$this->contentsCache = preg_replace($pattern, $replace, $this->contentsCache);
	}

	/**
	 * Adds the new line
	 * 
	 * @param  string $key   aliases | providers
	 * @param  array  $array items
	 * @return string        the update providers and alias content
	 */
	protected function getNewConfigContents($key, array $array)
	{
		if (isset($array[0])) {
			$header = "'{$key}' => array(\n\n\t\t'";
			$values = implode("',\n\t\t'", $array);
			$content = $header . $values . "',\n\n\t)";
		} else {
			$header = "'{$key}' => ";
			$values = var_export($array, true);
			$content = $header . $values;
			$content = str_replace('(', "(\n\t", $content);
			$content = str_replace(')', "\n\t)", $content);
			$content = str_replace("\n  ", "\n\t\t", $content);
			$content = str_replace("array (", "array(", $content);
		}

		return $content;
	}

	/**
	 * Loads the config file
	 * 
	 * @return string content of app.php
	 */
	protected function getConfigContents()
	{
		if(!isset($this->contentsCache)) {
			$this->contentsCache = $this->file->get($this->getConfigPath());
		}

		return $this->contentsCache;
	}

	/**
	 * Determines the application config path
	 * 
	 * @return string 
	 */
	protected function getConfigPath()
	{
		return app_path().'/config/app.php';
	}
}
