<?php

namespace Cfms\Interface;

interface Models
{
    public function toModel(array $data): self;
    public function getModel(array $data): self;
}
