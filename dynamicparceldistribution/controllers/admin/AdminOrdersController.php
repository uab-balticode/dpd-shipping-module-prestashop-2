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
 * AdminOrderBulkAction Class is Using when Overwrite has been not allowed
 *     because some other module already overwrite AdminOrderController file
 *
 * With This Class has been a very responsive,
    make backup before using this!
 */

class AdminOrderBulkAction
{
    /**
     * Value of file who need to be edit
     * @var string
     */
    protected $target;

    /**
     * Time stamp of today,
     * this has been changing file extension to backup data
     *
     * @var string
     */
    protected $backup_time_stamp;

    /**
     * backup prefix without data stamp
     * @var string
     */
    private $backup_prefix = '_backup_';

    /**
     * This is array where who and how replace current string
     * @var array
     */
    private $replace_array = array();

    /**
     * array of errors messages list
     * @var array
     */
    public $error_msg = array();

    /**
     * Temporary string where we save make changes of target file content
     *
     * @var string
     */
    private $file_content = null;

    /**
     * Class constructor
     *
     * @param boolean $backup do we need make backup of file or not
     * @return boolean if need make backup, and backup has been failed return false
     */
    public function __construct($backup = true)
    {
        $this->setTarget('/controllers/admin/AdminOrdersController.php');
        if ($backup) {
            if (!$this->makeBackup()) {
                return false;
            }
        }
    }

    /**
     * Set target file with patch to target
     *
     * @param string $target patch to file name
     */
    public function setTarget($target)
    {
        $this->target = _PS_ROOT_DIR_.$target;
    }

    /**
     * Create Backup file of changing content
     *
     * @param  string $target patch to file
     * @return boolean - true if success
     */
    public function makeBackup($target = null)
    {
        if (empty($target)) {
            $target = $this->target;
        }
        $this->backup_time_stamp = time();
        $parent = $target;
        $children = $target.$this->backup_prefix.$this->backup_time_stamp;
        if (!Tools::copy($parent, $children)) {
            $this->error_msg[] = error_get_last();
            return false;
        } else {
            if (filesize($parent) === filesize($children)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Add situation to private array who need to be replace add or remove
     *
     * @param string  $search  Some text who we search
     * @param string  $replace Some text who we replace searched string
     * @param string  $action  replace, before, after
     * @param integer $count   how much can be found
     * @param integer $index   if found more that one, with need to be change
     */
    public function addToReplace($search, $replace, $action = 'replace', $count = 1, $index = 1)
    {
        if (count($this->searchInFile($search))) {
            if ($this->searchInFile($search) > $count) {
                $this->error_msg[] = 'Found more that '.$count.' when search:'.htmlspecialchars($search);
                return false;
            }
            //Hear need more condition to test
            //Register who need to be change
            $this->replace_array[] = array('search' => $search, 'replace' => $replace, 'action' => $action, 'count' => $count, 'index' => $index);
        } else {
            $this->error_msg[] = 'Not found this string:'.htmlspecialchars($search);
            return false;
        }
    }

    /**
     * Search some content in file of target who set with function setTarget
     *
     * @param  string $search_string file content
     * @return int return how much results founds
     */
    private function searchInFile($search_string)
    {
        if (Tools::strlen($search_string)) {
            $content = Tools::file_get_contents($this->target);
            return substr_count($content, $search_string);
        }
        return 0;
    }

    /**
     * Function changing or append content to target file
     *
     * @return int | boolean if correct return bytes write, else return false
     */
    public function run()
    {
        if (!count($this->error_msg)) {
            $this->file_content = Tools::file_get_contents($this->target);
            foreach ($this->replace_array as $replace) {
                $replace_to = null;
                switch ($replace['action']) {
                    case 'replace':
                        $replace_to = $replace['replace'];
                        break;
                    case 'before':
                        $replace_to = $replace['replace'].$replace['search'];
                        break;
                    case 'after':
                        $replace_to = $replace['search'].$replace['replace'];
                        break;
                }
                $this->file_content = str_replace($replace['search'], $replace_to, $this->file_content);
            }
            return $this->save();
        } else {
            $this->error_msg[] = 'Found some errors!';
            return false;
        }
    }

    /**
     * write content to target
     *
     * @return int | boolean if correct return bytes to write, else return false
     */
    private function save()
    {
        return file_put_contents($this->target, $this->file_content);
    }

    /**
     * Return Error messages of private array
     *
     * @param  boolean $clear if true messages has been cleared
     * @return array          array of messages
     */
    public function getErrors($clear = true)
    {
        $errors = $this->error_msg; //get all errors messages
        if ($clear) {
            $this->error_msg = array(); //clear error list
        }
        return $errors; //return erros
    }
}
