<?php

$schema = new \Doctrine\DBAL\Schema\Schema();

$cliente = $schema->createTable('cliente');
$cliente->addColumn('id_cliente', 'integer', array('unsigned' => true, 'autoincrement' => true));
$cliente->addColumn('fone', 'string', array('length' => 20));
$cliente->addColumn('nome', 'string', array('length' => 60));
$cliente->addColumn('endereco', 'string', array('length' => 100));
$cliente->setPrimaryKey(array('id_cliente'));

$pedido = $schema->createTable('pedido');
$pedido->addColumn('id_pedido', 'integer', array('unsigned' => true, 'autoincrement' => true));
$pedido->addColumn('id_cliente', 'integer');
$pedido->addColumn('mensagem', 'string', array('length' => 200));
$pedido->setPrimaryKey(array('id_pedido'));

return $schema;
