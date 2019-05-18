<?php

/**
 * Class CacheCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class CacheCli extends BaseCommandCli
{
    protected $_init;

    /**
     * CacheCli constructor.
     *
     * @param $initClass BaseCli
     * @param $action
     * @param $params
     */
    public function __construct($initClass, $action, $params)
    {
        $this->_init = $initClass;
        $this->$action();
    }

    /**
     * Clean image and system cache
     */
    protected function clean()
    {
        $this->cleanDir(DIR_CACHE);
        $this->cleanDir(DIR_IMAGE . 'cache');

        $this->result(self::SUCCESS_RESULT);
    }

    private function cleanDir($dir)
    {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file)
        {
            if ($file->isDir())
            {
                rmdir($file->getRealPath());
            }
            else
            {
                unlink($file->getRealPath());
            }
        }
    }
}