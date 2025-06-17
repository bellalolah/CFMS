<?php

namespace Cfms\Interface;

interface Model
{
    public function toModel(array $data): self;
    public function getModel(array $data): self;
}
