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
	 * [groupfiles src="wp-content/folder_name" maxdepth="2"]
	 */
	function shortcode_handler($atts, $content=NULL, $code="") {
		extract( shortcode_atts( array( 'src' => dirname(__FILE__),
						'maxdepth' => 0), 
			$atts ) );


		$groups = $this->group_files(ABSPATH . $src, $maxdepth);
		if ($groups && count($groups) > 0) {
			$this->print_grouped_files($groups);
		}
	}

	/*
	 * Finds all files in a directory recursively 
	 * returns associative array where key is full path, value is short file name
	 */
	function all_files($dir, $depth, $maxdepth) {
		$paths = array();

		if ($maxdepth && $depth < $maxdepth && 
			is_dir($dir) && $handle = opendir($dir)) {

			while(false !==($file = readdir($handle))) {
				
				if($file != '..' && $file != '.') {
					$fullpath = $dir . '/' . $file;
					if (is_dir($fullpath)) {
						$paths = array_merge($paths, $this->all_files($fullpath, $depth + 1, $maxdepth));
					} else {
						$name = $file;
						// strip file extension
						$ext = strrchr($name, '.');  
						if($ext !== false)  {  
							$name = substr($name, 0, -strlen($ext));  
						}  
						// lowercase
						$name = strtolower($name);
						
						// treat _ as ' '
						$name = strtr($name, "-_", "  ");

						// remove '
						$name = str_replace("'", "", $name);

						// strip trailing number
						$words = explode(' ', $name);
						$count = count($words);
						if ($count > 1) {
							$last = $words[$count - 1];
							if (is_numeric($last)) {
								array_pop($words);
								$name = implode(" ", $words);
							}
						}

						// strip trailing 'p1, p2, p3'
						$count = count($words);
						if ($count > 1) {
							$last = $words[$count - 1];
							if ($last == 'p1' || 
								$last == 'p2' ||
								$last == 'p3') {
								array_pop($words);
								$name = implode(" ", $words);
							}
						}

						if (strlen($name) > 0) {
							$paths[$fullpath] = $name;
						}

					}
				}
			}
		}

		return $paths;
	}
	/*
	 * Groups files and puts 'em in associateive array keyed by group
	 */
	function group_files($dir, $maxdepth) {

		$all_files = $this->all_files($dir, 0, $maxdepth);

		asort($all_files);

		$groups = array();

		// Group by similar prefix
		$prefix = NULL;
		foreach ($all_files as $path => $name) {
			if (! $prefix || strncmp($name, $prefix, strlen($prefix)) != 0) {
				$prefix = $name;
				$groups[$prefix] = array( $path );
			} else {
				array_push($groups[$prefix], $path);
			}
		}

		/*
		function str_startswith($source, $prefix)
		{
			   return strncmp($source, $prefix, strlen($prefix)) == 0;
		}
		 */

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

				    $relpath = str_replace(ABSPATH, "", $path);

				    echo "<li>$relpath</li>";
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
