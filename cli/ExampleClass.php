<?php

/**
 * Class ExampleClass
 *
 * An example class inside the cli folder
 */
class ExampleClass
{
    public function __construct($init)
    {
        //access the db
        //$query = $init->db->query('');

        //load a model class set the context param, if you want load a class from the admin
        $init->load->model('catalog/product');

        echo 'Hello ExampleClass' . PHP_EOL;
    }
}