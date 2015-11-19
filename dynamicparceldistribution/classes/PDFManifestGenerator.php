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

require_once(_PS_TOOL_DIR_.'tcpdf/config/lang/eng.php');
require_once(_PS_TOOL_DIR_.'tcpdf/tcpdf.php');

/**
 * @since 1.5
 */
class PDFManifestGenerator extends PDFGeneratorCore
{
    /**
     * Set default Font family
     */
    const DEFAULT_FONT = 'helvetica';

    /**
     * HTML script of header
     * @var string
     */
    public $header;

    /**
     * HTML script of footer
     * @var string
     */
    public $footer;

    /**
     * HTML script of content
     * @var string
     */
    public $content;
    public $font;

    /**
     * HTML script of last page content
     * Footer with signature
     * @var string
     */
    public $last_page_content;

    /**
     * This is last page?
     * @var boolean
     */
    protected $last_page_flag = false;

    /**
     * Class constructor
     * set some default parameters
     */
    public function __construct($use_cache = false)
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', $use_cache, false);
    }

    public function lastPage($resetmargins = false)
    {
        $this->setPage($this->getNumPages(), $resetmargins);
        $this->last_page_flag = true;
    }

    /**
     * Write a PDF page
     */
    public function writePage()
    {
        if ($this->last_page_flag) {
            $this->setMargins(20, 20, 20, true);
        } else {
            $this->SetFooterMargin(75);
        }

        $this->SetHeaderMargin(5);
        $this->setMargins(10, 60, 10);
        $this->setHeaderFont(array('dejavusans', '', null, '', false));
        $this->setFooterFont(array('dejavusans', '', null, '', false));
        $this->SetFont('dejavusans', '', null, '', false);
        $this->AddPage();
        $this->writeHTML($this->content, true, false, true, false, '');
    }

    /**
     * Render header data of document
     */
    public function header()
    {
        if ($this->page == 1) {
            parent::Header();
        } else {
            $this->setMargins(10, 10, 10, true);
        }
    }

    /**
     * Render footer data of document
     */
    public function footer()
    {
        if ($this->last_page_flag) {
            parent::Footer();
        } else {
            $this->setMargins(10, 10, 10, true);
        }

        $this->SetY(-10);
        $this->SetFont('dejavusans', '', 8);
        $this->Cell(
            0,
            5,
            $this->getAliasNumPage().' / '.$this->getAliasNbPages(),
            0,
            false,
            'R',
            0,
            '',
            0,
            false,
            'T',
            'M'
        );
    }

    /**
     * Set HTML content of last document page, this is for footer with signature of manifest
     */
    public function addLastPage($html)
    {
        $this->last_page_content = $html;
    }
}
