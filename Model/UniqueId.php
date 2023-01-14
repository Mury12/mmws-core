<?php

namespace MMWS\Model;

use Error;
use MMWS\Abstracts\Model;
use ValueError;

class UniqueId extends Model
{

    /**
     * The unique identifier
     */
    public string $uid;
    /**
     * The hash used to generate the identifier
     */
    public string $hash;
    /**
     * Length of the identifier
     */
    public Int $length;

    /**
     * The hash type, such as sha256, sha512 or sha2.
     * `UniqueId::<hashType>` can be used
     */
    public string $hashType;


    const SHA256 = 'sha256';
    const SHA512 =  'sha512';
    const SHA2 = 'sha2';

    public function __construct(Int $length = 6, ?string $hashType = self::SHA256)
    {
        $this->hidden[] = 'hashType';
        $this->length = $length;
        $this->hashType = $hashType;
        $this->get();
    }

    /**
     * Generates an unique identifier string with mixed characters
     */
    function get()
    {
        $d = time();
        $pre = 'unique_id_mm@@_';
        $pre = hash($this->hashType, $pre . $d);
        $uid = '';
        $len = 6;

        if (!$pre) return ['res' => false, 'message' => 'Invalid hashing algorithm.'];

        if ($this->length <= 128) {
            $len = $this->length;
        } else {
            return array('res' => 'Length must be an integer below 128!', 'err' => true);
        }
        for ($i = 0; $i < $len; $i++) {
            $uid .= substr($pre, rand(0, $len), 1);
        }
        $this->uid = $uid;
        $this->length = strlen($uid);
        $this->hash = $pre;
        return $this;
    }

    /**
     * Regenrates the unique identifier with the same parameters or new ones if defined
     */
    function regen(?Int $length, ?string $hash)
    {
        $this->length = $length ?? $this->length;
        $this->hash = $hash ?? $this->hash;

        $this->get();
        return $this;
    }

    function __set($name, $value)
    {
        trigger_error("Cannot set readonly property!", E_USER_WARNING);
    }
}
