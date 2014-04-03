<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class to aid in reading and writing uploaded files on disc.
 * specifically: 
 * to move a file from $_FILES into a temp location
 * It also creates some syntactic sugar for interacting with (reading/writing) PHP File objects.
 *
 * PHP version 5
 *
 * @author     Dylan Wood <dwood@mrn.org>
 */
class TempFile
{
    private $filename;

    /**
     * Construct new TempFile object from optional filename
     *
     * @param  string $filename (optional)
     * @return object TempFile 
     */
    public function __construct($filename = null, $extension = '') 
    {
        if ($filename === null) {
             $filename = static::generateTmpFilename($extension);
        }
        $this->set('filename', $filename);

        return $this;
    }

    /**
     * Generate a unique file path based on current timestamp
     *
     * @return string
     */
    public static function generateTmpFilename($extension = '')
    {
	if ($extension) $extension = ".$extension";
        return '/tmp/filewrapper_tmpfile_' . str_replace(' ', '_', microtime())
            . $extension;
    }

    /**
     * Create new TempFile object from uploaded file or this file if in test mode
     *
     * @param  boolean $testMode
     * @return object TempFile 
     */
    public static function createFromUpload($extension, $testMode = false)
    {
        $tmpFilename = static::generateTmpFilename($extension);
        if ($testMode) {
            copy(__FILE__, $tmpFilename);

            return new TempFile($tmpFilename);
        }
        $formFieldName = static::getUploadedFileFieldName();
        $moveSuccessful = move_uploaded_file(
            $_FILES[$formFieldName]['tmp_name'],
            $tmpFilename
        );
        if ($moveSuccessful) {
            return new TempFile($tmpFilename);
        } else {
            $errorMsg = 'Unable to locate uploade file under field "'
                . $formName;
            throw new Exception($errorMsg);
        }
    }

    /**
     * Generic getter method
     *
     * @param  string  $key
     * @return unknown
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Generic setter method
     *
     * @param  string  $key
     * @param  unknown $value
     * @return unknown
     */
    public function set($key, $value)
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * Replace current file contents with $data
     *
     * @param  string $data
     * @return int,   boolean
     */
    public function write($data)
    {
        return file_put_contents($this->get('filename'), $data);
    }

    /**
     * Read and return file contents from beginning of file to end
     *
     * @return string
     */
    public function read()
    {
        return file_get_contents($this->get('filename'));
    }

    /**
     * Delete file from disc
     *
     * @return boolean
     */
    public function destroy()
    {
        $filename = $this->get('filename');

        return unlink($filename);
    }

    /**
     * Get name of uploaded file
     *
     * @param  boolean $testMode
     * @return string
     */
    public static function getUploadedFileName($testMode = false)
    {
        if ($testMode) {
            return basename(__FILE__);
        }
        $formFieldName = static::getUploadedFileFieldName();

        return $_FILES[$formFieldName]['name'];
    }

    /**
     * Get array key of $_FILES in which the uploaded file metadata can be found
     *
     * @return string
     */
    public static function getUploadedFileFieldName()
    {
        if (empty($_FILES)) {
            $errorMsg = 'Unable to locate uploaded file"';
            throw new Exception($errorMsg);
        }
        $keys = array_keys($_FILES);

        return $keys[0];
    }
}
