<?php

namespace RockLobsterInc\FormDataTree;

use function RockLobsterInc\Functions\{strip_whitespaces, exclude_blank};

/**
 * A class that implements FormDataTreeInterface. Wraps the PHP superglobals.
 */
class FormDataTree implements FormDataTreeInterface
{
    /**
     * Data origin.
     */
    private array $origin;

    /**
     * Constructor.
     *
     * @param array $origin Optional data origin.
     */
    public function __construct(array $origin = [])
    {
        $this->origin = [
            "post" => $origin["post"] ?? $_POST,
            "files" => $origin["files"] ?? File::buildTree(),
        ];
    }

    /**
     * Returns the values associated with a given field name.
     *
     * @param string $name Field name.
     * @return iterable Iterator of the values.
     */
    public function getAll(string $name): iterable
    {
        $name_parts = dissolve_name($name);

        if (empty($name_parts)) {
            return [];
        }

        $posted_value = $this->origin["post"];

        while ($next = array_shift($name_parts)) {
            if (
                preg_match('/^[0-9]*$/', $next) or !isset($posted_value[$next])
            ) {
                return [];
            }

            $posted_value = $posted_value[$next];
        }

        if (!is_array($posted_value)) {
            $posted_value = [$posted_value];
        }

        $posted_value = strip_whitespaces($posted_value);
        $posted_value = exclude_blank($posted_value);

        return $posted_value;
    }

    /**
     * Returns the file objects associated with a given field name.
     *
     * @param string $name Field name.
     * @return iterable Iterator of the FileInterface objects.
     */
    public function getAllFiles(string $name): iterable
    {
        $name_parts = dissolve_name($name);

        if (empty($name_parts)) {
            return [];
        }

        $files_tree = $this->origin["files"];

        while ($next = array_shift($name_parts)) {
            if (preg_match('/^[0-9]*$/', $next) or !isset($files_tree[$next])) {
                return [];
            }

            $files_tree = $files_tree[$next];
        }

        if (!is_array($files_tree)) {
            $files_tree = [$files_tree];
        }

        $files_tree = exclude_blank($files_tree);

        return $files_tree;
    }
}
