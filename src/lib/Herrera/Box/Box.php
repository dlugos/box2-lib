<?php

namespace Herrera\Box;

use Herrera\Box\Compactor\CompactorInterface;
use Herrera\Box\Exception\FileException;
use Herrera\Box\Exception\InvalidArgumentException;
use Phar;
use SplObjectStorage;

/**
 * Provides additional, complimentary functionality to the Phar class.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Box
{
    /**
     * The source code compactors.
     *
     * @var SplObjectStorage
     */
    private $compactors;

    /**
     * The Phar instance.
     *
     * @var Phar
     */
    private $phar;

    /**
     * The placeholder values.
     *
     * @var array
     */
    private $values = array();

    /**
     * Sets the Phar instance.
     *
     * @param Phar $phar The instance.
     */
    public function __construct(Phar $phar)
    {
        $this->compactors = new SplObjectStorage();
        $this->phar = $phar;
    }

    /**
     * Adds a file contents compactor.
     *
     * @param CompactorInterface $compactor The compactor.
     */
    public function addCompactor(CompactorInterface $compactor)
    {
        $this->compactors->attach($compactor);
    }

    /**
     * Compacts the file contents using the supported compactors.
     *
     * @param string $file     The file name.
     * @param string $contents The file contents.
     *
     * @return string The compacted contents.
     */
    public function compactContents($file, $contents)
    {
        foreach ($this->compactors as $compactor) {
            /** @var $compactor CompactorInterface */
            if ($compactor->supports($file)) {
                $contents = $compactor->compact($contents);
            }
        }

        return $contents;
    }

    /**
     * Returns the Phar instance.
     *
     * @return Phar The instance.
     */
    public function getPhar()
    {
        return $this->phar;
    }

    /**
     * Replaces the placeholders with their values.
     *
     * @param string $contents The contents.
     *
     * @return string The replaced contents.
     */
    public function replaceValues($contents)
    {
        return str_replace(
            array_keys($this->values),
            array_values($this->values),
            $contents
        );
    }

    /**
     * Sets the placeholder values.
     *
     * @param array $values The values.
     *
     * @throws Exception\Exception
     * @throws InvalidArgumentException If a non-scalar value is used.
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (false === is_scalar($value)) {
                throw InvalidArgumentException::create(
                    'Non-scalar values (such as %s) are not supported.',
                    gettype($value)
                );
            }

            $this->values["@$key@"] = $value;
        }
    }
}