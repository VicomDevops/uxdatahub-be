<?php

namespace App\Utils;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigManager
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * ConfigManager constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getConfigWs($providerTag, $wsNameTag)
    {
        $env = $this->parameterBag->get('API_ENV_CLIENT');
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $dirConfigProvider = $projectDir . '/config/ws/' . $env . '/' . $providerTag . '.yaml';

        $dirConfigWs = Yaml::parse(file_get_contents($dirConfigProvider));

        return $dirConfigWs[$wsNameTag];
    }
}
