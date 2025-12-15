<?php

namespace App\Services;

/**
 * Maps icon pack names to Blade Icons component names.
 */
class IconPackMapper
{
    /**
     * Map of pack names to component name patterns.
     *
     * @var array<string, callable>
     */
    protected array $packMappings = [
        'heroicons' => fn (string $name): string => "heroicon-o-{

public 

public 

public 

public $name}",
    'heroicons-solid' => fn (string $name): string => "heroicon-s-{

public 

public 

public 

public $name}",
    'fontawesome' => fn (string $name): string => "fas-{

public 

public 

public 

public $name}",
    'bootstrap' => fn (string $name): string => "bi-{

public 

public 

public 

public $name}",
    'feather' => fn (string $name): string => "feather-{

public 

public 

public 

public $name}",
    ];

    /**
     * Get the Blade Icons component name for a given pack and icon name.
     */
    public function getComponentName(string $pack, string $name): string
    {
        if (isset($this->packMappings[$pack])) {
            return ($this->packMappings[$pack])($name);
        }

        // Fallback to question-mark icon
        return 'heroicon-o-question-mark-circle';
    }

    /**
     * Check if a pack is supported.
     */
    public function isPackSupported(string $pack): bool
    {
        return isset($this->packMappings[$pack]);
    }

    /**
     * Get all supported packs.
     *
     * @return array<string>
     */
    public function getSupportedPacks(): array
    {
        return array_keys($this->packMappings);
    }
}
