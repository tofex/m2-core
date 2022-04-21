<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Framework\Filesystem\DirectoryList;
use Tofex\Help\Arrays;
use Tofex\Help\Files;
use Tofex\Xml\Writer;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Xml
{
    /** @var Files */
    protected $filesHelper;

    /** @var Arrays */
    protected $arrayHelper;

    /** @var DirectoryList */
    protected $directoryList;

    /**
     * @param Files         $filesHelper
     * @param Arrays        $arrayHelper
     * @param DirectoryList $directoryList
     */
    public function __construct(Files $filesHelper, Arrays $arrayHelper, DirectoryList $directoryList)
    {
        $this->filesHelper = $filesHelper;
        $this->arrayHelper = $arrayHelper;

        $this->directoryList = $directoryList;
    }

    /**
     * @param string $fileName
     * @param string $rootElement
     * @param array  $rootElementAttributes
     * @param array  $data
     * @param bool   $append
     * @param array  $characterDataElements
     * @param string $version
     * @param string $encoding
     *
     * @throws Exception
     */
    public function write(
        string $fileName,
        string $rootElement,
        array $rootElementAttributes,
        array $data,
        bool $append = false,
        array $characterDataElements = [],
        string $version = '1.0',
        string $encoding = 'UTF-8')
    {
        $xmlWriter = new Writer($this->filesHelper, $this->arrayHelper);

        $xmlWriter->setBasePath($this->directoryList->getRoot());
        $xmlWriter->setFileName($fileName);

        foreach ($characterDataElements as $characterDataElement) {
            $xmlWriter->addForceCharacterData($characterDataElement);
        }

        $xmlWriter->write($rootElement, $rootElementAttributes, $data, $append, $version, $encoding);
    }
}
