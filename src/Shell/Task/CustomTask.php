<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Upgrade\Shell\Task;

use Cake\Upgrade\Shell\Task\BaseTask;
use Cake\Utility\Inflector;
use Cake\Utility\String;
/**
 * Handles custom stuff
 *
 */
class CustomTask extends BaseTask {

	use ChangeTrait;

	public $tasks = ['Stage'];

/**
 * Processes a path.
 *
 * @param string $path
 * @return void
 */
	protected function _process($path) {
		$configPatterns = [
			[
				'<?php $config = [ to return $config = [',
				'/\<\?php(\s+\s*)\$config = \[/',
				'<?php\1return ['
			]
		];
		if (strpos($path, DS . 'config' . DS) !== false) {
			$original = $contents = $this->Stage->source($path);
			$contents = $this->_updateContents($contents, $configPatterns);

			return $this->Stage->change($path, $original, $contents);
		}

		$patterns = [
			[
				'->_table->behaviors()->loaded( to has(',
				'/-\>behaviors\(\)-\>loaded\(([^\)]+)/',
				'->behaviors()->has(\1'
			],
			[
				'validateIdentical compare fields',
				'/\'validateIdentical\', \'(.+?)\'\$/i',
				'\'validateIdentical\', [\'compare\' => \'\1\']',
			],
			[
				'throw new FooException( to throw new \\Exception(',
				'/\bthrow new (?!(MethodNotAllowed|Forbidden|NotFound))*Exception\(/i',
				'throw new \Exception(',
			],
			[
				'new DateTime(',
				'/\bnew DateTime\(/i',
				'new \DateTime(',
			],
			[
				'<br> to <br/>',
				'/\<br\s*\>/i',
				'<br/>',
			],
			[
				'<br /> to <br/>',
				'/\<br\s+\/\>/i',
				'<br/>',
			],
			[
				'Tools.GoogleMapV3 to Geo.GoogleMapV3',
				'/\bTools.GoogleMapV3\b/',
				'Geo.GoogleMapV3'
			],
			[
				'Tools.Geocoder to Geo.Geocoder',
				'/\bTools.Geocoder\b/',
				'Geo.Geocoder'
			],
			[
				'Tools.Ajax to Ajax.Ajax',
				'/\bTools.Ajax\b/',
				'Ajax.Ajax'
			],
			[
				'Tools.Tiny to TinyAuth.Tiny',
				'/\bTools.Tiny\b/',
				'TinyAuth.Tiny'
			],
		];

		$original = $contents = $this->Stage->source($path);

		$contents = $this->_updateContents($contents, $patterns);
		$contents = $this->_replaceCustom($contents, $path);

		return $this->Stage->change($path, $original, $contents);
	}

/**
 * Custom stuff
 *
 * @param string $contents
 * @param string $path
 * @return string
 */
	protected function _replaceCustom($contents, $path) {
		return $contents;

		$pattern = '//i';
		$replacement = function ($matches) {
			$entity = lcfirst($matches[1]);
			return '$this->Form->create($' . $entity . ')';
		};
		$contents = preg_replace_callback($pattern, $replacement, $contents);

		return $contents;
	}


/**
 * _shouldProcess
 *
 * Bail for invalid files (php/ctp files only)
 *
 * @param string $path
 * @return bool
 */
	protected function _shouldProcess($path) {
		$ending = substr($path, -4);
		return $ending === '.php' || $ending === '.ctp';
	}

}
