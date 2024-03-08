<?php

namespace Lacodix\LaravelScopedMailConfig\Concerns;

interface HasMailConfig
{
    public function getMailConfig(string $name): array;
}
