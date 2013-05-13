<?php

/**
 * View Group Test for CakeResque
 *
 * PHP versions 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage	 CakeResque.Lib
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/

/**
 * AllCakeResqueTest class
 *
 * @package 		CakeResque
 * @subpackage 	CakeResque.Test.Case
 */
class AllCakeResqueTest extends CakeTestSuite
{

	public static function suite() {
		$suite = new CakeTestSuite('All model tests');
		$suite->addTestDirectory(__DIR__ . DS . 'Lib');
		$suite->addTestDirectory(__DIR__ . DS . 'Console' . DS . 'Command');
		$suite->addTestDirectory(__DIR__ . DS . 'Config');
		return $suite;
	}
}
