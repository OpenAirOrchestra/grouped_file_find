<?php
/*
Plugin Name: Grouped File Find 
Plugin URI: http://www.thecarnivalband.com
Description: Displays files in a directory tree in groups of similar files.
Version: 0.1
Author: Open Air Orchestra Webmonkey
Author URI: mailto://oaowebmonkey@gmail.com
License: GPL2
*/

/*  Copyright 2010  Open Air Orchestra  (email : oaowebmonkey@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USAi
*/

$include_folder = dirname(__FILE__);

/*
 * Main class for grouped file find.  Handles activation, hooks, etc.
 */
class groupedFileFind {

	/*
	 * Handles groupfiles shortcode
	 * example:
	 * [groupfiles src="wp-content/folder_name"]
	 */
	function shortcode_handler($atts, $content=NULL, $code="") {
		extract( shortcode_atts( array( 'src' => dirname(__FILE__)), 
			$atts ) );


		$groups = $this->group_files(ABSPATH . $src);
		if ($groups && count($groups) > 0) {
			$this->print_grouped_files($groups);
		}
	}

	/*
	 * Finds all files in a directory recursively 
	 */
	function all_files($dir) {
		$paths = array();

		if(is_dir($dir) && $handle = opendir($dir)) {

			while(false !==($file = readdir($handle))) {
				
				if($file != '..' && $file != '.') {
					$fullpath = $dir . '/' . $file;
					if (isdir($fullpath)) {
						$paths = array_merge($paths, $this->all_files($fullpath));
					} else {
						array_push($paths, $fullpath);
					}
				}
			}
		}

		return $paths;
	}
	/*
	 * Groups files and puts 'em in associateive array keyed by group
	 */
	function group_files($dir) {

		$all_files = find_files($dir);

		$groups = array();

		$groups['foo'] = array( 'Bb/foob', 'Bb/fooz', 'Bb/fooa');
		$groups['bar'] = array( 'Bb/barfoob', 'Bb/barfooz');
		$groups['paths'] = $all_files;

		ksort($groups);

		return $groups;
	}

	/*
	 * Outputs grouped files
	 */
	function print_grouped_files($groups) {

		foreach ($groups as $key => $group) {
			    sort($group);
			    echo "<h4>$key</h4>\n";
			    echo "<ul>\n";
			    foreach ($group as $path) {
				    echo "<li>$path</li>";
			    }
			    echo "</ul>\n";
		}
	}
}

$GROUPEDFILEFIND = new groupedFileFind;

// shortcodes
add_shortcode('groupfiles', array($GROUPEDFILEFIND, 'shortcode_handler'));

//
?>
