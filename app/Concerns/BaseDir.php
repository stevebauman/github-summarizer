<?php

namespace App\Concerns;

trait BaseDir
{
    public function getHomeDir(): string
    {
        return (new \XdgBaseDir\Xdg())->getHomeDir();
    }
}
