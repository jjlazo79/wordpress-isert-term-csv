<?php

declare(strict_types=1);
/**
 * Class ImportTerm
 *
 * Handles the importation of CSV data into WP Term
 * @package WordPress
 * @subpackage ImportTerm
 * @since ImportTerm 0.0.1
 */
class ImportTerm
{
	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance()
	{

		if (null == self::$instance) {
			self::$instance = new ImportTerm();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct()
	{
		add_action('init', array($this, 'inser_tax'));
	}

	/**
	 * Extract CSV data into multidimensional array
	 *
	 * @author: Klemen Nagode
	 */
	public static function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n")
	{
		$array        = array();
		$size         = strlen($string);
		$columnIndex  = 0;
		$rowIndex     = 0;
		$fieldValue   = "";
		$isEnclosured = false;
		for ($i = 0; $i < $size; $i++) {

			$char = $string[$i];
			$addChar = "";

			if ($isEnclosured) {
				if ($char == $enclosureChar) {

					if ($i + 1 < $size && $string[$i + 1] == $enclosureChar) {
						// escaped char
						$addChar = $char;
						$i++; // dont check next char
					} else {
						$isEnclosured = false;
					}
				} else {
					$addChar = $char;
				}
			} else {
				if ($char == $enclosureChar) {
					$isEnclosured = true;
				} else {

					if ($char == $separatorChar) {

						$array[$rowIndex][$columnIndex] = $fieldValue;
						$fieldValue = "";

						$columnIndex++;
					} elseif ($char == $newlineChar) {
						echo $char;
						$array[$rowIndex][$columnIndex] = $fieldValue;
						$fieldValue  = "";
						$columnIndex = 0;
						$rowIndex++;
					} else {
						$addChar = $char;
					}
				}
			}
			if ($addChar != "") {
				$fieldValue .= $addChar;
			}
		}

		if ($fieldValue) { // save last field
			$array[$rowIndex][$columnIndex] = $fieldValue;
		}
		return $array;
	}

	/**
	* Pass string to slug
	*
	* @param string $text
	* @return void
	*/
	public static function slugify($text)
	{
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}

	/**
	* Insert csv as term list
	*
	* @return void
	*/
	public function inser_tax()
	{
		$dataArray = ImportTerm::csvstring_to_array(file_get_contents(__DIR__ . '/municipios-2017.csv'), ';');
		$taxonomy = 'service-city';
		foreach ($dataArray as $data) :

			$term_name      = $data[2];
			$term_slug      = ImportTerm::slugify($term_name);
			$parent_term_id = $data[3];
			$output = wp_insert_term(
				$term_name,   // the term
				$taxonomy, // the taxonomy
				array(
					'slug'        => $term_slug,
					'parent'      => $parent_term_id,
				)
			);

			// print_r($output);

			// print_r($term_name);
			// echo '<br>';
			// print_r($term_slug);
			// echo '<br>';
			// echo '<br>';
			// print_r($data);

		endforeach;
	}
}
