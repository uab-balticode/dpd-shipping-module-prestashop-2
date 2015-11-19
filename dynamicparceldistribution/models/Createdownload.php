<?php
/**
 * 2015 UAB BaltiCode
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License available
 * through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@balticode.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to
 * newer versions in the future.
 *
 *  @author    UAB Balticode Kęstutis Kaleckas
 *  @package   Balticode_DPD
 *  @copyright Copyright (c) 2015 UAB Balticode (http://balticode.com/)
 *  @license   http://www.gnu.org/licenses/gpl-3.0.txt  GPLv3
 */

/**
 * Create download class is for render file content to make it downloadable
 */
class Createdownload
{
    /**
     * name/route of file to download
     * @var string
     */
    private $filename = '';

    /**
     * filename to download the file under
     * @var string
     */
    private $dl_filename = '';

    /**
     * the mimetype of the file
     * @var string
     */
    private $mimetype = '';

    /**
     * length of the file
     * @var integer
     */
    private $file_size = 0;

    /**
     * disallow multi-threaded downloading
     * @var boolean
     */
    private $force_single = false;

    /**
     * in multi-threaded downloading, the offset to start at
     * @var integer
     */
    private $mt_range = 0;

    /*
    * Class Constructor
    */
    public function __construct($dl_filename = '', $mimetype = 'application/octet-stream', $force_single = false)
    {
        //import members
        $this->force_single = $force_single;
        $this->dl_filename = $dl_filename;
        $this->mimetype = $mimetype;
        //if safe mode is enabled, raise a warning
        if (ini_get('safe_mode')) {
            trigger_error('<b>Downloader:</b> Will not be able to handle large files while safe mode is enabled.'.E_USER_WARNING);
        }
    }

    /*
    * Prepare Headers
    *
    * Prepare the main output header strings for the download
    */
    private function prepareHeaders($size = 0)
    {
        if (ob_get_contents()) {
            print_r('Some data has already been output');
            die();
        }
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header('Content-Description: File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('refresh:1;url='.$_SERVER['HTTP_REFERER'].'');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-Type: '.$this->mimetype, false);
        header('Content-Disposition: attachment; filename="'.$this->dl_filename.'"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        //The three lines below basically make the
        //download non-cacheable
        header('Cache-control: private');
        header('Pragma: private');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        // multipart-download and download resuming support
        if (isset($_SERVER['HTTP_RANGE']) && !$this->force_single) {
            list($a, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            unset($a);
            list($range) = explode(',', $range, 2);
            list($range, $range_end) = explode('-', $range);
            $range = (int)$range;
            if (!$range_end) {
                $range_end = $size - 1;
            } else {
                $range_end = (int)$range_end;
            }
            $new_length = $range_end - $range + 1;
            header('HTTP/1.1 206 Partial Content');
            //header('Content-Length: '.$new_length);
            header('Content-Range: bytes '.$range.'-'.$range_end.'/'.$size);
            //set the offset range
            $this->mt_range = $range;
        } else {
            $new_length = $size;
            header('Content-Length: '.$size);
        }

        return $new_length;
    }

    /*
    * Download File
    *
    * Set up the headers and download the file to the
    */
    public function downloadFile($filename = '')
    {
        //assert the file is valid
        if (!is_file($filename)) {
            throw new Exception('Downloader: Could not find file \''.$filename.'\'');
        }
        //make sure it's read-able
        if (!is_readable($filename)) {
            throw new Exception('Downloader: File was unreadable \''.$filename.'\'');
        }
        //set script execution time to 0 so the script
        //won't time out.
        set_time_limit(0);
        //get the size of the file
        $this->file_size = filesize($filename);
        //set up the main headers
        //find out the number of bytes to write in this iteration
        $block_size = $this->prepareHeaders($this->file_size);
        /* output the file itself */
        $chunksize = 1 * (1024 * 1024);
        $bytes_send = 0;
        if ($file = fopen($filename, 'r')) {
            if (isset($_SERVER['HTTP_RANGE']) && !$this->force_single) {
                fseek($file, $this->mt_range);
            }
            //write the data out to the browser
            while (!feof($file) && !connection_aborted() && $bytes_send < $block_size) {
                $buffer = fread($file, $chunksize);
                echo $buffer;
                flush();
                $bytes_send += Tools::strlen($buffer);
            }
            fclose($file);
        } else {
            throw new Exception('Downloader: Could not open file \''.$filename.'\'');
        }

        //terminate script upon completion
        return '';
    }

    /**
     * Send correct header and output file content
     * @param  string $content file content
     * @return string return nothing it's need to stop script and end the file content
     */
    public function render($content)
    {
        set_time_limit(0);
        $this->prepareHeaders(Tools::strlen($content, 'binary'));
        echo $content;
        return '';
    }
}
