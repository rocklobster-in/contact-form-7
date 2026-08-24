<?php

require __DIR__ . "/../vendor/autoload.php";

use RockLobsterInc\FormDataTree\FileInterface;

class FileMock implements FileInterface
{
    private string $name;
    private int $size;
    private string $temporaryFilePath;
    private int $error;

    public function __construct(array $properties = [])
    {
        $this->name = $properties["name"];
        $this->size = $properties["size"];
        $this->temporaryFilePath = $properties["temporaryFilePath"];
        $this->error = $properties["error"];
    }

    public function name(): string
    {
        return $this->name;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function temporaryFilePath(): string
    {
        return $this->temporaryFilePath;
    }

    public function error(): int
    {
        return $this->error;
    }
}
