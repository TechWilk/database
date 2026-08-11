<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

trait LoadsSqlSeeds
{
    protected function loadSeedFile(string $filename): void
    {
        if (str_contains($filename, '/')) {
            throw new \InvalidArgumentException('Seed filename cannot contain slashes');
        }
        $sqlContent = file_get_contents(__DIR__ . '/data/' . $filename);
        $sqlStatements = explode(';', $sqlContent);
        foreach ($sqlStatements as $sql) {
            if (trim($sql) === '') {
                continue;
            }

            $this->database->query($sql);
        }
    }
}
