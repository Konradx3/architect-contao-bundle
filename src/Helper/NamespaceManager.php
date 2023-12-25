<?php

namespace Architect\ContaoCommandBundle\Helper;

use Contao\System;
use Symfony\Component\Dotenv\Dotenv;
use Architect\ContaoCommandBundle\Helper;

class NamespaceManager
{
    private const ENV_FILE = '.env';
    private const NAMESPACE_KEY = 'BUNDLE_NAMESPACE';

    /**
     * Set the namespace in the .env file.
     *
     * @param string $namespace
     */
    public static function setNamespace(string $namespace): void
    {
        self::setEnvVariable(self::NAMESPACE_KEY, $namespace);
    }

    /**
     * Get the namespace from the .env file.
     *
     * @return string
     */
    public static function getNamespace(): string
    {
        return self::getEnvVariable(self::NAMESPACE_KEY, 'App\\AppBundle');
    }

    /**
     * Get the value of an environment variable from the .env file.
     *
     * @param string $variableName
     * @param string $defaultValue
     * @return string
     */
    private static function getEnvVariable(string $variableName, string $defaultValue = ''): string
    {
        $envPath = self::getEnvPath();

        $dotenv = new Dotenv();
        $dotenv->load($envPath);

        $envContent = file_get_contents($envPath);
        $pattern = "/^$variableName=[^\n]*/m";

        if (preg_match($pattern, $envContent, $matches)) {
            $value = explode('=', $matches[0], 2)[1];
            return trim($value, '"');
        }

        return $defaultValue;
    }

    /**
     * Gets the path to the .env file.
     *
     * @return string
     */
    private static function getEnvPath(): string
    {
        return System::getContainer()->getParameter('kernel.project_dir') . '/' . self::ENV_FILE;
    }

    /**
     * Sets the value of an environment variable in the .env file.
     *
     * @param string $variableName
     * @param string $value
     */
    private static function setEnvVariable(string $variableName, string $value): void
    {
        $envPath = self::getEnvPath();

        $dotenv = new Dotenv();
        $dotenv->load($envPath);

        $envContent = file_get_contents($envPath);
        $pattern = "/^$variableName=[^\n]*/m";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, "$variableName=\"$value\"", $envContent);
        } else {
            $envContent .= "\n$variableName=\"$value\"\n";
        }

        file_put_contents($envPath, $envContent);
    }

}
