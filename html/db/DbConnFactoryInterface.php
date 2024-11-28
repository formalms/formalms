<?php

namespace FormaLms\db;

interface DbConnFactoryInterface
{
    public function create(array $config): ?DbConn;
}