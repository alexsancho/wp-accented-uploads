<?php
/**
 * Plugin name: WP Sanitize Accented Uploads
 * Plugin URI: https://github.com/alexsancho/wp-accented-uploads
 * Description: Replaces accents from future uploads and has wp-cli command which you can use to sanitize current content
 * Author: Alex Sancho
 * Author URI: https://github.com/alexsancho
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Version: 1.0.0
 */

namespace Asancho;

use Normalizer;

\defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Add commands into wp-cli
 * With these you can easily make your current database and uploads sanitized
 */
if ( \defined( 'WP_CLI' ) && WP_CLI ) {
    require_once __DIR__ . '/wp-cli-integration.php';
}

if ( ! class_exists( __NAMESPACE__ . '\\Sanitizer' ) ) {
    /**
     * Class Sanitizer
     *
     * @since 1.0.0
     */
    class Sanitizer {

        /**
		 * Loads Plugin features
		 */
        public static function load() {

            // Remove accents from all uploaded files
            add_filter( 'sanitize_file_name', [ __CLASS__, 'sanitize_filenames_on_upload' ], 10, 1 );
        }

        /**
		 * Replaces all files immediately on upload
         *
         * @param string $filename File name.
         *
         * @return string
         */
        public static function sanitize_filenames_on_upload( $filename ) {

            // Remove accents and filename to lowercase for better urls
            // Don't sanitize file here because wordpress does this automatically
            return strtolower( self::remove_accents( $filename, false ) );
        }

        /**
         * Removes all accents from string
         *
         * @param string $filename Any filename with absolute path.
         * @param bool   $sanitize Sanitized all special characters as well?.
         *
         * @return null|string|string[]
         */
        public static function remove_accents( $filename, $sanitize = true ) {

            // Get path and basename
            $file_info = pathinfo( $filename );
            $filename  = $file_info['basename'];

            // If available remove all NFD characters before doing anything else
            if ( class_exists( 'Normalizer' ) ) {
                $filename = Normalizer::normalize( $filename, Normalizer::FORM_C );
            }

            // Removes accents using wordpress function
            $filename = remove_accents( $filename );

            if ( $sanitize ) {
                // Sanitize special characters for files so that they work in urls
                $filename = sanitize_file_name( $filename );

            }

            // And then just remove anything fancy like ¼ and ™
            $filename = self::remove_non_ascii_characters( $filename );

            // If this was full path return it like it was before
            // pathinfo returns . for only filenames
            if ( $file_info['dirname'] !== '.' ) {
                $filename = $file_info['dirname'] . '/' . $filename;
            }

            // Return full path
            return $filename;
        }

        /**
         * Removes all non-ascii characters
         *
         * @param string $string String to sanitize.
         *
         * @return null|string|string[]
         */
        public static function remove_non_ascii_characters( $string ) {
            return preg_replace( "/[^(\x20-\x7F)]*/", '', $string );
        }

        /**
         * Tries to move any version of NFC & NFD unicode compositions of $old_file
         * Also tries to fix typical unix file encoding errors
         *
         * @param string $old_file Full path to original file.
         * @param string $new_file Full path to new file.
         *
         * @return string Return the name of the file which could be found
         */
        public static function rename_accented_files_in_any_form( $old_file, $new_file ) {
            // Try to move the file without any hacks before continuing
            $result = @rename( $old_file, $new_file );

            if ( $result ) {
                return $old_file;
            }

            // Continue if we couldn't rename $old_file and $new_file doesn't yet exist
            if ( ! file_exists( $new_file ) ) {

                $possible_old_files = [];

                // If Normalizer is available try to rename file with NFD characters
                if ( class_exists( 'Normalizer' ) ) {

                    // This is the normal way to do unicode
                    $possible_old_files[] = Normalizer::normalize( $old_file, Normalizer::FORM_C );

                    // This is the OS-X way to do unicode
                    $possible_old_files[] = Normalizer::normalize( $old_file, Normalizer::FORM_D );

                    // Even try to fix files which have already been corrupted
                    // by creating encoding errors on purpose
                    $possible_old_files[] = self::replace_filename_with_encoding_errors( $old_file );
                }

                foreach ( $possible_old_files as $possible_old_file ) {

                    // Rename the file if it exists, ignore errors because this is encoding bingo
                    $result = @rename( $possible_old_file, $new_file );

                    // Stop immediately if we found a solution
                    if ( $result ) {
                        return $possible_old_file;
                    }
                }
            }

            // Return false if it didn't find a file earlier
            return false;
        }

        /**
         * This is a list of usual encoding errors
         *
         * @return array List of possible fixes for encoding errors
         */
        public static function get_encoding_fix_list() {

            /**
             * List of possible fixes for encoding errors
             *
			 * @source  http://www.i18nqa.com/debug/utf8-debug.html
			 */
            $fix_list = [

                // 3 char errors first
                'â€š' => '‚',
                'â€ž' => '„',
                'â€¦' => '…',
                'â€¡' => '‡',
                'â€°' => '‰',
                'â€¹' => '‹',
                'â€˜' => '‘',
                'â€™' => '’',
                'â€œ' => '“',
                'â€¢' => '•',
                'â€“' => '–',
                'â€”' => '—',
                'â„¢' => '™',
                'â€º' => '›',
                'â‚¬' => '€',

                // 2 char errors
                'Ã‚'  => 'Â',
                'Æ’'  => 'ƒ',
                'Ãƒ'  => 'Ã',
                'Ã„'  => 'Ä',
                'Ã…'  => 'Å',
                'â€'  => '†',
                'Ã†'  => 'Æ',
                'Ã‡'  => 'Ç',
                'Ë†'  => 'ˆ',
                'Ãˆ'  => 'È',
                'Ã‰'  => 'É',
                'ÃŠ'  => 'Ê',
                'Ã‹'  => 'Ë',
                'Å’'  => 'Œ',
                'ÃŒ'  => 'Ì',
                'Å½'  => 'Ž',
                'ÃŽ'  => 'Î',
                'Ã‘'  => 'Ñ',
                'Ã’'  => 'Ò',
                'Ã“'  => 'Ó',
                'â€'  => '”',
                'Ã”'  => 'Ô',
                'Ã•'  => 'Õ',
                'Ã–'  => 'Ö',
                'Ã—'  => '×',
                'Ëœ'  => '˜',
                'Ã˜'  => 'Ø',
                'Ã™'  => 'Ù',
                'Å¡'  => 'š',
                'Ãš'  => 'Ú',
                'Ã›'  => 'Û',
                'Å“'  => 'œ',
                'Ãœ'  => 'Ü',
                'Å¾'  => 'ž',
                'Ãž'  => 'Þ',
                'Å¸'  => 'Ÿ',
                'ÃŸ'  => 'ß',
                'Â¡'  => '¡',
                'Ã¡'  => 'á',
                'Â¢'  => '¢',
                'Ã¢'  => 'â',
                'Â£'  => '£',
                'Ã£'  => 'ã',
                'Â¤'  => '¤',
                'Ã¤'  => 'ä',
                'Â¥'  => '¥',
                'Ã¥'  => 'å',
                'Â¦'  => '¦',
                'Ã¦'  => 'æ',
                'Â§'  => '§',
                'Ã§'  => 'ç',
                'Â¨'  => '¨',
                'Ã¨'  => 'è',
                'Â©'  => '©',
                'Ã©'  => 'é',
                'Âª'  => 'ª',
                'Ãª'  => 'ê',
                'Â«'  => '«',
                'Ã«'  => 'ë',
                'Â¬'  => '¬',
                'Ã¬'  => 'ì',
                'Â®'  => '®',
                'Ã®'  => 'î',
                'Â¯'  => '¯',
                'Ã¯'  => 'ï',
                'Â°'  => '°',
                'Ã°'  => 'ð',
                'Â±'  => '±',
                'Ã±'  => 'ñ',
                'Â²'  => '²',
                'Ã²'  => 'ò',
                'Â³'  => '³',
                'Ã³'  => 'ó',
                'Â´'  => '´',
                'Ã´'  => 'ô',
                'Âµ'  => 'µ',
                'Ãµ'  => 'õ',
                'Â¶'  => '¶',
                'Ã¶'  => 'ö',
                'Â·'  => '·',
                'Ã·'  => '÷',
                'Â¸'  => '¸',
                'Ã¸'  => 'ø',
                'Â¹'  => '¹',
                'Ã¹'  => 'ù',
                'Âº'  => 'º',
                'Ãº'  => 'ú',
                'Â»'  => '»',
                'Ã»'  => 'û',
                'Â¼'  => '¼',
                'Ã¼'  => 'ü',
                'Â½'  => '½',
                'Ã½'  => 'ý',
                'Â¾'  => '¾',
                'Ã¾'  => 'þ',
                'Â¿'  => '¿',
                'Ã¿'  => 'ÿ',
                'Ã€'  => 'À',

                // 1 char errors last
                'Ã'   => 'Á',
                'Å'   => 'Š',
                'Ã'   => 'Í',
                'Ã'   => 'Ï',
                'Ã'   => 'Ð',
                'Ã'   => 'Ý',
                'Ã'   => 'à',
                'Ã­'  => 'í',
            ];

            // Allow developers to add fixes into this list
            return apply_filters( 'wp_sanitize_accented_uploads_encoding_fixes', $fix_list );
        }

        /**
         * Creates counterpart for filename with encoding errors
         *
         * @param string $filename Possibly corrupted filename.
         *
         * @return string - filename with encoding errors
         */
        public static function replace_filename_with_encoding_errors( $filename ) {

            // Get associative array of fixes
            $fix_list = self::get_encoding_fix_list();

            // Check if developer filtered all of these away
            if ( empty( $fix_list ) ) {
                return $filename;
            }

            // Get encoding errors
            $error_chars = array_keys( $fix_list );
            $real_chars  = array_values( $fix_list );

            // The errors can happen in both nfd/nfc format so convert chars into nfd
            // Check which one $filename uses and use it
            // If $filename doesn't use FORM_D or FORM_C don't convert errors
            if ( Normalizer::isNormalized( $filename, Normalizer::FORM_D ) ) {

                $error_chars = array_map( function ( $n ) {
                    return Normalizer::normalize( $n, Normalizer::FORM_D );
                }, $error_chars );

            } elseif ( Normalizer::isNormalized( $filename, Normalizer::FORM_C ) ) {

                $error_chars = array_map( function ( $n ) {
                    return Normalizer::normalize( $n, Normalizer::FORM_C );
                }, $error_chars );

            }

            // Replaces all accented characters with encoding errors
            return str_replace( $real_chars, $error_chars, $filename );
        }
    }
}

Sanitizer::load();
