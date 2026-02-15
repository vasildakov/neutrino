<?php

namespace Neutrino\Queue;

interface ProducerInterface
{
    public function produce(): void;
}